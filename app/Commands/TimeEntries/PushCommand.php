<?php

namespace App\Commands\TimeEntries;

use App\Commands\Concerns\ResolvesProjectFilter;
use App\Http\Integrations\Toggl\TogglConnector;
use App\Project;
use App\TimeEntry;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LaravelZero\Framework\Commands\Command;
use Saloon\Exceptions\Request\RequestException;

class PushCommand extends Command
{
    use ResolvesProjectFilter;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'time:push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push local time entries to Toggl';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $connector = new TogglConnector;
        } catch (ModelNotFoundException) {
            $this->components->error('Not authenticated. Run the "auth" command first.');

            return self::FAILURE;
        }

        $projectFilter = $this->warnIfProjectFilterInvalid();

        $query = TimeEntry::query()
            ->whereToday()
            ->whereNotNull('stopped_at');

        if ($projectFilter instanceof Project) {
            $query->forProject($projectFilter);
        }

        $entries = $query->get();

        if ($entries->isEmpty()) {
            $this->components->info('No completed time entries to push.');

            return self::SUCCESS;
        }

        $newEntries = $entries->whereNull('ext_id');
        $existingEntries = $entries->whereNotNull('ext_id');

        $created = 0;
        $updated = 0;
        $failed = 0;

        foreach ($newEntries as $entry) {
            try {
                $response = $connector->createEntry($entry);
                $togglId = $response->json('id');

                $entry->update(['ext_id' => $togglId]);
                $created++;

                $this->components->twoColumnDetail(
                    $entry->task->name ?? '(unknown task)',
                    '<fg=green>created</>'
                );
            } catch (RequestException $e) {
                $failed++;
                $this->components->twoColumnDetail(
                    $entry->task->name ?? '(unknown task)',
                    '<fg=red>failed: ' . $e->getMessage() . '</>'
                );
            }
        }

        foreach ($existingEntries as $entry) {
            try {
                $connector->updateEntry($entry);
                $updated++;

                $this->components->twoColumnDetail(
                    $entry->task->name ?? '(unknown task)',
                    '<fg=yellow>updated</>'
                );
            } catch (RequestException $e) {
                $failed++;
                $this->components->twoColumnDetail(
                    $entry->task->name ?? '(unknown task)',
                    '<fg=red>failed: ' . $e->getMessage() . '</>'
                );
            }
        }

        $this->newLine();
        $this->components->info("Push complete: {$created} created, {$updated} updated, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
