<?php
namespace App\Http\Core\Const\Options;


enum  Roles : string {
    case Super_Admin = 'super-admin';
    case Office      = 'office manager';
    case User        = 'user';
    case Employee = 'employee';
}