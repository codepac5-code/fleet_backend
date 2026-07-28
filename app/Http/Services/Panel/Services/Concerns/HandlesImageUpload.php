<?php

namespace App\Http\Services\Panel\Services\Concerns;

use Illuminate\Http\Request;

trait HandlesImageUpload
{
    protected function uploadedImage(Request $request, string $folder): ?string
    {
        if ($request->hasFile('image')) {
            return $request->file('image')->store($folder, 'public');
        }

        return null;
    }
}
