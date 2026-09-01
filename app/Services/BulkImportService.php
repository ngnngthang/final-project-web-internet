<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;

class BulkImportService
{
    /**
     * Parses an uploaded CSV (path given), validates rows, and — if
     * $dryRun is false — inserts valid rows in a single transaction via
     * Student::bulkImport(). Returns a report: ['valid' => n, 'errors' => [...]].
     *
     * TODO: full validation per classhub_detailed_specifications.md Module 2
     * (duplicate student_id checks, age-range warnings, generated credentials
     * export). This is a minimal working version.
     */
    public function importFromCsv(string $csvPath, int $schoolId, bool $dryRun = true): array
    {
        $rows = [];
        $errors = [];

        if (($handle = fopen($csvPath, 'r')) === false) {
            throw new \RuntimeException("Could not open CSV: {$csvPath}");
        }

        $header = fgetcsv($handle);
        $lineNo = 1;
        while (($line = fgetcsv($handle)) !== false) {
            $lineNo++;
            $row = array_combine($header, $line);
            if (empty($row['full_name']) || empty($row['student_id'])) {
                $errors[] = "Line {$lineNo}: missing full_name or student_id";
                continue;
            }
            $row['school_id'] = $schoolId;
            $rows[] = $row;
        }
        fclose($handle);

        if ($dryRun) {
            return ['valid' => count($rows), 'errors' => $errors, 'dry_run' => true];
        }

        $result = Student::bulkImport($rows);
        return array_merge($result, ['errors' => array_merge($errors, $result['errors'])]);
    }
}
