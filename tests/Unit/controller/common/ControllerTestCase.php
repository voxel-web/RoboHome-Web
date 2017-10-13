<?php

namespace Tests\Unit\Controller\Common;

use Illuminate\Foundation\Testing\TestResponse;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class ControllerTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

//        putenv('DB_CONNECTION=none');
    }

    protected function assertRedirectedToRouteWith302(TestResponse $response, $route): void
    {
        $response->assertStatus(302);
        $response->assertRedirect($route);
    }
}
