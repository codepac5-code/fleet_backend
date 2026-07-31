<?php

namespace App\Http\Services\Panel\Admin\Countries\Controller;

use App\Http\Controllers\Controller;
use App\Models\InfrastructureNode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestConnectionController extends Controller
{
    private const PROBE = 'country_probe';

    public function __invoke(Request $request): JsonResponse
    {
        $host = trim((string) $request->input('db_host'));
        $port = (int) ($request->input('db_port') ?: 3306);
        $name = trim((string) $request->input('db_name'));
        $user = trim((string) $request->input('db_user'));
        $pass = (string) $request->input('db_pass');

        if ($pass === '' && $request->filled('node')) {
            $existing = InfrastructureNode::query()->whereKey($request->input('node'))->first();
            $pass = $existing->db_pass ?? '';
        }

        if ($host === '' || $name === '' || $user === '') {
            return response()->json([
                'ok'      => false,
                'message' => textByLanguage('أدخل المضيف واسم القاعدة والمستخدم أولاً', 'Enter host, database name and user first'),
            ], 422);
        }

        // Probe at the SERVER level (no database selected). A brand-new country's
        // database usually does not exist yet — it is created at provisioning time
        // — so selecting it here would fail with "Unknown database" and read as a
        // connection failure. We connect to the server, then just CHECK whether the
        // database already exists.
        config(['database.connections.' . self::PROBE => [
            'driver'   => 'mysql',
            'host'     => $host,
            'port'     => $port,
            'database' => '',
            'username' => $user,
            'password' => $pass,
            'charset'  => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options'  => [\PDO::ATTR_TIMEOUT => 5],
        ]]);

        DB::purge(self::PROBE);

        try {
            $conn    = DB::connection(self::PROBE);
            $version = $conn->select('select version() as v')[0]->v ?? null;
            $exists  = $conn->select('select schema_name from information_schema.schemata where schema_name = ?', [$name]) !== [];
            $tables  = $exists
                ? ($conn->select('select count(*) as c from information_schema.tables where table_schema = ?', [$name])[0]->c ?? 0)
                : 0;

            DB::purge(self::PROBE);

            return response()->json([
                'ok'      => true,
                'message' => $exists
                    ? textByLanguage('تم الاتصال بنجاح', 'Connection successful')
                    : textByLanguage('الخادم متصل — سيتم إنشاء القاعدة تلقائياً عند التجهيز', 'Server reachable — the database will be created automatically on provisioning'),
                'data'    => [
                    'server'          => $version,
                    'tables'          => (int) $tables,
                    'database_exists' => $exists,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::purge(self::PROBE);

            return response()->json([
                'ok'      => false,
                'message' => textByLanguage('فشل الاتصال', 'Connection failed'),
                'error'   => $e->getMessage(),
            ], 200);
        }
    }
}
