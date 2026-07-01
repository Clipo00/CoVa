<?php

declare(strict_types=1);

namespace App\Commands;

use App\ApiClient;
use Illuminate\Console\Command;

/**
 * List accessible blueprints as a formatted table.
 *
 * Fetches blueprints from the CoVa API via GET /api/blueprints and displays
 * them in a table. With the --with-descriptions (-g) flag, includes the
 * description column for each blueprint.
 *
 * Usage:
 *   covar list
 *   covar list -g
 *   covar list --with-descriptions
 */
class ListCommand extends Command
{
    /**
     * @var string The console command signature.
     */
    protected $signature = 'list
        {-g|--with-descriptions : Include the description column}';

    /**
     * @var string The console command description.
     */
    protected $description = 'List accessible blueprints';

    private ?ApiClient $apiClient;

    /**
     * @param ApiClient|null $apiClient Optional injected client for testing
     */
    public function __construct(?ApiClient $apiClient = null)
    {
        parent::__construct();
        $this->apiClient = $apiClient;
    }

    /**
     * Execute the console command.
     *
     * 1. Fetches blueprints via the API client
     * 2. Renders a table with slug + title (and description with -g)
     * 3. Handles errors: 401, 403, network, and generic runtime exceptions
     *
     * @return int Exit code (0 for success, 1 for error)
     */
    public function handle(): int
    {
        $client = $this->apiClient ?? new ApiClient();

        try {
            $result = $client->get('/api/blueprints');
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $blueprints = $result['data'] ?? [];

        if (empty($blueprints)) {
            $this->info('No blueprints found');

            return 0;
        }

        $rows = [];
        $withDescriptions = (bool) $this->option('with-descriptions');

        foreach ($blueprints as $blueprint) {
            $row = [
                $blueprint['slug'] ?? '',
                $blueprint['title'] ?? '',
            ];

            if ($withDescriptions) {
                $row[] = $blueprint['description'] ?? '';
            }

            $rows[] = $row;
        }

        $headers = $withDescriptions
            ? ['Slug', 'Title', 'Description']
            : ['Slug', 'Title'];

        $this->table($headers, $rows);

        return 0;
    }
}
