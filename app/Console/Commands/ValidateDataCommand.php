<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ValidateDataCommand extends Command
{
    protected $signature = 'rts:validate-data {--json : Output results as JSON}';

    protected $description = 'Run a comprehensive data integrity and validity check across all master data tables';

    private array $issues = [];

    private array $summary = [];

    public function handle()
    {
        $this->info('🔍 Running Data Integrity & Validity Check...');
        $this->newLine();
        $startTime = microtime(true);

        $this->checkDanglingVehicleFKs();
        $this->checkUnlinkedServiceHistories();
        $this->checkAnonymousCustomerCollision();
        $this->checkSuspiciouslyLargeFleets();
        $this->checkDuplicateChassisNumbers();
        $this->checkEmailFormats();
        $this->checkPhoneFormats();
        $this->checkQualityScoreDistribution();
        $this->checkOrphanGhostVehicles();

        $elapsed = round(microtime(true) - $startTime, 1);

        if ($this->option('json')) {
            $this->line(json_encode([
                'issues' => $this->issues,
                'summary' => $this->summary,
                'elapsed' => $elapsed,
            ], JSON_PRETTY_PRINT));

            return Command::SUCCESS;
        }

        $this->displayResults($elapsed);

        return empty($this->issues) ? Command::SUCCESS : Command::FAILURE;
    }

    // ── Checks ───────────────────────────────────────────────────────────────

    private function checkDanglingVehicleFKs(): void
    {
        $count = DB::table('master_vehicles as mv')
            ->leftJoin('master_customers as mc', 'mv.primary_customer_id', '=', 'mc.id')
            ->whereNotNull('mv.primary_customer_id')
            ->whereNull('mc.id')
            ->count();

        $this->addResult('Dangling Vehicle FK', $count, 0,
            'Vehicles with primary_customer_id pointing to non-existent customers',
            'critical');
    }

    private function checkUnlinkedServiceHistories(): void
    {
        $noVehicle = DB::table('service_histories')
            ->whereNull('vehicle_id')
            ->where('CHASN', '!=', '')
            ->whereNotNull('CHASN')
            ->count();

        $noCustomer = DB::table('service_histories')
            ->whereNull('customer_id')
            ->where('CCUST', '!=', '')
            ->whereNotNull('CCUST')
            ->count();

        $total = DB::table('service_histories')->count();

        $this->addResult('Service Histories without Vehicle Link', $noVehicle, 0,
            'Service histories with a chassis_no but no resolved vehicle_id (run rts:link-history or rts:recover-ghosts)',
            'warning');

        $this->addResult('Service Histories without Customer Link', $noCustomer, 0,
            'Service histories with a CCUST but no resolved customer_id (run rts:link-history)',
            'warning');

        $this->summary[] = ['Total Service Histories', number_format($total)];
    }

    private function checkAnonymousCustomerCollision(): void
    {
        // The bug we just fixed — detect if it exists again
        $count = DB::table('master_customers')
            ->whereRaw("(name IS NULL OR name = '') AND (phone_fingerprint IS NULL OR phone_fingerprint = '')")
            ->count();

        $maxVehicles = DB::table('master_vehicles')
            ->select('primary_customer_id', DB::raw('count(*) as c'))
            ->groupBy('primary_customer_id')
            ->orderByDesc('c')
            ->first();

        $this->addResult('Customers with No Name AND No Phone (collision risk)', $count, 0,
            'Records that could collapse into a single bucket during re-import (see: Anonymous Collision Fix)',
            'warning');

        if ($maxVehicles && $maxVehicles->c > 500) {
            $this->issues[] = [
                'check' => 'Suspiciously Large Fleet (possible collision)',
                'count' => $maxVehicles->c,
                'severity' => 'critical',
                'detail' => "Customer ID {$maxVehicles->primary_customer_id} has {$maxVehicles->c} vehicles — possible data collision",
            ];
        }
    }

    private function checkSuspiciouslyLargeFleets(): void
    {
        $largeFleets = DB::table('master_vehicles')
            ->select('primary_customer_id', DB::raw('count(*) as c'))
            ->whereNotNull('primary_customer_id')
            ->groupBy('primary_customer_id')
            ->having('c', '>', 200)
            ->orderByDesc('c')
            ->get();

        foreach ($largeFleets as $fleet) {
            $customer = DB::table('master_customers')->where('id', $fleet->primary_customer_id)->first(['name', 'company_name']);
            $name = $customer->company_name ?: $customer->name ?: '(No Name)';
            $this->issues[] = [
                'check' => 'Large Fleet',
                'count' => $fleet->c,
                'severity' => $fleet->c > 500 ? 'critical' : 'warning',
                'detail' => "Customer #{$fleet->primary_customer_id} ({$name}) has {$fleet->c} vehicles",
            ];
        }

        $this->summary[] = ['Customers with >200 vehicles', $largeFleets->count()];
    }

    private function checkDuplicateChassisNumbers(): void
    {
        // Should be zero due to UNIQUE constraint, but validates the constraint is working
        $dups = DB::table('master_vehicles')
            ->select('chassis_no', DB::raw('count(*) as c'))
            ->whereNotNull('chassis_no')
            ->groupBy('chassis_no')
            ->having('c', '>', 1)
            ->count();

        $this->addResult('Duplicate Chassis Numbers', $dups, 0,
            'Chassis numbers appearing more than once (violates uniqueness constraint)',
            'critical');
    }

    private function checkEmailFormats(): void
    {
        // Check for obviously malformed emails (no @ or no .)
        $invalid = DB::table('master_customers')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where(function ($q) {
                $q->whereRaw("email NOT LIKE '%@%'")
                    ->orWhereRaw("email NOT LIKE '%.%'");
            })
            ->count();

        $total = DB::table('master_customers')->whereNotNull('email')->where('email', '!=', '')->count();

        $this->addResult('Invalid Email Formats', $invalid, 0,
            "Customer emails missing '@' or '.' character",
            'warning');

        $this->summary[] = ['Customers with email', number_format($total)];
    }

    private function checkPhoneFormats(): void
    {
        // Check for phones that look like placeholders: '0', '-', 'N/A'
        $placeholder = DB::table('master_customers')
            ->whereNotNull('telp_1')
            ->whereIn('telp_1', ['0', '-', 'N/A', 'n/a', 'nan', '00000000'])
            ->count();

        $this->addResult('Placeholder Phone Numbers', $placeholder, 0,
            'Customers with telp_1 set to a known placeholder value (0, -, N/A)',
            'info');

        // Check for suspiciously short phone numbers (less than 7 digits)
        $tooShort = DB::table('master_customers')
            ->whereNotNull('telp_1')
            ->where('telp_1', '!=', '')
            ->whereRaw("LENGTH(REGEXP_REPLACE(telp_1, '[^0-9]', '')) < 7")
            ->count();

        $this->addResult('Suspiciously Short Phone Numbers (<7 digits)', $tooShort, 0,
            'Customers with telp_1 having fewer than 7 numeric digits',
            'info');
    }

    private function checkQualityScoreDistribution(): void
    {
        $dist = DB::table('master_customers')
            ->select(DB::raw('
                SUM(CASE WHEN data_quality_score = 0  THEN 1 ELSE 0 END) as score_0,
                SUM(CASE WHEN data_quality_score <= 20 AND data_quality_score > 0  THEN 1 ELSE 0 END) as score_low,
                SUM(CASE WHEN data_quality_score > 20 AND data_quality_score <= 60 THEN 1 ELSE 0 END) as score_mid,
                SUM(CASE WHEN data_quality_score > 60 THEN 1 ELSE 0 END) as score_high,
                COUNT(*) as total
            '))
            ->first();

        $this->summary[] = ['Total Customers',               number_format($dist->total)];
        $this->summary[] = ['Quality Score = 0 (empty)',     number_format($dist->score_0)];
        $this->summary[] = ['Quality Score 1–20 (very low)', number_format($dist->score_low)];
        $this->summary[] = ['Quality Score 21–60 (medium)',  number_format($dist->score_mid)];
        $this->summary[] = ['Quality Score > 60 (good)',     number_format($dist->score_high)];
    }

    private function checkOrphanGhostVehicles(): void
    {
        // Ghost vehicles are those marked is_recovered with no service history link
        $orphanGhosts = DB::table('master_vehicles as mv')
            ->leftJoin('service_histories as sh', 'mv.id', '=', 'sh.vehicle_id')
            ->where('mv.is_recovered', true)
            ->whereNull('sh.vehicle_id')
            ->count();

        $this->addResult('Orphan Ghost Vehicles (no service history)', $orphanGhosts, 0,
            'Recovered ghost vehicles that have no service history linked — may be duplicates of LVS vehicles',
            'info');

        $totalVehicles = DB::table('master_vehicles')->count();
        $totalRecovered = DB::table('master_vehicles')->where('is_recovered', true)->count();
        $this->summary[] = ['Total Vehicles',               number_format($totalVehicles)];
        $this->summary[] = ['Ghost/Recovered Vehicles',     number_format($totalRecovered)];
        $this->summary[] = ['LVS Master Vehicles (normal)', number_format($totalVehicles - $totalRecovered)];
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function addResult(string $check, int $count, int $expectedMax, string $detail, string $severity = 'warning'): void
    {
        $this->summary[] = [$check, number_format($count)];

        if ($count > $expectedMax) {
            $this->issues[] = [
                'check' => $check,
                'count' => $count,
                'severity' => $severity,
                'detail' => $detail,
            ];
        }
    }

    private function displayResults(float $elapsed): void
    {
        // Issues table
        if (! empty($this->issues)) {
            $this->newLine();
            $this->warn('⚠️  Issues Found:');
            $rows = [];
            foreach ($this->issues as $issue) {
                $icon = match ($issue['severity']) {
                    'critical' => '🔴',
                    'warning' => '🟡',
                    default => '🔵',
                };
                $rows[] = [$icon.' '.$issue['severity'], $issue['check'], number_format($issue['count']), $issue['detail']];
            }
            $this->table(['Severity', 'Check', 'Count', 'Detail'], $rows);
        } else {
            $this->info('✅ No critical issues found!');
        }

        // Summary table
        $this->newLine();
        $this->info('📊 Data Summary:');
        $this->table(['Metric', 'Value'], $this->summary);

        $this->newLine();
        $issueCount = count($this->issues);
        $criticalCount = count(array_filter($this->issues, fn ($i) => $i['severity'] === 'critical'));
        $this->line("Validation completed in {$elapsed}s — {$issueCount} issue(s) found ({$criticalCount} critical).");
    }
}
