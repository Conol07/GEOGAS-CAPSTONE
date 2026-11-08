<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Imports Philippine fuel headlines from RSS/Atom feeds as DRAFT news items.
 * Only the headline, a short snippet and the link to the original are stored;
 * the LGU reviews, rewrites the summary in its own words, then publishes.
 */
class ImportNationalNews extends Command
{
    protected $signature = 'geogas:import-national-news {--file= : Parse a local RSS/Atom file instead of the configured feeds (testing)} {--dry-run : Show what would be imported}';

    protected $description = 'Import Philippine fuel news headlines from RSS/Atom feeds as drafts for LGU review';

    public function handle(): int
    {
        $feeds = config('geogas.news_feeds');
        $sources = [];

        if ($file = $this->option('file')) {
            $sources[] = ['name' => 'Test feed', 'body' => (string) @file_get_contents($file)];
        } else {
            foreach ($feeds as $entry) {
                [$name, $url] = array_pad(explode('|', $entry, 2), 2, null);
                if (! $url || ! preg_match('#^https?://#i', $url)) {
                    $this->warn("Skipping invalid feed entry: {$entry}");
                    continue;
                }
                try {
                    $res = Http::timeout(10)->withHeaders(['User-Agent' => 'GeoGasManFort/1.0 (news importer)'])->get($url);
                    if (! $res->ok()) {
                        $this->warn("{$name}: HTTP {$res->status()}");
                        continue;
                    }
                    $sources[] = ['name' => trim($name), 'body' => $res->body()];
                } catch (\Throwable $e) {
                    $this->warn("{$name}: ".$e->getMessage());
                }
            }
        }

        if (! $sources) {
            $this->info('No feeds configured (set GEOGAS_NEWS_FEEDS). Nothing to import.');

            return self::SUCCESS;
        }

        $author = User::where('role', 'lgu_admin')->orderBy('id')->first();
        if (! $author) {
            $this->error('No LGU admin account to attribute drafts to.');

            return self::FAILURE;
        }

        $keywords = config('geogas.news_keywords');
        $max = config('geogas.news_max_per_feed');
        $added = 0;

        foreach ($sources as $src) {
            $count = 0;
            foreach ($this->parse($src['body']) as $item) {
                if ($count >= $max) {
                    break;
                }
                $haystack = Str::lower($item['title'].' '.$item['summary']);
                if ($keywords && ! collect($keywords)->contains(fn ($k) => str_contains($haystack, Str::lower($k)))) {
                    continue;
                }
                if (News::where('source_url', $item['url'])->exists()) {
                    continue;
                }
                $count++;
                $this->line("+ [{$src['name']}] {$item['title']}");
                if ($this->option('dry-run')) {
                    continue;
                }
                News::create([
                    'title' => Str::limit($item['title'], 250, ''),
                    'category' => $this->guessCategory($haystack),
                    'scope' => 'national',
                    'source_name' => Str::limit($src['name'], 120, ''),
                    'source_url' => Str::limit($item['url'], 500, ''),
                    'content' => $item['summary'] !== '' ? $item['summary'] : 'See the original article for details.',
                    'status' => config('geogas.news_auto_publish') ? 'published' : 'draft',
                    'published_at' => config('geogas.news_auto_publish') ? now() : null,
                    'created_by' => $author->id,
                ]);
                $added++;
            }
        }

        $this->info($this->option('dry-run') ? 'Dry run complete.' : "Imported {$added} item(s). ".(config('geogas.news_auto_publish') ? 'Published automatically.' : 'Review the drafts under LGU > News before publishing.'));

        return self::SUCCESS;
    }

    /** @return array<int,array{title:string,url:string,summary:string}> */
    private function parse(string $xml): array
    {
        $prev = libxml_use_internal_errors(true);
        // No LIBXML_NOENT / DTD loading, and no network: safe against XXE.
        $doc = simplexml_load_string($xml, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        if (! $doc) {
            return [];
        }

        $items = [];
        foreach ($doc->channel->item ?? [] as $i) {                    // RSS 2.0
            $items[] = $this->item((string) $i->title, (string) $i->link, (string) $i->description);
        }
        foreach ($doc->entry ?? [] as $e) {                            // Atom
            $link = '';
            foreach ($e->link as $l) {
                $rel = (string) ($l['rel'] ?? 'alternate');
                if ($rel === 'alternate' || $link === '') {
                    $link = (string) $l['href'];
                }
            }
            $items[] = $this->item((string) $e->title, $link, (string) ($e->summary ?: $e->content));
        }

        return array_values(array_filter($items, fn ($x) => $x['title'] !== '' && preg_match('#^https?://#i', $x['url'])));
    }

    private function item(string $title, string $url, string $summary): array
    {
        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($summary), ENT_QUOTES | ENT_HTML5)));

        return [
            'title' => trim(html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5)),
            'url' => trim($url),
            'summary' => Str::limit($text, 300),
        ];
    }

    private function guessCategory(string $text): string
    {
        if (preg_match('/rollback|roll back|decrease|cut|lower|down\b/', $text)) {
            return 'price_decrease';
        }
        if (preg_match('/hike|increase|rise|higher|up\b/', $text)) {
            return 'price_hike';
        }

        return 'other';
    }
}
