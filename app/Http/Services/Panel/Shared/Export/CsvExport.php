<?php

namespace App\Http\Services\Panel\Shared\Export;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a CSV download. Callers pass the header row and a lazy row generator;
 * the data itself is always fetched by the calling controller on the ACTIVE
 * country shard, so an export never mixes countries.
 */
class CsvExport
{
    public static function stream(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so Excel renders Arabic correctly.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, $headers);

            foreach ($rows as $row) {
                fputcsv($out, array_map(fn ($v) => is_scalar($v) || $v === null ? $v : json_encode($v), $row));
            }

            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
