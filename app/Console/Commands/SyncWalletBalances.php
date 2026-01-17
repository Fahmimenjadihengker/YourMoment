<?php

namespace App\Console\Commands;

use App\Services\FinancialSummaryService;
use Illuminate\Console\Command;

class SyncWalletBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:sync {--user= : Sync specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync wallet balances with actual transaction totals';

    /**
     * Execute the console command.
     */
    public function handle(FinancialSummaryService $financialService): int
    {
        $userId = $this->option('user');

        if ($userId) {
            $newBalance = $financialService->syncWalletBalance((int) $userId);
            $this->info("User {$userId} balance synced to: Rp " . number_format($newBalance, 0, ',', '.'));
        } else {
            $count = $financialService->syncAllWalletBalances();
            $this->info("Synced {$count} user wallet balances.");
        }

        return Command::SUCCESS;
    }
}
