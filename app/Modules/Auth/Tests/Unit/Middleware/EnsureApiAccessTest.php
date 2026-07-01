<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit\Middleware;

use App\Modules\Auth\Middleware\EnsureApiAccess;
use App\Modules\Auth\Models\User;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class EnsureApiAccessTest extends TestCase
{
    use RefreshDatabase;

    private EnsureApiAccess $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new EnsureApiAccess();
        $this->seed(PlanSeeder::class);
    }

    public function test_free_user_receives_403_with_rfc_7807(): void
    {
        $freePlan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Free User',
            'email' => 'free@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $freePlan->id,
        ]);

        $request = Request::create('/api/blueprints', 'GET');
        $request->setUserResolver(fn () => $user);

        /** @var \Illuminate\Http\JsonResponse $response */
        $response = $this->middleware->handle($request, fn () => response('OK'));

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Forbidden', $data['title']);
        $this->assertEquals(403, $data['status']);
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('detail', $data);
    }

    public function test_pro_user_passes_through(): void
    {
        $proPlan = Plan::where('slug', 'pro')->first();
        $user = User::create([
            'name' => 'Pro User',
            'email' => 'pro@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $request = Request::create('/api/blueprints', 'GET');
        $request->setUserResolver(fn () => $user);

        /** @var \Illuminate\Http\Response $response */
        $response = $this->middleware->handle($request, fn () => response('OK'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_unauthenticated_request_returns_403(): void
    {
        $request = Request::create('/api/blueprints', 'GET');
        $request->setUserResolver(fn () => null);

        /** @var \Illuminate\Http\JsonResponse $response */
        $response = $this->middleware->handle($request, fn () => response('OK'));

        $this->assertEquals(403, $response->getStatusCode());
    }
}
