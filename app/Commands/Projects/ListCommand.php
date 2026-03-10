<?php

namespace App\Commands\Projects;

use App\Http\Integrations\Toggl\TogglConnector;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LaravelZero\Framework\Commands\Command;

class ListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all Toggl projects';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $connector = new TogglConnector;
        } catch (ModelNotFoundException) {
            $this->components->error('Not authenticated. Run the authenticate command first.');

            return self::FAILURE;
        }

        $response = $connector->projects();

        $response->collect()
            ->pluck('name')
            ->each(fn (string $name) => $this->line($name));

        return self::SUCCESS;
    }
}
