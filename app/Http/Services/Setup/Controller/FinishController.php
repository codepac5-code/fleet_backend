<?php

namespace App\Http\Services\Setup\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Install\Installer;
use Illuminate\Http\JsonResponse;

class FinishController extends Controller
{
    public function __invoke(Installer $installer): JsonResponse
    {
        $installer->lock();

        return response()->json([
            'ok'       => true,
            'message'  => textByLanguage('اكتمل التثبيت', 'Installation complete'),
            'redirect' => url('/panel/login'),
        ]);
    }
}
