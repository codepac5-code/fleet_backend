<?php

namespace App\Http\Services\Panel\Admin\Permissions\Logic;

class PermissionLabels
{
    private const GROUPS = [
        'dashboard'   => ['ar' => 'لوحة التحكم', 'en' => 'Dashboard'],
        'office'      => ['ar' => 'المكاتب', 'en' => 'Offices'],
        'employee'    => ['ar' => 'الموظفون', 'en' => 'Employees'],
        'driver'      => ['ar' => 'السائقون', 'en' => 'Drivers'],
        'user'        => ['ar' => 'المستخدمون', 'en' => 'Users'],
        'vehicle'     => ['ar' => 'المركبات', 'en' => 'Vehicles'],
        'service'     => ['ar' => 'الخدمات', 'en' => 'Services'],
        'sub-service' => ['ar' => 'الخدمات الفرعية', 'en' => 'Sub-services'],
        'orders'      => ['ar' => 'الطلبات', 'en' => 'Orders'],
        'system'      => ['ar' => 'النظام والعمولات', 'en' => 'System & commissions'],
        'department'  => ['ar' => 'الأقسام', 'en' => 'Departments'],
        'banners'     => ['ar' => 'اللافتات', 'en' => 'Banners'],
        'issues'      => ['ar' => 'البلاغات', 'en' => 'Issues'],
        'other'       => ['ar' => 'أخرى', 'en' => 'Other'],
    ];

    private const PERMISSIONS = [
        'view dashboard'                   => ['ar' => 'عرض اللوحة', 'en' => 'View dashboard'],
        'order history'                    => ['ar' => 'سجلّ الطلبات', 'en' => 'Order history'],
        'follow orders'                    => ['ar' => 'متابعة الطلبات', 'en' => 'Follow orders'],
        'view roles'                       => ['ar' => 'عرض الأدوار', 'en' => 'View roles'],
        'monthly revenue'                  => ['ar' => 'الإيراد الشهري', 'en' => 'Monthly revenue'],
        'track drivers locations'          => ['ar' => 'تتبّع مواقع السائقين', 'en' => 'Track driver locations'],
        'view office list'                 => ['ar' => 'عرض قائمة المكاتب', 'en' => 'View office list'],
        'add office'                       => ['ar' => 'إضافة مكتب', 'en' => 'Add office'],
        'update office'                    => ['ar' => 'تعديل مكتب', 'en' => 'Update office'],
        'edit office'                      => ['ar' => 'تعديل مكتب', 'en' => 'Edit office'],
        'delete office'                    => ['ar' => 'حذف مكتب', 'en' => 'Delete office'],
        'office overview'                  => ['ar' => 'نظرة عامة على المكتب', 'en' => 'Office overview'],
        'office change custom commission'  => ['ar' => 'تعديل عمولة المكتب', 'en' => 'Change office commission'],
        'view employee list'               => ['ar' => 'عرض قائمة الموظفين', 'en' => 'View employee list'],
        'add employee'                     => ['ar' => 'إضافة موظف', 'en' => 'Add employee'],
        'update employee'                  => ['ar' => 'تعديل موظف', 'en' => 'Update employee'],
        'delete employee'                  => ['ar' => 'حذف موظف', 'en' => 'Delete employee'],
        'view driver list'                 => ['ar' => 'عرض قائمة السائقين', 'en' => 'View driver list'],
        'add driver'                       => ['ar' => 'إضافة سائق', 'en' => 'Add driver'],
        'edit driver'                      => ['ar' => 'تعديل سائق', 'en' => 'Edit driver'],
        'delete driver'                    => ['ar' => 'حذف سائق', 'en' => 'Delete driver'],
        'assign permissions'               => ['ar' => 'إسناد الصلاحيات', 'en' => 'Assign permissions'],
        'view drivers new style'           => ['ar' => 'عرض السائقين (نمط جديد)', 'en' => 'Drivers (new style)'],
        'driver change custom commission'  => ['ar' => 'تعديل عمولة السائق', 'en' => 'Change driver commission'],
        'view user list'                   => ['ar' => 'عرض قائمة المستخدمين', 'en' => 'View user list'],
        'add user'                         => ['ar' => 'إضافة مستخدم', 'en' => 'Add user'],
        'edit user'                        => ['ar' => 'تعديل مستخدم', 'en' => 'Edit user'],
        'delete user'                      => ['ar' => 'حذف مستخدم', 'en' => 'Delete user'],
        'view vehicle list'                => ['ar' => 'عرض قائمة المركبات', 'en' => 'View vehicle list'],
        'add vehicle'                      => ['ar' => 'إضافة مركبة', 'en' => 'Add vehicle'],
        'update vehicle'                   => ['ar' => 'تعديل مركبة', 'en' => 'Update vehicle'],
        'delete vehicle'                   => ['ar' => 'حذف مركبة', 'en' => 'Delete vehicle'],
        'view service list'                => ['ar' => 'عرض الخدمات', 'en' => 'View service list'],
        'add service'                      => ['ar' => 'إضافة خدمة', 'en' => 'Add service'],
        'edit service'                     => ['ar' => 'تعديل خدمة', 'en' => 'Edit service'],
        'delete service'                   => ['ar' => 'حذف خدمة', 'en' => 'Delete service'],
        'view sub-service list'            => ['ar' => 'عرض الخدمات الفرعية', 'en' => 'View sub-service list'],
        'add sub-service'                  => ['ar' => 'إضافة خدمة فرعية', 'en' => 'Add sub-service'],
        'edit sub-service'                 => ['ar' => 'تعديل خدمة فرعية', 'en' => 'Edit sub-service'],
        'delete sub-service'               => ['ar' => 'حذف خدمة فرعية', 'en' => 'Delete sub-service'],
        'show order details'               => ['ar' => 'عرض تفاصيل الطلب', 'en' => 'Show order details'],
        'edit order status'                => ['ar' => 'تعديل حالة الطلب', 'en' => 'Edit order status'],
        'view commission'                  => ['ar' => 'عرض العمولات', 'en' => 'View commission'],
        'edit commission'                  => ['ar' => 'تعديل العمولات', 'en' => 'Edit commission'],
        'add department'                   => ['ar' => 'إضافة قسم', 'en' => 'Add department'],
        'update department'                => ['ar' => 'تعديل قسم', 'en' => 'Update department'],
        'delete department'                => ['ar' => 'حذف قسم', 'en' => 'Delete department'],
        'view department list'             => ['ar' => 'عرض الأقسام', 'en' => 'View department list'],
        'view banner list'                 => ['ar' => 'عرض اللافتات', 'en' => 'View banner list'],
        'issues add'                       => ['ar' => 'إضافة بلاغ', 'en' => 'Add issue'],
    ];

    public static function group(string $name): string
    {
        $entry = self::GROUPS[$name] ?? null;

        return $entry ? textByLanguage($entry['ar'], $entry['en']) : ucwords(str_replace('-', ' ', $name));
    }

    public static function permission(string $name): string
    {
        $entry = self::PERMISSIONS[$name] ?? null;

        return $entry ? textByLanguage($entry['ar'], $entry['en']) : ucwords($name);
    }
}
