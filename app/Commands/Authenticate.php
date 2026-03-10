<?php

namespace App\Commands;

use App\Http\Integrations\Toggl\Requests\MeRequest;
use App\Http\Integrations\Toggl\TogglConnector;
use App\Token;
use Illuminate\Support\Facades\Process;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\text;

class Authenticate extends Command
{
    private const TOGGL_PROFILE_URL = 'https://track.toggl.com/profile';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authenticate';

    protected $aliases = ['auth'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Authenticate with Toggl using an API token';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $token = Token::query()
            ->firstOrNew(['name' => 'default']);

        if ($token->isClean()) {
            $this->components->warn('Token is already defined');
        }

        $this->openTogglProfilePage();

        $tokenString = text(
            label: 'Paste your Toggl API token',
            required: true,
            hint: 'Find it at '.self::TOGGL_PROFILE_URL,
        );

        $connector = new TogglConnector($tokenString);

        $workspaceId = $connector->send(new MeRequest)
            ->json('default_workspace_id');

        $token->contents = $tokenString;
        $token->default_workspace_id = $workspaceId;
        $token->save();

        $this->components->info('Token saved successfully.');
    }

    private function openTogglProfilePage(): void
    {
        $command = match (PHP_OS_FAMILY) {
            'Darwin' => 'open',
            'Windows' => 'start',
            default => 'xdg-open',
        };

        $result = Process::run($command.' '.escapeshellarg(self::TOGGL_PROFILE_URL));

        if ($result->successful()) {
            $this->components->info('Opening Toggl profile page in your browser...');
        } else {
            $this->components->warn('Could not open browser. Visit this URL to find your API token:');
            $this->line(self::TOGGL_PROFILE_URL);
        }
    }
}
