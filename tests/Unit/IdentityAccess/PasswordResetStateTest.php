<?php

namespace Tests\Unit\IdentityAccess;

use App\Modules\IdentityAccess\Domain\Enums\PasswordResetStatus;
use PHPUnit\Framework\TestCase;

class PasswordResetStateTest extends TestCase
{
    public function test_only_approved_state_transitions_are_allowed(): void
    {
        $this->assertTrue(PasswordResetStatus::PENDING->canTransitionTo(PasswordResetStatus::CONSUMED));
        $this->assertTrue(PasswordResetStatus::PENDING->canTransitionTo(PasswordResetStatus::SUPERSEDED));
        $this->assertTrue(PasswordResetStatus::PENDING->canTransitionTo(PasswordResetStatus::EXPIRED));
        $this->assertTrue(PasswordResetStatus::CONSUMED->canTransitionTo(PasswordResetStatus::COMPLETED));
        $this->assertTrue(PasswordResetStatus::CONSUMED->canTransitionTo(PasswordResetStatus::SUPERSEDED));
        $this->assertFalse(PasswordResetStatus::COMPLETED->canTransitionTo(PasswordResetStatus::PENDING));
    }
}
