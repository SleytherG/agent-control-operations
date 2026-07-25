<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoRoutesRemovedTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_routes_are_inaccessible(): void
    {
        $demoPaths = [
            '/demo',
            '/demo/login',
            '/demo/dashboard',
            '/demo/operations',
            '/demo/example',
        ];

        foreach ($demoPaths as $path) {
            $response = $this->get($path);
            $this->assertEquals(
                404,
                $response->status(),
                "Demo route '{$path}' should return 404"
            );
        }
    }
}
