<?php

namespace App\Services\PinCodes;

use App\Models\PinCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final class PinCodeCsvImporter
{
    /**
     * Expected header keys (case-insensitive, spaces or underscores accepted).
     *
     * @var list<string>
     */
    private const REQUIRED_HEADERS = ['pincode', 'area_name', 'city'];

    /**
     * @return array{created: int, skipped: int, failed: int, errors: list<string>}
     */
    public function import(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        if ($path === false) {
            return ['created' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => [__('Could not read the uploaded file.')]];
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return ['created' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => [__('Could not open the CSV file.')]];
        }

        $first = fgets($handle);
        if ($first === false) {
            fclose($handle);

            return ['created' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => [__('The CSV file is empty.')]];
        }

        if (str_starts_with($first, "\xEF\xBB\xBF")) {
            $first = substr($first, 3);
        }

        $headerLine = $first;
        $headers = str_getcsv($headerLine);
        $map = $this->buildHeaderMap($headers);

        foreach (self::REQUIRED_HEADERS as $required) {
            if (! isset($map[$required])) {
                fclose($handle);

                return [
                    'created' => 0,
                    'skipped' => 0,
                    'failed' => 0,
                    'errors' => [__('Missing required column: :col.', ['col' => $required])],
                ];
            }
        }

        $created = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];

        $lineNo = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $lineNo++;
                if ($this->rowIsEmpty($row)) {
                    continue;
                }

                $data = $this->normalizeRow($row, $map);
                if ($data === null) {
                    $failed++;
                    $errors[] = __('Line :line: invalid pincode or missing required fields.', ['line' => $lineNo]);

                    continue;
                }

                if (PinCode::query()->where('pincode', $data['pincode'])->exists()) {
                    $skipped++;

                    continue;
                }

                PinCode::query()->create($data);
                $created++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);

            return [
                'created' => 0,
                'skipped' => 0,
                'failed' => 0,
                'errors' => [__('Import failed: :msg', ['msg' => $e->getMessage()])],
            ];
        }

        fclose($handle);

        return [
            'created' => $created,
            'skipped' => $skipped,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * @param  list<string|null>|false  $headers
     * @return array<string, int>
     */
    private function buildHeaderMap(array|false $headers): array
    {
        if ($headers === false) {
            return [];
        }

        $map = [];
        foreach ($headers as $i => $h) {
            $key = $this->normalizeHeaderKey((string) $h);
            if ($key !== '') {
                $map[$key] = (int) $i;
            }
        }

        return $map;
    }

    private function normalizeHeaderKey(string $header): string
    {
        $h = strtolower(trim($header));
        $h = str_replace([' ', '-'], '_', $h);

        return match ($h) {
            'pin', 'pincode', 'postal_code', 'postalcode', 'zip' => 'pincode',
            'area', 'area_name', 'areaname' => 'area_name',
            'locality', 'neighbourhood', 'neighborhood' => 'locality',
            'city', 'town' => 'city',
            'serviceable', 'serviceability', 'is_serviceable' => 'serviceability',
            'delivery_charge', 'charge', 'fee' => 'delivery_charge',
            'meta_title', 'metatitle', 'title' => 'meta_title',
            'meta_description', 'metadescription', 'description' => 'meta_description',
            'seo_keywords', 'keywords', 'seo' => 'seo_keywords',
            default => $h,
        };
    }

    /**
     * @param  list<string|null>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string|null>  $row
     * @param  array<string, int>  $map
     * @return array<string, mixed>|null
     */
    private function normalizeRow(array $row, array $map): ?array
    {
        $pin = $this->cell($row, $map['pincode'] ?? -1);
        $pin = preg_replace('/\D/', '', $pin) ?? '';
        if (strlen($pin) < 6 || strlen($pin) > 12) {
            return null;
        }

        $area = trim($this->cell($row, $map['area_name'] ?? -1));
        $city = trim($this->cell($row, $map['city'] ?? -1));
        if ($area === '' || $city === '') {
            return null;
        }

        $locality = isset($map['locality']) ? trim($this->cell($row, $map['locality'])) : null;
        if ($locality === '') {
            $locality = null;
        }

        $serviceable = true;
        if (isset($map['serviceability'])) {
            $serviceable = $this->parseBool($this->cell($row, $map['serviceability']));
        }

        $charge = null;
        if (isset($map['delivery_charge'])) {
            $raw = trim($this->cell($row, $map['delivery_charge']));
            if ($raw !== '') {
                $charge = is_numeric($raw) ? $raw : null;
            }
        }

        $metaTitle = isset($map['meta_title']) ? trim($this->cell($row, $map['meta_title'])) : null;
        $metaDesc = isset($map['meta_description']) ? trim($this->cell($row, $map['meta_description'])) : null;
        $seoKw = isset($map['seo_keywords']) ? trim($this->cell($row, $map['seo_keywords'])) : null;

        return [
            'pincode' => $pin,
            'area_name' => $area,
            'city' => $city,
            'locality' => $locality,
            'is_serviceable' => $serviceable,
            'is_active' => true,
            'delivery_charge' => $charge,
            'meta_title' => $metaTitle !== '' ? $metaTitle : null,
            'meta_description' => $metaDesc !== '' ? $metaDesc : null,
            'seo_keywords' => $seoKw !== '' ? $seoKw : null,
            'geo_page_ready' => false,
            'slug' => null,
        ];
    }

    /**
     * @param  list<string|null>  $row
     */
    private function cell(array $row, int $index): string
    {
        return (string) ($row[$index] ?? '');
    }

    private function parseBool(string $value): bool
    {
        $v = strtolower(trim($value));

        return in_array($v, ['1', 'true', 'yes', 'y', 'serviceable', 'on'], true);
    }
}
