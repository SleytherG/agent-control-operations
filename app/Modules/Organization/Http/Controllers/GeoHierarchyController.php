<?php

namespace App\Modules\Organization\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Organization\Http\Requests\RegionRequest;
use App\Modules\Organization\Http\Requests\ProvinceRequest;
use App\Modules\Organization\Http\Requests\DistrictRequest;
use App\Modules\Organization\Models\Region;
use App\Modules\Organization\Models\Province;
use App\Modules\Organization\Models\District;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GeoHierarchyController extends Controller
{
    /* Regions */

    public function regionsIndex(Request $request): View
    {
        Gate::authorize('viewAny', Region::class);

        $regions = Region::withCount('provinces')
            ->where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('organization.geo.regions.index', compact('regions'));
    }

    public function storeRegion(RegionRequest $request): RedirectResponse
    {
        Gate::authorize('create', Region::class);

        $region = Region::create([
            'organization_id' => auth()->user()->organization_id,
            'name' => $request->input('name'),
            'is_active' => true,
        ]);

        $this->logAudit('region.created', Region::class, $region->id, null, $region->only(['name']));

        return redirect()->route('admin.regions.index')
            ->with('status', 'Región creada correctamente.');
    }

    public function showRegion(Region $region): View
    {
        Gate::authorize('view', $region);

        $provinces = $region->provinces()
            ->orderBy('name')
            ->paginate(20);

        return view('organization.geo.regions.show', compact('region', 'provinces'));
    }

    public function updateRegion(RegionRequest $request, Region $region): RedirectResponse
    {
        Gate::authorize('update', $region);

        $before = $region->only(['name', 'is_active']);

        $region->update([
            'name' => $request->input('name'),
            'is_active' => $request->boolean('is_active', true),
            'deactivated_at' => $request->boolean('is_active', true) ? null : ($region->deactivated_at ?? now()),
        ]);

        $this->logAudit('region.updated', Region::class, $region->id, $before, $region->only(['name', 'is_active']));

        return redirect()->route('admin.regions.index')
            ->with('status', 'Región actualizada correctamente.');
    }

    public function deactivateRegion(Region $region): RedirectResponse
    {
        Gate::authorize('deactivate', $region);

        $before = $region->only(['is_active']);
        $region->update(['is_active' => false, 'deactivated_at' => now()]);

        $this->logAudit('region.deactivated', Region::class, $region->id, $before, $region->only(['is_active']));

        return redirect()->route('admin.regions.index')
            ->with('status', 'Región desactivada correctamente.');
    }

    /* Provinces */

    public function provincesIndex(Request $request, Region $region): View
    {
        Gate::authorize('viewAny', Province::class);

        $provinces = $region->provinces()
            ->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('organization.geo.provinces.index', compact('region', 'provinces'));
    }

    public function storeProvince(ProvinceRequest $request, Region $region): RedirectResponse
    {
        Gate::authorize('create', Province::class);

        $province = Province::create([
            'organization_id' => auth()->user()->organization_id,
            'region_id' => $region->id,
            'name' => $request->input('name'),
            'is_active' => true,
        ]);

        $this->logAudit('province.created', Province::class, $province->id, null, $province->only(['name', 'region_id']));

        return redirect()->route('admin.regions.provinces.index', $region)
            ->with('status', 'Provincia creada correctamente.');
    }

    public function updateProvince(ProvinceRequest $request, Province $province): RedirectResponse
    {
        Gate::authorize('update', $province);

        $before = $province->only(['name', 'is_active']);

        $province->update([
            'name' => $request->input('name'),
            'is_active' => $request->boolean('is_active', true),
            'deactivated_at' => $request->boolean('is_active', true) ? null : ($province->deactivated_at ?? now()),
        ]);

        $this->logAudit('province.updated', Province::class, $province->id, $before, $province->only(['name', 'is_active']));

        return redirect()->route('admin.regions.provinces.index', $province->region)
            ->with('status', 'Provincia actualizada correctamente.');
    }

    public function deactivateProvince(Province $province): RedirectResponse
    {
        Gate::authorize('deactivate', $province);

        $before = $province->only(['is_active']);
        $province->update(['is_active' => false, 'deactivated_at' => now()]);

        $this->logAudit('province.deactivated', Province::class, $province->id, $before, $province->only(['is_active']));

        return redirect()->route('admin.regions.provinces.index', $province->region)
            ->with('status', 'Provincia desactivada correctamente.');
    }

    /* Districts */

    public function districtsIndex(Request $request, Province $province): View
    {
        Gate::authorize('viewAny', District::class);

        $districts = $province->districts()
            ->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('organization.geo.districts.index', compact('province', 'districts'));
    }

    public function storeDistrict(DistrictRequest $request, Province $province): RedirectResponse
    {
        Gate::authorize('create', District::class);

        $district = District::create([
            'organization_id' => auth()->user()->organization_id,
            'province_id' => $province->id,
            'name' => $request->input('name'),
            'is_active' => true,
        ]);

        $this->logAudit('district.created', District::class, $district->id, null, $district->only(['name', 'province_id']));

        return redirect()->route('admin.provinces.districts.index', $province)
            ->with('status', 'Distrito creado correctamente.');
    }

    public function updateDistrict(DistrictRequest $request, District $district): RedirectResponse
    {
        Gate::authorize('update', $district);

        $before = $district->only(['name', 'is_active']);

        $district->update([
            'name' => $request->input('name'),
            'is_active' => $request->boolean('is_active', true),
            'deactivated_at' => $request->boolean('is_active', true) ? null : ($district->deactivated_at ?? now()),
        ]);

        $this->logAudit('district.updated', District::class, $district->id, $before, $district->only(['name', 'is_active']));

        return redirect()->route('admin.provinces.districts.index', $district->province)
            ->with('status', 'Distrito actualizado correctamente.');
    }

    public function deactivateDistrict(District $district): RedirectResponse
    {
        Gate::authorize('deactivate', $district);

        $before = $district->only(['is_active']);
        $district->update(['is_active' => false, 'deactivated_at' => now()]);

        $this->logAudit('district.deactivated', District::class, $district->id, $before, $district->only(['is_active']));

        return redirect()->route('admin.provinces.districts.index', $district->province)
            ->with('status', 'Distrito desactivado correctamente.');
    }

    private function logAudit(string $action, string $entityType, int $entityId, ?array $before, ?array $after): void
    {
        AuditLog::create([
            'correlation_id' => (string) \Illuminate\Support\Str::uuid(),
            'created_at' => now(),
            'organization_id' => auth()->user()->organization_id,
            'actor_user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before_values' => $before,
            'after_values' => $after,
            'occurred_at' => now(),
        ]);
    }
}
