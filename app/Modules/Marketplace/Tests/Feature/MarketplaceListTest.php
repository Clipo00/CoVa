<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Models\BlueprintTag;
use App\Modules\Marketplace\Models\Vote;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MarketplaceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PlanSeeder::class);
        $this->seed(\Database\Seeders\MarketplaceSeeder::class);
    }

    private function getMarketplaceId(): int
    {
        return Organization::where('slug', 'cova-marketplace')->value('id');
    }

    public function test_renders_public_blueprints(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
            'owner_id' => $user->id,
        ]);

        Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'public-bp',
            'title' => 'Public Blueprint',
            'description' => 'A public blueprint',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'private-bp',
            'title' => 'Private Blueprint',
            'is_public' => false,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        Livewire::test(\App\Modules\Marketplace\Livewire\MarketplaceList::class)
            ->assertSee('Public Blueprint')
            ->assertDontSee('Private Blueprint');
    }

    public function test_pagination_works(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Paginated User',
            'email' => 'paginated@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Paginated Org',
            'slug' => 'paginated-org',
            'owner_id' => $user->id,
        ]);

        // Create 25 public blueprints (paginate 20)
        for ($i = 1; $i <= 25; $i++) {
            Blueprint::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'organization_id' => $this->getMarketplaceId(),
                'slug' => "bp-{$i}",
                'title' => "Blueprint {$i}",
                'is_public' => true,
                'tabs_config' => [],
                'created_by' => $user->id,
            ]);
        }

        Livewire::test(\App\Modules\Marketplace\Livewire\MarketplaceList::class)
            ->assertSee('Blueprint 1')
            ->assertSee('Blueprint 20')
            ->assertDontSee('Blueprint 21');
    }

    public function test_search_filters_by_title(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Search User',
            'email' => 'search@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Search Org',
            'slug' => 'search-org',
            'owner_id' => $user->id,
        ]);

        Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'laravel-bp',
            'title' => 'Laravel Setup',
            'description' => 'Laravel configuration blueprint',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'react-bp',
            'title' => 'React Setup',
            'description' => 'React configuration blueprint',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        Livewire::test(\App\Modules\Marketplace\Livewire\MarketplaceList::class)
            ->set('search', 'Laravel')
            ->assertSee('Laravel Setup')
            ->assertDontSee('React Setup');
    }

    public function test_sort_by_rating(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Sort User',
            'email' => 'sort@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Sort Org',
            'slug' => 'sort-org',
            'owner_id' => $user->id,
        ]);

        $lowRated = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'low-rated',
            'title' => 'Low Rated',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);
        $lowRated->votes_count = 1;
        $lowRated->save();

        $highRated = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'high-rated',
            'title' => 'High Rated',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);
        $highRated->votes_count = 10;
        $highRated->save();

        Livewire::test(\App\Modules\Marketplace\Livewire\MarketplaceList::class)
            ->set('sort', 'rating')
            ->assertSeeInOrder(['High Rated', 'Low Rated']);
    }

    public function test_sort_by_subscribers(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Sub Sort User',
            'email' => 'subsort@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Sub Sort Org',
            'slug' => 'subsort-org',
            'owner_id' => $user->id,
        ]);

        $lowSubs = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'low-subs',
            'title' => 'Low Subscribers',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);
        $lowSubs->subscribers_count = 2;
        $lowSubs->save();

        $highSubs = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'high-subs',
            'title' => 'High Subscribers',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);
        $highSubs->subscribers_count = 20;
        $highSubs->save();

        Livewire::test(\App\Modules\Marketplace\Livewire\MarketplaceList::class)
            ->set('sort', 'subscribers')
            ->assertSeeInOrder(['High Subscribers', 'Low Subscribers']);
    }

    public function test_sort_by_recent(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Recent User',
            'email' => 'recent@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Recent Org',
            'slug' => 'recent-org',
            'owner_id' => $user->id,
        ]);

        $oldBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'old-bp',
            'title' => 'Old Blueprint',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);
        $oldBp->created_at = now()->subDays(10);
        $oldBp->save();

        $recent = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'recent-bp',
            'title' => 'Recent Blueprint',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        Livewire::test(\App\Modules\Marketplace\Livewire\MarketplaceList::class)
            ->set('sort', 'recent')
            ->assertSeeInOrder(['Recent Blueprint', 'Old Blueprint']);
    }

    public function test_shows_organization_name(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Org Show User',
            'email' => 'orgshow@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Acme Corp',
            'slug' => 'acme-corp',
            'owner_id' => $user->id,
        ]);

        Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'org-bp',
            'title' => 'Acme Blueprint',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        Livewire::test(\App\Modules\Marketplace\Livewire\MarketplaceList::class)
            ->assertSee('CoVa Marketplace');
    }

    public function test_shows_votes_and_subscribers_count(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Counts User',
            'email' => 'counts@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Counts Org',
            'slug' => 'counts-org',
            'owner_id' => $user->id,
        ]);

        $countedBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'counts-bp',
            'title' => 'Counted Blueprint',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);
        $countedBp->votes_count = 5;
        $countedBp->subscribers_count = 3;
        $countedBp->save();

        Livewire::test(\App\Modules\Marketplace\Livewire\MarketplaceList::class)
            ->assertSee('5')
            ->assertSee('3');
    }

    public function test_tag_filter_by_single_tag(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Tag User',
            'email' => 'tag@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Tag Org',
            'slug' => 'tag-org',
            'owner_id' => $user->id,
        ]);

        $laravelBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'laravel-tag',
            'title' => 'Laravel Blueprint',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $reactBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'react-tag',
            'title' => 'React Blueprint',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        BlueprintTag::create(['blueprint_id' => $laravelBp->id, 'tag' => 'laravel']);
        BlueprintTag::create(['blueprint_id' => $reactBp->id, 'tag' => 'react']);

        Livewire::test(\App\Modules\Marketplace\Livewire\MarketplaceList::class)
            ->set('selectedTags', ['laravel'])
            ->assertSee('Laravel Blueprint')
            ->assertDontSee('React Blueprint');
    }

    public function test_tag_filter_by_multiple_tags(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Multi Tag User',
            'email' => 'multitag@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Multi Tag Org',
            'slug' => 'multitag-org',
            'owner_id' => $user->id,
        ]);

        $fullstack = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'fullstack',
            'title' => 'Fullstack App',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $pythonApp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'python',
            'title' => 'Python App',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $anotherBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'another',
            'title' => 'Another App',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        BlueprintTag::create(['blueprint_id' => $fullstack->id, 'tag' => 'laravel']);
        BlueprintTag::create(['blueprint_id' => $fullstack->id, 'tag' => 'react']);
        BlueprintTag::create(['blueprint_id' => $pythonApp->id, 'tag' => 'python']);
        BlueprintTag::create(['blueprint_id' => $anotherBp->id, 'tag' => 'go']);

        Livewire::test(\App\Modules\Marketplace\Livewire\MarketplaceList::class)
            ->set('selectedTags', ['laravel', 'react'])
            ->assertSee('Fullstack App')
            ->assertDontSee('Python App')
            ->assertDontSee('Another App');
    }

    public function test_tag_filter_shows_no_results(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'No Results User',
            'email' => 'noresults@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'No Results Org',
            'slug' => 'noresults-org',
            'owner_id' => $user->id,
        ]);

        $bp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $this->getMarketplaceId(),
            'slug' => 'bp-no-tag',
            'title' => 'No Tags Blueprint',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        BlueprintTag::create(['blueprint_id' => $bp->id, 'tag' => 'laravel']);

        Livewire::test(\App\Modules\Marketplace\Livewire\MarketplaceList::class)
            ->set('selectedTags', ['nonexistent'])
            ->assertSee(__('marketplace.no_results'));
    }
}
