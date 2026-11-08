<?php

namespace App\Http\Controllers\Lgu;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\FuelPrice;
use App\Models\GasolineStation;
use App\Models\StationPersonnel;
use App\Models\StationService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StationController extends Controller
{
    public function index(Request $request)
    {
        $stations = GasolineStation::search($request->input('q'))
            ->inBarangay($request->input('barangay'))
            ->orderBy('station_name')
            ->paginate(12)
            ->withQueryString();

        $barangays = GasolineStation::select('barangay')->distinct()->orderBy('barangay')->pluck('barangay');

        return view('lgu.stations.index', compact('stations', 'barangays'));
    }

    /**
     * Read-only station detail for the LGU: profile, linked accounts (never
     * credentials), current prices, history, complaints and activity.
     */
    public function show(GasolineStation $station)
    {
        $station->load('services');

        $personnel = StationPersonnel::with('user:id,name,email,phone,role,status')
            ->where('station_id', $station->id)
            ->get();
        $managers = $personnel->filter(fn ($p) => optional($p->user)->role === 'manager')->values();
        $staff = $personnel->filter(fn ($p) => optional($p->user)->role === 'staff')->values();

        $currentPrices = $station->currentPrices();
        $priceHistory = FuelPrice::with(['fuelType', 'updater:id,name'])
            ->where('station_id', $station->id)
            ->latest('id')
            ->take(20)
            ->get();
        $complaints = Complaint::where('station_id', $station->id)->latest('created_at')->take(10)->get();
        $complaintTotal = Complaint::where('station_id', $station->id)->count();

        $activity = AuditLog::with('user:id,name')
            ->where(function ($q) use ($station) {
                $q->where(fn ($s) => $s->where('subject_type', GasolineStation::class)->where('subject_id', $station->id))
                    ->orWhere('description', 'like', '%'.$station->station_name.'%');
            })
            ->latest('id')
            ->take(10)
            ->get();

        return view('lgu.stations.show', compact(
            'station', 'managers', 'staff', 'currentPrices', 'priceHistory', 'complaints', 'complaintTotal', 'activity'
        ));
    }

    public function toggleStatus(Request $request, GasolineStation $station)
    {
        $station->update(['status' => $station->status === 'active' ? 'inactive' : 'active']);

        AuditLog::record($request->user(), $station->status === 'active' ? 'Activated station' : 'Deactivated station', $station, $station->station_name);

        return back()->with('status', "Station is now {$station->status}.");
    }

    public function create()
    {
        return view('lgu.stations.create');
    }

    /**
     * Registers the station AND its manager account in one step, since a
     * station is useless to the public without someone able to update it.
     */
    public function store(Request $request)
    {
        $validated = $this->validateStation($request);

        $managerData = $request->validate([
            'manager_email' => ['required', 'email', 'unique:users,email'],
            'manager_username' => ['required', 'string', 'max:255'],
            'manager_password' => ['required', 'string', 'min:8'],
        ]);

        $validated = $this->applyImageUploads($request, $validated, null);

        $station = GasolineStation::create($validated);

        foreach (StationService::CATALOG as $key => $label) {
            $station->services()->create([
                'service_key' => $key,
                'label' => $label,
                'available' => $request->boolean("service_{$key}"),
            ]);
        }

        $manager = User::create([
            'name' => $managerData['manager_username'],
            'email' => $managerData['manager_email'],
            'password' => Hash::make($managerData['manager_password']),
            'role' => 'manager',
            'status' => 'active',
        ]);

        StationPersonnel::create([
            'user_id' => $manager->id,
            'station_id' => $station->id,
            'added_by' => $request->user()->id,
        ]);

        AuditLog::record($request->user(), 'Registered gasoline station', $station, $station->station_name);

        return redirect()->route('lgu.stations.index')->with('status', 'Station and manager account created. It now appears on the public map.');
    }

    public function edit(GasolineStation $station)
    {
        return view('lgu.stations.edit', compact('station'));
    }

    public function update(Request $request, GasolineStation $station)
    {
        $validated = $this->validateStation($request);
        $validated = $this->applyImageUploads($request, $validated, $station);

        $station->update($validated);

        AuditLog::record($request->user(), 'Updated station information', $station, $station->station_name);

        return redirect()->route('lgu.stations.index')->with('status', 'Station updated successfully.');
    }

    public function destroy(Request $request, GasolineStation $station)
    {
        $station->update(['status' => 'inactive']);

        AuditLog::record($request->user(), 'Deactivated station', $station, $station->station_name);

        return back()->with('status', 'Station deactivated.');
    }

    private function validateStation(Request $request): array
    {
        $validated = $request->validate([
            'station_name' => ['required', 'string', 'max:255'],
            'company_owner' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'address' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:100'],
            'municipality' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        // These are handled separately in applyImageUploads(); never mass-assign them directly.
        unset($validated['logo'], $validated['photo'], $validated['remove_logo'], $validated['remove_photo']);

        return $validated;
    }

    /**
     * Handles upload / replace / remove for the logo and photo fields,
     * deleting the old file from storage whenever it's being replaced
     * or removed. Returns $validated with logo_path/photo_path merged in.
     */
    private function applyImageUploads(Request $request, array $validated, ?GasolineStation $station): array
    {
        if ($request->hasFile('logo')) {
            if ($station?->logo_path) {
                Storage::disk('public')->delete($station->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('stations/logos', 'public');
        } elseif ($request->boolean('remove_logo') && $station?->logo_path) {
            Storage::disk('public')->delete($station->logo_path);
            $validated['logo_path'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($station?->photo_path) {
                Storage::disk('public')->delete($station->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('stations/photos', 'public');
        } elseif ($request->boolean('remove_photo') && $station?->photo_path) {
            Storage::disk('public')->delete($station->photo_path);
            $validated['photo_path'] = null;
        }

        return $validated;
    }
}
