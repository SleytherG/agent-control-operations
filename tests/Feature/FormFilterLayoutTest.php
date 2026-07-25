<?php

namespace Tests\Feature;

use App\Modules\IdentityAccess\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormFilterLayoutTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->administradorPropietario()->create([
            'password_changed_at' => now(),
        ]);

        $this->actingAsJwt($this->admin);
    }

    public function test_reported_forms_use_shared_form_layout_markup(): void
    {
        foreach ([
            'admin.agents.create',
            'admin.users.create',
            'daily-closures.create',
            'admin.operation-types.create',
        ] as $route) {
            $response = $this->get(route($route));

            $response->assertOk()
                ->assertSee('form-page', false)
                ->assertSee('form-layout', false)
                ->assertSee('form-actions', false);
        }

        $this->get(route('daily-closures.create'))
            ->assertSee('form-check', false);
    }

    public function test_filter_views_use_shared_filter_markup(): void
    {
        $routeMarkers = [
            'admin.agents.index' => ['filter-bar', 'filter-bar-actions', 'Filtrar por código', 'Filtrar por nombre'],
            'admin.users.index' => ['filter-bar', 'filter-bar-actions', 'Filtrar por usuario', 'Filtrar por correo'],
            'admin.operation-types.index' => ['filter-bar', 'filter-bar-actions', 'Filtrar por nombre', 'Filtrar por descripción', 'Filtrar por orden'],
            'daily-closures.index' => ['filter-bar', 'filter-bar-actions'],
            'operations.index' => ['filter-bar', 'filter-bar-actions', 'Filtrar', 'Limpiar'],
            'sessions.index' => ['filter-panel', 'filter-form', 'filter-form-actions'],
            'admin.dashboard.operators' => ['filter-panel', 'filter-form', 'form-select--multiple'],
        ];

        foreach ($routeMarkers as $route => $markers) {
            $response = $this->get(route($route));

            $response->assertOk();

            foreach ($markers as $marker) {
                $response->assertSee($marker, false);
            }
        }
    }

    public function test_list_primary_actions_share_the_heading_row_before_filters(): void
    {
        $routeMarkers = [
            'admin.agents.index' => 'Nuevo Agente',
            'admin.users.index' => 'Nuevo Operador',
            'admin.operation-types.index' => 'Nuevo Tipo',
        ];

        foreach ($routeMarkers as $route => $label) {
            $response = $this->get(route($route));

            $response->assertOk()
                ->assertSee('admin-page-header', false)
                ->assertSee('filter-bar--standalone', false)
                ->assertDontSee('page-toolbar__primary', false)
                ->assertSee($label);

            $content = $response->getContent();
            $this->assertLessThan(
                strpos($content, 'filter-bar--standalone'),
                strpos($content, 'admin-page-header'),
            );
        }

        $markup = view('components.screen.admin-filters', [
            'regions' => collect(),
            'agents' => collect(),
            'types' => collect(),
            'operators' => collect(),
            'currentFilters' => [],
            'period' => 'month',
            'date' => now()->format('Y-m-d'),
        ])->render();

        $this->assertStringContainsString('admin-filters-form', $markup);
        $this->assertStringContainsString('admin-filters-actions', $markup);
    }
}
