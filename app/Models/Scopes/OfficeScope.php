<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class OfficeScope implements Scope
{
    /**
     * Re-entrancy guard. `apply()` reads the authenticated user, but the
     * office/employee models THEMSELVES carry this scope — so resolving the
     * logged-in user from the session (a query on `employees`/`offices`) applies
     * this scope, which calls `Auth::guard()->user()` again WHILE that very user
     * is still being resolved → infinite recursion → memory exhaustion (seen in
     * SoftDeletingScope). While inside a guard-resolution query we skip scoping;
     * fetching the user by id needs no office filter anyway.
     */
    private static bool $resolving = false;

    public function apply(Builder $builder, Model $model): void
    {
        if (self::$resolving) {
            return;
        }

        self::$resolving = true;

        try {
            if (Auth::guard('admin')->check()) {
                return;
            }

            if (Auth::guard('office')->check()) {

                $office = Auth::guard('office')->user();

                if ($office) {
                    $builder->where(
                        $model->getTable() . '.officeId',
                        $office->id
                    );
                }

                return;
            }

            if (Auth::guard('employee')->check()) {

                $employee = Auth::guard('employee')->user();

                if ($employee && $employee->officeId) {

                    $builder->where(
                        $model->getTable() . '.officeId',
                        $employee->officeId
                    );
                }
            }
        } finally {
            self::$resolving = false;
        }
    }
}
