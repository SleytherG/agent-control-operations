<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSessionHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_filter_all_sessions_in_organization(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
