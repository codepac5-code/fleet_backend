<?php

namespace App\Traits;

use App\Models\Scopes\OfficeScope;
use Illuminate\Support\Facades\Auth;

trait BelongsToOffice
{
    use ResolvesTenantConnection;

    protected static function bootBelongsToOffice(): void
    {
        static::addGlobalScope(new OfficeScope());

        static::creating(function ($model) {

            if (Auth::guard('office')->check()) {

                $model->officeId = Auth::guard('office')->id();
            }

            elseif (Auth::guard('employee')->check()) {

                $employee = Auth::guard('employee')->user();

                if ($employee->officeId) {
                    $model->officeId = $employee->officeId;
                }
            }
        });
    }
}
