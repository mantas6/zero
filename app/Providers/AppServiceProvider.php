<?php

namespace App\Providers;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Memory usage at command start in bytes.
     */
    private int $startMemory = 0;

    /**
     * Timestamp at command start in microseconds.
     */
    private float $startTime = 0.0;

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!config('app.debug')) {
            return;
        }

        Event::listen(CommandStarting::class, function (): void {
            $this->startMemory = memory_get_usage();
            $this->startTime = microtime(true);
        });

        Event::listen(CommandFinished::class, function (CommandFinished $event): void {
            $endMemory = memory_get_usage();
            $peakMemory = memory_get_peak_usage();
            $elapsed = microtime(true) - $this->startTime;

            $output = $event->output;
            $output->writeln('');
            $output->writeln(sprintf(
                '<fg=gray>[debug] Memory: %s (peak: %s, delta: %s) | Time: %s</>',
                $this->formatBytes($endMemory),
                $this->formatBytes($peakMemory),
                $this->formatBytes($endMemory - $this->startMemory),
                $this->formatDuration($elapsed),
            ));
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        Model::unguard();
    }

    /**
     * Format bytes into a human-readable string.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $absBytes = abs($bytes);
        $sign = $bytes < 0 ? '-' : '';

        if ($absBytes === 0) {
            return '0 B';
        }

        $power = (int) floor(log($absBytes, 1024));
        $power = min($power, count($units) - 1);

        return sprintf('%s%.2f %s', $sign, $absBytes / (1024 ** $power), $units[$power]);
    }

    /**
     * Format a duration in seconds into a human-readable string.
     */
    private function formatDuration(float $seconds): string
    {
        if ($seconds < 1) {
            return sprintf('%.1fms', $seconds * 1000);
        }

        return sprintf('%.2fs', $seconds);
    }
}
