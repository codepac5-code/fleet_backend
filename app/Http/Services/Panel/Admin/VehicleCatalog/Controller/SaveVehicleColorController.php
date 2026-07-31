<?php

namespace App\Http\Services\Panel\Admin\VehicleCatalog\Controller;

use App\Http\Controllers\Controller;
use App\Models\VehicleColor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaveVehicleColorController extends Controller
{
    public function __invoke(Request $request, ?int $color = null): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'name_en' => ['required', 'string', 'max:120'],
            'hex' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ], [
            'hex.regex' => textByLanguage('اللون يجب أن يكون بصيغة #RRGGBB', 'Colour must look like #RRGGBB'),
        ]);

        $record = $color !== null ? VehicleColor::query()->findOrFail($color) : new VehicleColor();

        $record->name = $data['name'];
        $record->name_en = $data['name_en'];
        $record->hex = $data['hex'] ?? null;

        if ($color === null) {
            $record->status = true;
        }

        $record->save();

        return back()->with('status', $color !== null
            ? textByLanguage('تم تحديث اللون', 'Colour updated')
            : textByLanguage('تمت إضافة اللون', 'Colour added'));
    }
}
