<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class RetrainDemandCommandTest extends TestCase
{
    /**
     * Test that demand:retrain command executes successfully in dry-run mode.
     */
    public function test_demand_retrain_command_dry_run(): void
    {
        $this->artisan('demand:retrain', ['--dry-run' => true])
            ->expectsOutputToContain('CAMP FREEDIVEPH: AI DEMAND & REVENUE RETRAINING PIPELINE')
            ->expectsOutputToContain('DRY RUN: Retraining execution simulated successfully.')
            ->assertExitCode(0);
    }

    /**
     * Test that ml:retrain-demand alias also executes successfully in dry-run mode.
     */
    public function test_demand_retrain_alias_command_dry_run(): void
    {
        $this->artisan('ml:retrain-demand', ['--dry-run' => true])
            ->expectsOutputToContain('CAMP FREEDIVEPH: AI DEMAND & REVENUE RETRAINING PIPELINE')
            ->expectsOutputToContain('DRY RUN: Retraining execution simulated successfully.')
            ->assertExitCode(0);
    }

    /**
     * Test that demand:retrain command is registered in Laravel's nightly schedule.
     */
    public function test_demand_retrain_is_scheduled(): void
    {
        $schedule = app(Schedule::class);
        $events = collect($schedule->events());

        $retrainEvent = $events->first(function (Event $event) {
            return str_contains($event->command, 'demand:retrain');
        });

        $this->assertNotNull($retrainEvent, "demand:retrain command is not registered in schedule");
        $this->assertEquals('0 2 * * *', $retrainEvent->expression, "demand:retrain is not scheduled at 02:00 daily");
    }

    /**
     * Test that demand:retrain fails gracefully when given an invalid script path.
     */
    public function test_demand_retrain_handles_invalid_script_path(): void
    {
        $this->artisan('demand:retrain', ['--script' => '/invalid/path/nonexistent.py'])
            ->expectsOutputToContain('Demand model retraining failed: Script not found')
            ->assertExitCode(1);
    }
}
