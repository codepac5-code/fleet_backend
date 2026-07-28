<?php

namespace App\Http\Services\Setup\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Install\Installer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaveDatabaseController extends Controller
{
    public function __invoke(Request $request, Installer $installer): JsonResponse
    {
        $data = $request->validate([
            'db_host' => ['required', 'string', 'max:191'],
            'db_port' => ['required', 'integer', 'between:1,65535'],
            'db_name' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_]+$/'],
            'db_user' => ['required', 'string', 'max:191'],
            'db_pass' => ['nullable', 'string', 'max:191'],
        ]);

        $creds = [
            'db_host' => $data['db_host'],
            'db_port' => (int) $data['db_port'],
            'db_name' => $data['db_name'],
            'db_user' => $data['db_user'],
            'db_pass' => $data['db_pass'] ?? '',
        ];

        $create = $installer->tryCreateDatabase($creds);
        $test   = $installer->testConnection($creds);

        if (! $test['ok']) {
            return response()->json([
                'ok'      => false,
                'message' => textByLanguage('تعذّر الاتصال بقاعدة البيانات', 'Could not connect to the database'),
                'error'   => $test['error'] ?? ($create['error'] ?? null),
            ], 200);
        }

        $installer->writeEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST'       => $creds['db_host'],
            'DB_PORT'       => (string) $creds['db_port'],
            'DB_DATABASE'   => $creds['db_name'],
            'DB_USERNAME'   => $creds['db_user'],
            'DB_PASSWORD'   => $creds['db_pass'],
        ]);

        $installer->configureGlobalRuntime($creds);

        try {
            $installer->migrateGlobal();
        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => textByLanguage('فشلت تهيئة الجداول', 'Migration failed'),
                'error'   => $e->getMessage(),
            ], 200);
        }

        return response()->json([
            'ok'      => true,
            'message' => textByLanguage('تم إعداد قاعدة البيانات وتهيئة الجداول', 'Database configured and tables created'),
            'created' => $create['created'] ?? false,
            'server'  => $test['server'] ?? null,
        ]);
    }
}
