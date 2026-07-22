<?php

namespace Tests\Feature\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorSessionHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_retrieves_only_own_sessions(): void
    {
        $this->markTestSkipped('Requires full integration.');
    }
}
