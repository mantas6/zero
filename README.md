# Zero

A command-line interface for [Toggl Track](https://toggl.com/track/) time tracking, built with [Laravel Zero](https://laravel-zero.com/).

## Requirements

- PHP 8.2+
- SQLite

## Installation

```bash
composer global require mantas6/zero
```

Or clone and install locally:

```bash
git clone https://github.com/mantas6/zero.git
cd zero
composer install
php zero migrate
```

## Workflow

The typical workflow follows this sequence:

1. **Authenticate** — run `zero auth` to store your Toggl API token
2. **Sync projects** — run `zero projects:sync` to pull your workspace projects locally
3. **Configure tags** — run `zero projects:tags` to set default tags per project (optional)
4. **Sync tasks** — run `zero tasks:sync` to pull tasks for your local projects
5. **Track time** — use `time:start`, `time:stop`, `time:current`, `time:elapsed`, and `time:list` throughout the day
6. **Push to Toggl** — run `zero time:push` to send completed entries to Toggl

## Authentication

Authenticate with your Toggl API token:

```bash
zero auth
```

This opens your [Toggl Profile page](https://track.toggl.com/profile) in the browser where you can copy your API token. If the browser cannot be opened, the URL is printed as a fallback.

## Commands

### Projects

```bash
zero projects:sync          # Sync all workspace projects from Toggl
zero projects:sync --active  # Sync only active projects
zero projects:list          # List locally synced projects (active only by default)
zero projects:list --all    # Include archived projects
zero projects:add           # Add a single Toggl project interactively
zero projects:tags          # Configure default tags for a project
```

### Tasks

```bash
zero tasks:sync             # Sync tasks from Toggl for all local projects
zero tasks:sync "project"   # Sync tasks for a specific project (partial match)
zero tasks:sync --active    # Fetch only active tasks from Toggl
zero tasks:cp "project"     # Copy a task name to clipboard (interactive search)
zero tasks:cp "project" -y  # Sync tasks first, then copy
zero tasks:cp --all         # Include inactive (done) tasks in selection
```

### Time Tracking

```bash
zero time:start <task-id>   # Start tracking time for a task
zero time:stop              # Stop the current timer
zero time:current           # Show the current task name
zero time:current --with-project  # Include project name prefix
zero time:elapsed           # Show elapsed time on the current task
zero time:list              # List today's time entries
zero time:total             # Show total tracked time for today
zero time:push              # Push local entries to Toggl (create or update)
```

### Aliases

Several commands have short aliases for quick access:

| Alias  | Command        |
|--------|----------------|
| `auth` | `authenticate` |
| `add`  | `projects:add` |
| `sync` | `tasks:sync`   |
| `y`    | `tasks:sync`   |
| `cp`   | `tasks:cp`     |

## Environment Variables

| Variable | Description |
|----------|-------------|
| `TG_PROJECT_ID` | Filter all output to a specific local project ID. When set, commands like `time:list`, `time:start`, `time:total`, and `tasks:sync` scope their output to the specified project. When unset, all projects are shown. |
| `ZERO_DEBUG` | Set to `1` to display memory usage and execution time after each command. |

## Shell Completion

Zero supports autocompletion for bash, zsh, and fish shells via the built-in `completion` command. This is especially useful for commands that deal with long lists of projects and tasks, such as `tasks:sync`, `tasks:cp`, and `time:start`.

### Bash

Add to your `~/.bashrc`:

```bash
eval "$(zero completion bash)"
```

Or install globally:

```bash
zero completion bash | sudo tee /etc/bash_completion.d/zero > /dev/null
```

After adding, restart your shell or run:

```bash
source ~/.bashrc
```

### Zsh

Add to your `~/.zshrc`:

```zsh
eval "$(zero completion zsh)"
```

Or install as a completion function (pick one of the directories from your `$fpath`):

```zsh
zero completion zsh | sudo tee "${fpath[1]}/_zero" > /dev/null
```

Make sure completion is enabled in your `~/.zshrc` (add these lines before the `eval` line if not already present):

```zsh
autoload -Uz compinit
compinit
```

After adding, restart your shell or run:

```zsh
source ~/.zshrc
```

### Fish

Add to your fish config:

```fish
zero completion fish | source
```

Or install persistently:

```fish
zero completion fish > ~/.config/fish/completions/zero.fish
```

### Verifying Completion

After setup, type `zero ` and press `Tab` to see available commands:

```
$ zero <TAB>
authenticate   projects:add   projects:list  projects:sync  projects:tags
tasks:cp       tasks:sync     time:current   time:elapsed   time:list
time:push      time:start     time:stop      time:total
```

Command arguments and options are also completed:

```
$ zero tasks:sync <TAB>     # completes project-name argument
$ zero tasks:cp --<TAB>     # completes --sync option
```

## Development

### Code Quality

The project uses three code quality tools:

```bash
vendor/bin/pint             # Code style (Laravel Pint)
vendor/bin/rector process   # Automated refactoring (Rector)
vendor/bin/phpstan analyse  # Static analysis (PHPStan)
```

### Testing

```bash
php zero test
```

## License

MIT
