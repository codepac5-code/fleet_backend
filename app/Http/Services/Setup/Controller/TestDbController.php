<?php

namespace App\Http\Services\Setup\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Install\Installer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestDbController extends Controller
{
    public function __invoke(Request $request, Installer $installer): JsonResponse
    {
        $creds = $this->creds($request);

        if (! $creds['db_host'] || ! $creds['db_name'] || ! $creds['db_user']) {
            return response()->json(['ok' => false, 'message' => textByLanguage('أكمل بيانات الاتصال', 'Fill in the connection fields')], 422);
        }

        $result = $installer->testConnection($creds);

        return response()->json([
            'ok'      => $result['ok'],
            'message' => $result['ok']
                ? textByLanguage('تم الاتصال بنجاح', 'Connection successful')
                : textByLanguage('تعذّر الاتصال بالقاعدة (قد تكون غير منشأة بعد)', 'Could not connect (database may not exist yet)'),
            'server'  => $result['server'] ?? null,
            'error'   => $result['error'] ?? null,
        ]);
    }

    private function creds(Request $request): array
    {
        return [
            'db_host' => trim((string) $request->input('db_host')),
            'db_port' => (int) ($request->input('db_port') ?: 3306),
            'db_name' => trim((string) $request->input('db_name')),
            'db_user' => trim((string) $request->input('db_user')),
            'db_pass' => (string) $request->input('db_pass'),
        ];
    }
}
