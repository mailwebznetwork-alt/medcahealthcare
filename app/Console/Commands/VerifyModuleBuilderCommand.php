<?php

namespace App\Console\Commands;

use App\Services\DynamicModules\ModuleBuilderVerificationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:verify-module-builder {--keep : Leave the TestProduct verification module in the database after the run}')]
#[Description('Verify Dynamic Module Builder schema, CRUD, validation, and field-removal wiring')]
class VerifyModuleBuilderCommand extends Command
{
    public function __construct(
        private readonly ModuleBuilderVerificationService $verification,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Dynamic Module Builder — System Wiring Verification');
        $this->newLine();

        $report = $this->verification->run(cleanup: ! $this->option('keep'));

        $this->line('Table convention: '.$report['table_name']);
        $this->comment($report['note']);
        $this->newLine();

        foreach ($report['checks'] as $check) {
            $status = ($check['passed'] ?? false) ? '<fg=green>PASS</>' : '<fg=red>FAIL</>';
            $this->line("[{$status}] {$check['name']}");

            if (isset($check['details']) && is_array($check['details'])) {
                foreach ($check['details'] as $key => $value) {
                    $formatted = is_array($value) ? json_encode($value) : (string) $value;
                    $this->line("  - {$key}: {$formatted}");
                }
            }
        }

        $this->newLine();
        if ($report['passed']) {
            $this->info('System Wiring Report: ALL CHECKS PASSED');

            return self::SUCCESS;
        }

        $this->error('System Wiring Report: ONE OR MORE CHECKS FAILED');

        return self::FAILURE;
    }
}
