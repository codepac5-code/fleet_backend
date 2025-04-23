<?php
namespace App\Http\Core\Const\Options;

use App\Models\Permission;

enum Permissions : string {
    
    //--- users
    case user_list              = 'user list';
    case add_user               = 'add user';
    case update_user            = 'update user';
    case delete_user            = 'delete user';
    case user_changePassword    = 'user changePassword';


    //--- drivers
    case driver_list            = 'driver list';
    case add_driver             = 'add driver';
    case update_driver          = 'update driver';
    case delete_driver          = 'delete driver';
    case driver_changePassword  = 'driver changePassword';


    //--- office
    case add_office     = 'add office';
    case update_office  = 'update office';
    case office_list    = 'office list';

    //--- coupon
    case add_coupon     = 'add coupon';
    case update_coupon  = 'update coupon';

    //--- service
    case add_service     = 'add service';
    case update_service  = 'update service';

    //--- sub service
    case add_sub_service     = 'add sub-service';
    case update_sub_service  = 'update sub-service';




}
