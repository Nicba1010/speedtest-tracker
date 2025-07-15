<?php

namespace App\Actions;

use App\Actions\Ookla\RunSpeedtest;
use Cron\CronExpression;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckForScheduledSpeedtests
{
    use AsAction;

    public function handle(): void
    {
        $schedule = config('speedtest.schedule');

        if (blank($schedule) || $schedule === false) {
            return;
        }

        if (! $this->isSpeedtestDue(schedule: $schedule)) {
            return;
        }

        if (config('speedtest.mode', 'random') === 'sequential') {
            $servers = array_filter(
                array_map('trim', explode(',', (string) config('speedtest.servers')))
            );

            if (count($servers)) {
                foreach ($servers as $serverId) {
                    RunSpeedtest::run(
                        scheduled: true,
                        serverId: (int) $serverId,
                    );
                }

                return;
            }
        }

        RunSpeedtest::run(scheduled: true);
    }

    /**
     * Assess if a speedtest is due to run based on the schedule.
     */
    private function isSpeedtestDue(string $schedule): bool
    {
        $cron = new CronExpression($schedule);

        return $cron->isDue(
            currentTime: now(),
            timeZone: config('app.display_timezone')
        );
    }
}
