<?php
// Path: app/Services/FineStrategy/DailyRateFine.php
namespace App\Services\FineStrategy;

class DailyRateFine implements FineStrategyInterface
{
    /**
     * Assume the fine is, for example, 1.50 PLN for each overdue day.
     */
    protected $ratePerDay = 1.50;

    public function calculate(int $daysOverdue): float
    {
        if ($daysOverdue <= 0) {
            return 0.00;
        }
        return round($daysOverdue * $this->ratePerDay, 2);
    }
}
