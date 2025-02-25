<?php

namespace App\Services;

use League\Csv\Writer;
use SplTempFileObject;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
{
    public static function exportToCsv(Collection $collection, $filename = 'export.csv'): StreamedResponse
    {
        // Create CSV writer instance
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // Set CSV encoding for better compatibility
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->setDelimiter(',');

        // Add header row
        if ($collection->isNotEmpty()) {
            // Check if the first item is an array or an object
            $firstItem = $collection->first();
            $headers = is_array($firstItem) ? array_keys($firstItem) : array_keys($firstItem->toArray());
            $csv->insertOne($headers);
        }

        // Add data rows
        foreach ($collection as $item) {
            // Convert item to array if it's an object
            $row = is_array($item) ? $item : $item->toArray();
            $csv->insertOne($row);
        }

        // Return a StreamedResponse to force download
        return new StreamedResponse(function () use ($csv) {
            echo $csv->toString();
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
