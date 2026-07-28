<?php

namespace App\Http\Core\Classes\Billing;

use App\Http\Core\Const\Billing\BillingMode;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\InfrastructureNode;
use Throwable;

class RegionBilling
{
    public static function mode(?InfrastructureNode $node = null): string
    {
        try {
            $node = $node ?? ShardManager::current();

            if ($node === null) {
                return BillingMode::COMMISSION;
            }

            $mode = (string) ($node->billing_mode ?? BillingMode::COMMISSION);

            return BillingMode::isValid($mode) ? $mode : BillingMode::COMMISSION;
        } catch (Throwable $e) {
            return BillingMode::COMMISSION;
        }
    }

    public static function isSubscription(?InfrastructureNode $node = null): bool
    {
        return self::mode($node) === BillingMode::SUBSCRIPTION;
    }

    public static function isCommission(?InfrastructureNode $node = null): bool
    {
        return self::mode($node) === BillingMode::COMMISSION;
    }
}
