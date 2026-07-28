<?php

namespace App\Http\Services\Panel\Shared\Authorization;

class PanelPermission
{
    public const VIEW_DASHBOARD = 'view dashboard';

    public const VIEW_OFFICE_LIST   = 'view office list';
    public const ADD_OFFICE         = 'add office';
    public const UPDATE_OFFICE      = 'update office';
    public const DELETE_OFFICE      = 'delete office';
    public const VIEW_USER_LIST     = 'view user list';
    public const ADD_USER           = 'add user';
    public const EDIT_USER          = 'edit user';
    public const DELETE_USER        = 'delete user';
    public const VIEW_DRIVER_LIST   = 'view driver list';
    public const ADD_DRIVER         = 'add driver';
    public const EDIT_DRIVER        = 'edit driver';
    public const DELETE_DRIVER      = 'delete driver';
    public const VIEW_EMPLOYEE_LIST = 'view employee list';
    public const ADD_EMPLOYEE       = 'add employee';
    public const UPDATE_EMPLOYEE    = 'update employee';
    public const DELETE_EMPLOYEE    = 'delete employee';
    public const ASSIGN_PERMISSIONS = 'assign permissions';

    public const VIEW_SERVICE_LIST     = 'view service list';
    public const ADD_SERVICE           = 'add service';
    public const EDIT_SERVICE          = 'edit service';
    public const DELETE_SERVICE        = 'delete service';
    public const VIEW_SUB_SERVICE_LIST = 'view sub-service list';
    public const ADD_SUB_SERVICE       = 'add sub-service';
    public const EDIT_SUB_SERVICE      = 'edit sub-service';
    public const DELETE_SUB_SERVICE    = 'delete sub-service';

    public const VIEW_BOOKING_LIST = 'booking list';
    public const ORDER_HISTORY     = 'order history';
    public const EDIT_ORDER_STATUS = 'edit order status';
    public const SHOW_ORDER_DETAILS = 'show order details';
    public const VIEW_VEHICLE_LIST = 'view vehicle list';
    public const ADD_VEHICLE       = 'add vehicle';
    public const UPDATE_VEHICLE    = 'update vehicle';
    public const DELETE_VEHICLE    = 'delete vehicle';

    public const VIEW_COMMISSION          = 'view commission';
    public const EDIT_COMMISSION          = 'edit commission';
    public const VIEW_PAYMENTS            = 'view payments';
    public const VIEW_WALLET_TRANSACTIONS = 'view wallet transactions';

    public const VIEW_COUPON_LIST    = 'view coupon list';
    public const VIEW_BANNER_LIST    = 'view banner list';
    public const VIEW_USER_RATINGS   = 'userrating list';
    public const VIEW_DRIVER_RATINGS = 'handymanrating list';

    public const VIEW_TICKETS     = 'view tickets';
    public const VIEW_DEPARTMENTS = 'view department list';

    public const MANAGE_ROLES_PERMISSIONS = 'manage roles and permission';
    public const MANAGE_CURRENCIES        = 'manage currencies';
    public const VIEW_SETTINGS            = 'view settings';
}
