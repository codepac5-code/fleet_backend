<?php
namespace App\Http\Core\Const\Options;


enum  Roles : string {
    case Super_Admin = 'super-admin';
    case Office      = 'office';
    case User        = 'user';
}