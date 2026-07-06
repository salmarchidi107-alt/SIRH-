<?php


namespace App\Console\Commands;

use App\Models\VerificationCode;
use Illuminate\Console\Command;

class ExpireVerificationCodes extends Command
{
    protected $signature = 'verification-codes:expire';
    protected $description = "Expire automatiquement les codes ASSIGNED dont le trimestre est terminé.";

    public function handle(): int
    {
        $currentQuarter = VerificationCode::currentQuarterLabel();

        $toExpire = VerificationCode::where('status', VerificationCode::STATUS_ASSIGNED)
            ->where('quarter', '!=', $currentQuarter)
            ->get();

        $count = 0;
        foreach ($toExpire as $code) {
            $code->expire();
            $count++;
        }

        $this->info("{$count} code(s) expiré(s) automatiquement pour le trimestre {$currentQuarter}.");

        return self::SUCCESS;
    }
}
