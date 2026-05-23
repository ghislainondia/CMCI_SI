<?php

namespace ChurchCRM\Service;

class BertouaCatalogImportService
{
    private BertouaCatalogService $catalog;

    public function __construct(?BertouaCatalogService $catalog = null)
    {
        $this->catalog = $catalog ?? new BertouaCatalogService();
    }

    /**
     * @return array{
     *   modulesCreated: int,
     *   lessonsCreated: int,
     *   modulesUpdated: int,
     *   lessonsSkipped: int,
     *   rowsProcessed: int,
     *   errors: array<int, string>
     * }
     */
    public function importFromCsvFile(string $filePath, bool $replaceExisting = false): array
    {
        BertouaSchemaService::ensureSchema();

        $stats = [
            'modulesCreated' => 0,
            'lessonsCreated' => 0,
            'modulesUpdated' => 0,
            'lessonsSkipped' => 0,
            'rowsProcessed' => 0,
            'errors' => [],
        ];

        if (!is_readable($filePath)) {
            $stats['errors'][] = gettext('Unable to read the CSV file.');

            return $stats;
        }

        if ($replaceExisting) {
            $this->catalog->deleteAllCatalog();
        }

        $rows = $this->parseCsvFile($filePath);
        if ($rows === []) {
            $stats['errors'][] = gettext('The CSV file is empty or has no data rows.');

            return $stats;
        }

        /** @var array<string, array{id: int, title: string, sortOrder: int}> $moduleIndex */
        $moduleIndex = [];
        foreach ($this->catalog->listModules() as $module) {
            $moduleIndex[$this->normalizeKey($module['title'])] = $module;
        }

        /** @var array<string, array<string, true>> $lessonIndex moduleKey => [lessonKey => true] */
        $lessonIndex = [];

        foreach ($rows as $lineNumber => $row) {
            $stats['rowsProcessed']++;
            $moduleTitle = trim((string) ($row['module'] ?? ''));
            $lessonTitle = trim((string) ($row['lesson'] ?? ''));

            if ($moduleTitle === '') {
                $stats['errors'][] = sprintf(
                    gettext('Line %d: module title is required.'),
                    $lineNumber
                );
                continue;
            }

            $moduleKey = $this->normalizeKey($moduleTitle);
            $moduleOrder = $this->parseOptionalInt($row['module_order'] ?? null);

            if (!isset($moduleIndex[$moduleKey])) {
                $moduleId = $this->catalog->createModule(
                    $moduleTitle,
                    $moduleOrder ?? 0
                );
                $moduleIndex[$moduleKey] = [
                    'id' => $moduleId,
                    'title' => $moduleTitle,
                    'sortOrder' => $moduleOrder ?? 0,
                ];
                $lessonIndex[$moduleKey] = [];
                $stats['modulesCreated']++;
            } else {
                $module = $moduleIndex[$moduleKey];
                if ($moduleOrder !== null && $moduleOrder > 0) {
                    $this->catalog->updateModule((int) $module['id'], $module['title'], $moduleOrder);
                    $stats['modulesUpdated']++;
                }
            }

            if ($lessonTitle === '') {
                continue;
            }

            $moduleId = (int) $moduleIndex[$moduleKey]['id'];
            $lessonKey = $this->normalizeKey($lessonTitle);

            if (!isset($lessonIndex[$moduleKey])) {
                $lessonIndex[$moduleKey] = $this->loadLessonKeysForModule($moduleId);
            }

            if (isset($lessonIndex[$moduleKey][$lessonKey])) {
                $stats['lessonsSkipped']++;
                continue;
            }

            $lessonOrder = $this->parseOptionalInt($row['lesson_order'] ?? null);
            $this->catalog->createLesson($moduleId, $lessonTitle, $lessonOrder ?? 0);
            $lessonIndex[$moduleKey][$lessonKey] = true;
            $stats['lessonsCreated']++;
        }

        return $stats;
    }

    public static function getTemplateCsvContent(): string
    {
        return "module;module_order;lesson;lesson_order\n"
            . "Module 1 - Introduction;1;Leçon 1;1\n"
            . "Module 1 - Introduction;1;Leçon 2;2\n"
            . "Module 2 - Approfondissement;2;Leçon 1;1\n";
    }

    /**
     * @return array<int, array{module: string, module_order: string, lesson: string, lesson_order: string}>
     */
    private function parseCsvFile(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        $firstLine = preg_replace('/^\x{EF}\x{BB}\x{BF}/u', '', $firstLine) ?? $firstLine;
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $headers = array_map(
            fn (string $h): string => $this->normalizeHeader($h),
            str_getcsv($firstLine, $delimiter)
        );

        $columnMap = $this->buildColumnMap($headers);
        if ($columnMap['module'] === null) {
            fclose($handle);

            return [];
        }

        $rows = [];
        $lineNumber = 1;
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $lineNumber++;
            if ($this->isEmptyRow($data)) {
                continue;
            }

            $row = [
                'module' => $this->cellValue($data, $columnMap['module']),
                'module_order' => $this->cellValue($data, $columnMap['module_order']),
                'lesson' => $this->cellValue($data, $columnMap['lesson']),
                'lesson_order' => $this->cellValue($data, $columnMap['lesson_order']),
            ];

            if ($row['module'] === '' && $row['lesson'] === '') {
                continue;
            }

            $rows[$lineNumber] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @param array<int, string|null> $headers
     * @return array{module: ?int, module_order: ?int, lesson: ?int, lesson_order: ?int}
     */
    private function buildColumnMap(array $headers): array
    {
        $map = [
            'module' => null,
            'module_order' => null,
            'lesson' => null,
            'lesson_order' => null,
        ];

        foreach ($headers as $index => $header) {
            if ($header === 'module' || $header === 'module_title' || $header === 'titre_module') {
                $map['module'] = $index;
            } elseif (in_array($header, ['module_order', 'ordre_module', 'module_ordre'], true)) {
                $map['module_order'] = $index;
            } elseif (in_array($header, ['lesson', 'lecon', 'lesson_title', 'titre_lecon'], true)) {
                $map['lesson'] = $index;
            } elseif (in_array($header, ['lesson_order', 'ordre_lecon', 'lesson_ordre'], true)) {
                $map['lesson_order'] = $index;
            }
        }

        return $map;
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim(mb_strtolower($header));
        $header = str_replace([' ', '-', 'é', 'è', 'ê', 'à', 'ù', 'ô'], ['_', '_', 'e', 'e', 'e', 'a', 'u', 'o'], $header);

        return $header;
    }

    private function normalizeKey(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    /**
     * @param array<int, string> $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, string> $data
     */
    private function cellValue(array $data, ?int $index): string
    {
        if ($index === null || !array_key_exists($index, $data)) {
            return '';
        }

        return trim((string) $data[$index]);
    }

    private function parseOptionalInt(?string $value): ?int
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * @return array<string, true>
     */
    private function loadLessonKeysForModule(int $moduleId): array
    {
        $keys = [];
        foreach ($this->catalog->listLessons($moduleId) as $lesson) {
            $keys[$this->normalizeKey($lesson['title'])] = true;
        }

        return $keys;
    }
}
