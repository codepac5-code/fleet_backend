<?php

namespace App\Http\Services\Setup\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Install\Installer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreateAdminController extends Controller
{
    public function __invoke(Request $request, Installer $installer): JsonResponse
    {
        $data = $request->validate([
            'firstName' => ['required', 'string', 'max:60'],
            'lastName'  => ['required', 'string', 'max:60'],
            'email'     => ['required', 'email', 'max:191'],
            'password'  => ['required', 'string', 'min:8', 'max:191'],
        ]);

        try {
            $installer->seedCore($data);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => textByLanguage('فشل إنشاء المدير والبيانات الأساسية', 'Failed to create admin and core data'),
                'error'   => $e->getMessage(),
            ], 200);
        }

        return response()->json([
            'ok'      => true,
            'message' => textByLanguage('تم إنشاء المدير والصلاحيات والعملات والخطط', 'Admin, roles, currencies and plans created'),
        ]);
    }
}
