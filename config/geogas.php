<?php

return [
    // Price older than this many hours is flagged "may be outdated" on public pages.
    'stale_hours' => (int) env('GEOGAS_STALE_HOURS', 72),

    // Managers/staff are reminded when a station has not updated for this long.
    'reminder_hours' => (int) env('GEOGAS_REMINDER_HOURS', 48),

    // Sanity bounds for a pump price (PHP per litre). Outside = rejected.
    'price_min' => (float) env('GEOGAS_PRICE_MIN', 20),
    'price_max' => (float) env('GEOGAS_PRICE_MAX', 200),

    // A change bigger than this % versus the current price must be confirmed.
    'price_jump_percent' => (float) env('GEOGAS_PRICE_JUMP_PERCENT', 10),

    // Login throttling: failed attempts per email+IP before a lockout.
    'login_max_attempts' => (int) env('GEOGAS_LOGIN_MAX_ATTEMPTS', 5),
    'login_lockout_seconds' => (int) env('GEOGAS_LOGIN_LOCKOUT_SECONDS', 900),

    // National (Philippines) fuel-news importer. Comma-separated "Name|https://feed-url" pairs.
    // Empty = importer does nothing and LGU staff post national news by hand.
    'news_feeds' => array_values(array_filter(array_map('trim', explode(',', (string) env('GEOGAS_NEWS_FEEDS', ''))))),
    'news_keywords' => array_values(array_filter(array_map('trim', explode(',', (string) env('GEOGAS_NEWS_KEYWORDS', 'fuel,gasoline,diesel,kerosene,pump price,oil price,rollback,price hike,petrol,DOE,LPG'))))),
    // false = imported items wait as drafts for LGU review (recommended). true = publish at once.
    'news_auto_publish' => filter_var(env('GEOGAS_NEWS_AUTO_PUBLISH', false), FILTER_VALIDATE_BOOLEAN),
    'news_max_per_feed' => (int) env('GEOGAS_NEWS_MAX_PER_FEED', 10),
];
