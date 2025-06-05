<?php
// Path: app/Factories/FineStrategyFactory.php
namespace App\Factories;

require_once __DIR__ . '/../Services/FineStrategy/FineStrategyInterface.php';
require_once __DIR__ . '/../Services/FineStrategy/DailyRateFine.php';
require_once __DIR__ . '/../Services/FineStrategy/FixedRateFine.php';

use App\Services\FineStrategy\DailyRateFine;
use App\Services\FineStrategy\FixedRateFine;
use App\Services\FineStrategy\FineStrategyInterface;

class FineStrategyFactory
{
    /**
     * Creates a strategy object using the key 'daily' or 'fixed'.
     * @param string $type
     * @return FineStrategyInterface
     * @throws \Exception
     */
    public static function make(string $type): FineStrategyInterface
    {
        switch (strtolower($type)) {
            case 'daily':
                return new DailyRateFine();
            case 'fixed':
                return new FixedRateFine();
            default:
                throw new \Exception("Unknown fine strategy type: $type");
        }
    }
}
