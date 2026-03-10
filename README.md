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

## Authentication

Authenticate with your Toggl API token:

```bash
zero auth
```

You can find your API token on your [Toggl Profile page](https://track.toggl.com/profile).

## Commands

### Projects

```bash
zero projects:list          # List all Toggl projects
zero projects:add           # Add a Toggl project to the local database (interactive search)
```

### Tasks

```bash
zero tasks:sync             # Sync tasks from Toggl for all local projects
zero tasks:sync "project"   # Sync tasks for a specific project (partial match)
zero tasks:cp "project"     # Copy a task name to clipboard (interactive search)
zero tasks:cp "project" -y  # Sync tasks first, then copy
```

### Time Tracking

```bash
zero time:start <task-id>   # Start tracking time for a task
zero time:stop              # Stop the current timer
zero time:current           # Show the current task name
zero time:elapsed           # Show elapsed time on the current task
zero time:list              # List today's time entries
zero time:push              # Push local entries to Toggl (WIP)
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
authenticate   projects:add   projects:list  tasks:cp       tasks:sync
time:current   time:elapsed   time:list      time:push      time:start
time:stop
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
