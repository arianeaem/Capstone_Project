<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class RetrainDemandForecastCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ml:retrain-demand 
                            {--script= : Specific path to Python retrain pipeline script} 
                            {--python= : Specific path to Python interpreter binary} 
                            {--dry-run : Simulate execution without spawning long-running processes} 
                            {--timeout=1800 : Maximum process timeout in seconds (default: 30 minutes)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger the automated daily machine learning retraining pipeline for demand and revenue forecasting';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);

        $pythonBinary = $this->option('python') 
            ?: config('services.ml_demand.python_path', config('services.ml_safety.python_path', 'python'));

        $scriptPath = $this->option('script') 
            ?: config('services.ml_demand.retrain_script', base_path('../CapstoneProject_ML/retrain_pipeline.py'));

        $workingDir = config('services.ml_demand.working_dir', dirname($scriptPath));

        $isDryRun = (bool) $this->option('dry-run');
        $timeout = (int) $this->option('timeout');

        $this->info("====================================================================");
        $this->info("CAMP FREEDIVEPH: DAILY AI DEMAND & REVENUE RETRAINING PIPELINE");
        $this->info("====================================================================");
        $this->line("Python Interpreter: <fg=cyan>{$pythonBinary}</>");
        $this->line("Pipeline Script:    <fg=yellow>{$scriptPath}</>");
        $this->line("Working Directory:  <fg=yellow>{$workingDir}</>");
        $this->line("Execution Timeout:  <fg=magenta>{$timeout}s</>");

        if (!file_exists($scriptPath) && !$isDryRun) {
            $this->error("Retrain script not found at path: {$scriptPath}");
            Log::error("[ml:retrain-demand] Target script not found: {$scriptPath}");
            return self::FAILURE;
        }

        if ($isDryRun) {
            $this->warn("DRY RUN: Retraining execution simulated successfully.");
            return self::SUCCESS;
        }

        Log::info("[ml:retrain-demand] Starting daily demand forecasting retraining", [
            'script' => $scriptPath,
            'python' => $pythonBinary,
            'working_dir' => $workingDir,
        ]);

        try {
            $process = new Process([$pythonBinary, $scriptPath], $workingDir);
            $process->setTimeout($timeout);
            $process->setIdleTimeout(300);

            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    $this->output->write("<fg=red>{$buffer}</>");
                } else {
                    $this->output->write($buffer);
                }
            });

            if (!$process->isSuccessful()) {
                Log::error("[ml:retrain-demand] Pipeline execution failed", [
                    'exit_code' => $process->getExitCode(),
                    'error_output' => $process->getErrorOutput(),
                ]);
                $this->error("ML Demand Retrain Pipeline failed with exit code: " . $process->getExitCode());
                return self::FAILURE;
            }

            $duration = round(microtime(true) - $startTime, 2);
            $this->info("Daily Demand Retrain Pipeline completed in {$duration}s");
            Log::info("[ml:retrain-demand] Daily retraining completed successfully in {$duration}s");

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error("Pipeline exception: " . $e->getMessage());
            Log::error("[ml:retrain-demand] Process execution exception: " . $e->getMessage(), ['exception' => $e]);
            return self::FAILURE;
        }
    }
}
