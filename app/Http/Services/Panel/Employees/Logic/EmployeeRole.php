<?php

namespace App\Http\Services\Panel\Employees\Logic;

use App\Http\Services\Panel\Shared\Authorization\PanelPermission as Perm;

/**
 * What the three employee roles actually MEAN.
 *
 * `employees.role` used to be a label with no authority behind it: every new
 * employee was created with zero permissions and could see nothing until an
 * admin hand-ticked the matrix. Each role now carries a preset, applied when the
 * employee is created and re-applied when their role changes; the per-employee
 * matrix still overrides it afterwards, so a preset is a starting point, never a
 * cage.
 *
 * VIEWER — read-only. Everything that answers "what is happening", nothing that
 *          changes it.
 * AGENT  — the day-to-day dispatcher/support seat: run orders, drivers, riders
 *          and vehicles. No money terms, no staff management, no settings.
 * ADMIN  — the office's own administrator: everything the office guard has,
 *          including staff and their permissions.
 */
class EmployeeRole
{
    public const VIEWER = 'viewer';
    public const AGENT = 'agent';
    public const ADMIN = 'admin';

    public const ALL = [self::AGENT, self::ADMIN, self::VIEWER];

    /** Read-only access: the base every role builds on. */
    private const VIEWER_PERMISSIONS = [
        Perm::VIEW_DASHBOARD,
        Perm::VIEW_BOOKING_LIST,
        Perm::ORDER_HISTORY,
        Perm::SHOW_ORDER_DETAILS,
        Perm::VIEW_DRIVER_LIST,
        Perm::VIEW_USER_LIST,
        Perm::VIEW_VEHICLE_LIST,
        Perm::VIEW_EMPLOYEE_LIST,
        Perm::VIEW_SERVICE_LIST,
        Perm::VIEW_SUB_SERVICE_LIST,
        Perm::VIEW_USER_RATINGS,
        Perm::VIEW_DRIVER_RATINGS,
        Perm::VIEW_TICKETS,
    ];

    /** What an agent may CHANGE, on top of everything a viewer can see. */
    private const AGENT_EXTRA = [
        Perm::EDIT_ORDER_STATUS,
        Perm::ADD_DRIVER,
        Perm::EDIT_DRIVER,
        Perm::ADD_USER,
        Perm::EDIT_USER,
        Perm::ADD_VEHICLE,
        Perm::UPDATE_VEHICLE,
        Perm::VIEW_DEPARTMENTS,
    ];

    /** What an office admin adds on top of an agent. */
    private const ADMIN_EXTRA = [
        Perm::DELETE_DRIVER,
        Perm::DELETE_USER,
        Perm::DELETE_VEHICLE,
        Perm::ADD_EMPLOYEE,
        Perm::UPDATE_EMPLOYEE,
        Perm::DELETE_EMPLOYEE,
        Perm::ASSIGN_PERMISSIONS,
        Perm::ADD_SERVICE,
        Perm::EDIT_SERVICE,
        Perm::ADD_SUB_SERVICE,
        Perm::EDIT_SUB_SERVICE,
        Perm::VIEW_COMMISSION,
        Perm::EDIT_COMMISSION,
        Perm::VIEW_PAYMENTS,
        Perm::VIEW_WALLET_TRANSACTIONS,
        Perm::VIEW_COUPON_LIST,
    ];

    public static function isValid(?string $role): bool
    {
        return in_array($role, self::ALL, true);
    }

    /** The permission names a role starts with. */
    public static function permissions(?string $role): array
    {
        return match ($role) {
            self::ADMIN => array_values(array_unique(array_merge(self::VIEWER_PERMISSIONS, self::AGENT_EXTRA, self::ADMIN_EXTRA))),
            self::AGENT => array_values(array_unique(array_merge(self::VIEWER_PERMISSIONS, self::AGENT_EXTRA))),
            self::VIEWER => self::VIEWER_PERMISSIONS,
            default => [],
        };
    }

    public static function label(?string $role): string
    {
        return match ($role) {
            self::ADMIN => textByLanguage('مسؤول', 'Admin'),
            self::AGENT => textByLanguage('وكيل', 'Agent'),
            self::VIEWER => textByLanguage('مشاهد', 'Viewer'),
            default => (string) $role,
        };
    }

    public static function description(?string $role): string
    {
        return match ($role) {
            self::ADMIN => textByLanguage(
                'يدير المكتب بالكامل: الموظفون وصلاحياتهم، العمولات والمدفوعات، الخدمات.',
                'Runs the whole office: staff and their permissions, commissions and payments, services.'
            ),
            self::AGENT => textByLanguage(
                'التشغيل اليومي: الطلبات والسائقون والركّاب والمركبات — بلا شؤون مالية أو إدارة موظفين.',
                'Day-to-day operations: orders, drivers, riders and vehicles — no money terms, no staff management.'
            ),
            self::VIEWER => textByLanguage(
                'اطّلاع فقط: يرى كل شيء ولا يغيّر شيئاً.',
                'Read-only: sees everything, changes nothing.'
            ),
            default => '',
        };
    }

    /** For the form's select: value => label. */
    public static function options(): array
    {
        return [
            self::AGENT => self::label(self::AGENT),
            self::ADMIN => self::label(self::ADMIN),
            self::VIEWER => self::label(self::VIEWER),
        ];
    }
}
