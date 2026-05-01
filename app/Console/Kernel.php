<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\RefactorPhase3FinalAuditCommand;
use App\Console\Commands\RHFolhaProcessarCompetenciaCommand;
use App\Console\Commands\RHRescisaoAutomationAuditCommand;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('project:inventory')->dailyAt((string) config('hardening.schedule.project_inventory', '02:10'))->withoutOverlapping();
        $schedule->command('saas:snapshot-usage')->hourly()->withoutOverlapping();
        $schedule->job(new \App\Jobs\GenerateSaasUsageSnapshotJob(1))->hourly()->name('saas_snapshot_job_empresa_1')->withoutOverlapping();
        $schedule->job(new \App\Jobs\DispatchPremiumNotificationsJob(1))->everyThirtyMinutes()->name('saas_premium_notifications_job_empresa_1')->withoutOverlapping();
        $schedule->command('sefaz:diagnostico')->dailyAt((string) config('hardening.schedule.sefaz_diagnostico', '06:30'))->withoutOverlapping();
        $schedule->command('pdv:offline-retry')->everyTenMinutes()->withoutOverlapping();
        $schedule->command('stock:reconcile 1')->dailyAt((string) config('hardening.schedule.stock_reconcile', '03:40'))->withoutOverlapping();
        $schedule->command('schema:drift-report --write')->dailyAt((string) config('hardening.schedule.schema_drift_report', '04:00'))->withoutOverlapping();
        $schedule->command('system:healthcheck --write')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('refactor:governance-report --write')->dailyAt((string) config('hardening.schedule.refactor_governance_report', '04:20'))->withoutOverlapping();
        $schedule->command('stock:write-guard-report --write')->dailyAt((string) config('hardening.schedule.stock_write_guard_report', '04:35'))->withoutOverlapping();
        $schedule->command('fiscal:operations-report 1 --write')->dailyAt((string) config('hardening.schedule.fiscal_operations_report', '04:50'))->withoutOverlapping();
        $schedule->command('hardening:final-report --write')->dailyAt((string) config('hardening.schedule.hardening_final_report', '05:05'))->withoutOverlapping();
        $schedule->command('deadcode:candidates-report --write')->dailyAt((string) config('hardening.schedule.deadcode_candidates_report', '05:20'))->withoutOverlapping();
        $schedule->command('legacy:cutoff-readiness-report --write')->dailyAt((string) config('cutoff.schedule.legacy_cutoff_readiness_report', '05:35'))->withoutOverlapping();
        $schedule->command('performance:baseline-report --write')->dailyAt((string) config('cutoff.schedule.performance_baseline_report', '05:50'))->withoutOverlapping();
        $schedule->command('rh:dossie-sync')->dailyAt('06:05')->withoutOverlapping();
        $schedule->command('rh:rescisao-audit')->dailyAt('06:20')->withoutOverlapping();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
