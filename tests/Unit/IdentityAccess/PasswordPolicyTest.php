<?php

namespace Tests\Unit\IdentityAccess;

use App\Modules\IdentityAccess\Services\PasswordPolicy;
use Tests\TestCase;

class PasswordPolicyTest extends TestCase
{
    public function test_temporary_password_has_required_length_and_character_classes(): void
    {
        config()->set('session-security.password_reset.temporary_length', 20);

        $password = app(PasswordPolicy::class)->generateTemporary();

        $this->assertSame(20, strlen($password));
        $this->assertMatchesRegularExpression('/[A-Za-z]/', $password);
        $this->assertMatchesRegularExpression('/[0-9]/', $password);
        $this->assertMatchesRegularExpression('/[^A-Za-z0-9]/', $password);
        $this->assertDoesNotMatchRegularExpression('/\\s/', $password);
    }

    public function test_generated_passwords_are_not_repeated(): void
    {
        $policy = app(PasswordPolicy::class);
        $values = array_map(fn () => $policy->generateTemporary(), range(1, 50));

        $this->assertCount(50, array_unique($values));
    }
}
