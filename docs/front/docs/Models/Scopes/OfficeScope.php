<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class OfficeScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::guard('admin')->check()) {
            return;
        }

        if (Auth::guard('office')->check()) {

            $office = Auth::guard('office')->user();

            $builder->where(
                $model->getTable() . '.officeId',
                $office->id
            );

            return;
        }

        if (Auth::guard('employee')->check()) {

            $employee = Auth::guard('employee')->user();

            if ($employee->officeId) {

                $builder->where(
                    $model->getTable() . '.officeId',
                    $employee->officeId
                );
            }
        }
    }
}
