<?php

declare(strict_types=1);

namespace Tests\Feature\Config;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceFlagsTest extends TestCase
{
    public function test_marketplace_enabled_defaults_to_false(): void
    {
        $this->assertFalse(config('marketplace.enabled'));
    }

    public function test_billing_enabled_defaults_to_false(): void
    {
        $this->assertFalse(config('marketplace.billing_enabled'));
    }

    public function test_marketplace_enabled_can_be_set_via_env(): void
    {
        putenv('MARKETPLACE_ENABLED=true');
        // Re-cargar config para el test
        $this->app['config']->set('marketplace.enabled', env('MARKETPLACE_ENABLED', false));

        $this->assertTrue(config('marketplace.enabled'));

        // Restaurar
        putenv('MARKETPLACE_ENABLED');
        $this->app['config']->set('marketplace.enabled', env('MARKETPLACE_ENABLED', false));
    }

    public function test_billing_enabled_can_be_set_via_env(): void
    {
        putenv('BILLING_ENABLED=true');
        $this->app['config']->set('marketplace.billing_enabled', env('BILLING_ENABLED', false));

        $this->assertTrue(config('marketplace.billing_enabled'));

        putenv('BILLING_ENABLED');
        $this->app['config']->set('marketplace.billing_enabled', env('BILLING_ENABLED', false));
    }
}
