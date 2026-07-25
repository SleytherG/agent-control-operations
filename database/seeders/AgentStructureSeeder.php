<?php

namespace Database\Seeders;

use App\Modules\Agents\Models\Agent;
use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Database\Seeder;

class AgentStructureSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::first();
        if (! $org) {
            return;
        }

        $admin = User::where('role', Role::ADMINISTRADOR_PROPIETARIO)->first();
        if (! $admin) {
            $admin = User::factory()->create([
                'organization_id' => $org->id,
                'role' => Role::ADMINISTRADOR_PROPIETARIO,
                'status' => UserStatus::ACTIVE,
                'password_changed_at' => now(),
            ]);
        }

        $agents = [
            ['code' => 'AG-CENTRO', 'name' => 'Agente Centro', 'city' => 'Lima'],
            ['code' => 'AG-NORTE', 'name' => 'Agente Norte', 'city' => 'Lima'],
            ['code' => 'AG-SUR', 'name' => 'Agente Sur', 'city' => 'Arequipa'],
        ];

        foreach ($agents as $data) {
            Agent::firstOrCreate(
                ['organization_id' => $org->id, 'code' => $data['code']],
                [
                    'name' => $data['name'],
                    'city' => $data['city'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $operator = User::firstOrCreate(
            ['email_normalized' => 'operador@controloperaciones.local'],
            [
                'public_id' => (string) \Illuminate\Support\Str::uuid(),
                'organization_id' => $org->id,
                'username_normalized' => 'operador',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => Role::OPERADOR,
                'status' => UserStatus::ACTIVE,
                'password_changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $agent = Agent::where('code', 'AG-CENTRO')->first();
        if ($agent && $admin) {
            \App\Modules\Agents\Models\UserAgentAssignment::firstOrCreate(
                ['user_id' => $operator->id, 'agent_id' => $agent->id, 'is_active' => true],
                [
                    'assigned_by' => $admin->id,
                    'starts_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
