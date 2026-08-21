<?php

namespace Foorintodev\FormImport\Support;

/**
 * Lecture d'un CSV tolérante : détection du séparateur (`,`, `;`, tabulation),
 * suppression du BOM UTF-8, conversion en UTF-8 (repli Windows-1252 pour les
 * accents), gestion des champs entre guillemets (via fgetcsv).
 */
class Csv
{
    /**
     * @return array{headers: string[], rows: array<int, array<string, string>>, delimiter: string}
     */
    public static function read(string $path): array
    {
        $delimiter = self::detectDelimiter($path);

        $handle = fopen($path, 'r');
        $headers = null;
        $rows = [];

        while (($fields = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            // Ligne vide (fgetcsv renvoie [null]).
            if ($fields === [null]) {
                continue;
            }

            $fields = array_map([self::class, 'toUtf8'], $fields);

            if ($headers === null) {
                // Retire un éventuel BOM UTF-8 en tête de la 1re colonne.
                $fields[0] = preg_replace('/^\xEF\xBB\xBF/', '', $fields[0]);
                $headers = array_map('trim', $fields);

                continue;
            }

            $row = [];
            foreach ($headers as $j => $header) {
                $row[$header] = isset($fields[$j]) ? trim((string) $fields[$j]) : '';
            }
            $rows[] = $row;
        }

        fclose($handle);

        return [
            'headers' => $headers ?? [],
            'rows' => $rows,
            'delimiter' => $delimiter,
        ];
    }

    private static function detectDelimiter(string $path): string
    {
        $handle = fopen($path, 'r');
        $line = fgets($handle) ?: '';
        fclose($handle);

        $counts = [
            ';' => substr_count($line, ';'),
            ',' => substr_count($line, ','),
            "\t" => substr_count($line, "\t"),
        ];
        arsort($counts);

        $best = array_key_first($counts);

        return ($counts[$best] ?? 0) > 0 ? $best : ',';
    }

    private static function toUtf8($value): string
    {
        if ($value === null) {
            return '';
        }

        return mb_check_encoding($value, 'UTF-8')
            ? $value
            : mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
    }
}
