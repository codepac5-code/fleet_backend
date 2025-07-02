<?php
namespace App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet;

abstract class  BalanceStatus  {


    public static $Withdrawn = 'withdrawn';
    public static $Pending   = 'pending';
    public static $Total     = 'total';
    public static $Available = 'available';
//-----------------
    public static $DriversDue   = 'drivers-due';
    public static $OfficesDue   = 'offices-due';

    // public static $driver_fleet_due = 'driver-fleet-due-total';
    // public static $driver_model_due = 'driver-office-due';

    
    
}