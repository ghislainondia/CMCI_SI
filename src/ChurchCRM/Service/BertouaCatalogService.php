<?php

namespace ChurchCRM\Service;

use Propel\Runtime\Propel;

class BertouaCatalogService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listModules(): array
    {
        BertouaSchemaService::ensureSchema();

        $connection = Propel::getConnection();
        $stmt = $connection->query(
            'SELECT * FROM bertoua_btm_module ORDER BY btm_SortOrder ASC, btm_Title ASC'
        );

        $modules = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $modules[] = $this->hydrateModuleRow($row);
        }

        return $modules;
    }

    public function getModuleById(int $moduleId): ?array
    {
        $connection = Propel::getConnection();
        $stmt = $connection->prepare('SELECT * FROM bertoua_btm_module WHERE btm_ID = :id');
        $stmt->execute(['id' => $moduleId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? null : $this->hydrateModuleRow($row);
    }

    public function createModule(string $title, int $sortOrder = 0): int
    {
        $connection = Propel::getConnection();
        $now = date('Y-m-d H:i:s');
        if ($sortOrder <= 0) {
            $sortOrder = (int) $connection->query('SELECT COALESCE(MAX(btm_SortOrder), 0) + 1 FROM bertoua_btm_module')->fetchColumn();
        }

        $stmt = $connection->prepare(
            'INSERT INTO bertoua_btm_module (btm_Title, btm_SortOrder, btm_DateEntered, btm_DateLastEdited)
             VALUES (:title, :sortOrder, :now, :now)'
        );
        $stmt->execute([
            'title' => $title,
            'sortOrder' => $sortOrder,
            'now' => $now,
        ]);

        return (int) $connection->lastInsertId();
    }

    public function updateModule(int $moduleId, string $title, ?int $sortOrder = null): bool
    {
        $connection = Propel::getConnection();
        $now = date('Y-m-d H:i:s');

        if ($sortOrder !== null) {
            $stmt = $connection->prepare(
                'UPDATE bertoua_btm_module SET btm_Title = :title, btm_SortOrder = :sortOrder, btm_DateLastEdited = :now WHERE btm_ID = :id'
            );
            $stmt->execute(['title' => $title, 'sortOrder' => $sortOrder, 'now' => $now, 'id' => $moduleId]);
        } else {
            $stmt = $connection->prepare(
                'UPDATE bertoua_btm_module SET btm_Title = :title, btm_DateLastEdited = :now WHERE btm_ID = :id'
            );
            $stmt->execute(['title' => $title, 'now' => $now, 'id' => $moduleId]);
        }

        return $stmt->rowCount() > 0;
    }

    public function deleteModule(int $moduleId): bool
    {
        $connection = Propel::getConnection();
        $stmt = $connection->prepare('DELETE FROM bertoua_btm_module WHERE btm_ID = :id');
        $stmt->execute(['id' => $moduleId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @param array<int, int> $orderedModuleIds module id => sort order (1-based)
     */
    public function reorderModules(array $orderedModuleIds): void
    {
        $connection = Propel::getConnection();
        $now = date('Y-m-d H:i:s');
        $stmt = $connection->prepare(
            'UPDATE bertoua_btm_module SET btm_SortOrder = :sortOrder, btm_DateLastEdited = :now WHERE btm_ID = :id'
        );

        $order = 1;
        foreach ($orderedModuleIds as $moduleId) {
            $stmt->execute(['sortOrder' => $order++, 'now' => $now, 'id' => (int) $moduleId]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listLessons(int $moduleId): array
    {
        $connection = Propel::getConnection();
        $stmt = $connection->prepare(
            'SELECT * FROM bertoua_btl_lecon WHERE btl_ModuleId = :moduleId ORDER BY btl_SortOrder ASC, btl_Title ASC'
        );
        $stmt->execute(['moduleId' => $moduleId]);

        $lessons = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $lessons[] = $this->hydrateLessonRow($row);
        }

        return $lessons;
    }

    public function getLessonById(int $lessonId): ?array
    {
        $connection = Propel::getConnection();
        $stmt = $connection->prepare('SELECT * FROM bertoua_btl_lecon WHERE btl_ID = :id');
        $stmt->execute(['id' => $lessonId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? null : $this->hydrateLessonRow($row);
    }

    public function createLesson(int $moduleId, string $title, int $sortOrder = 0): int
    {
        $connection = Propel::getConnection();
        $now = date('Y-m-d H:i:s');
        if ($sortOrder <= 0) {
            $stmt = $connection->prepare(
                'SELECT COALESCE(MAX(btl_SortOrder), 0) + 1 FROM bertoua_btl_lecon WHERE btl_ModuleId = :moduleId'
            );
            $stmt->execute(['moduleId' => $moduleId]);
            $sortOrder = (int) $stmt->fetchColumn();
        }

        $stmt = $connection->prepare(
            'INSERT INTO bertoua_btl_lecon (btl_ModuleId, btl_Title, btl_SortOrder, btl_DateEntered, btl_DateLastEdited)
             VALUES (:moduleId, :title, :sortOrder, :now, :now)'
        );
        $stmt->execute([
            'moduleId' => $moduleId,
            'title' => $title,
            'sortOrder' => $sortOrder,
            'now' => $now,
        ]);

        return (int) $connection->lastInsertId();
    }

    public function updateLesson(int $lessonId, string $title, ?int $sortOrder = null): bool
    {
        $connection = Propel::getConnection();
        $now = date('Y-m-d H:i:s');

        if ($sortOrder !== null) {
            $stmt = $connection->prepare(
                'UPDATE bertoua_btl_lecon SET btl_Title = :title, btl_SortOrder = :sortOrder, btl_DateLastEdited = :now WHERE btl_ID = :id'
            );
            $stmt->execute(['title' => $title, 'sortOrder' => $sortOrder, 'now' => $now, 'id' => $lessonId]);
        } else {
            $stmt = $connection->prepare(
                'UPDATE bertoua_btl_lecon SET btl_Title = :title, btl_DateLastEdited = :now WHERE btl_ID = :id'
            );
            $stmt->execute(['title' => $title, 'now' => $now, 'id' => $lessonId]);
        }

        return $stmt->rowCount() > 0;
    }

    public function deleteLesson(int $lessonId): bool
    {
        $connection = Propel::getConnection();
        $stmt = $connection->prepare('DELETE FROM bertoua_btl_lecon WHERE btl_ID = :id');
        $stmt->execute(['id' => $lessonId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @param array<int, int> $orderedLessonIds
     */
    public function reorderLessons(int $moduleId, array $orderedLessonIds): void
    {
        $connection = Propel::getConnection();
        $now = date('Y-m-d H:i:s');
        $stmt = $connection->prepare(
            'UPDATE bertoua_btl_lecon SET btl_SortOrder = :sortOrder, btl_DateLastEdited = :now
             WHERE btl_ID = :id AND btl_ModuleId = :moduleId'
        );

        $order = 1;
        foreach ($orderedLessonIds as $lessonId) {
            $stmt->execute([
                'sortOrder' => $order++,
                'now' => $now,
                'id' => (int) $lessonId,
                'moduleId' => $moduleId,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function hydrateModuleRow(array $row): array
    {
        return [
            'id' => (int) $row['btm_ID'],
            'title' => (string) $row['btm_Title'],
            'sortOrder' => (int) $row['btm_SortOrder'],
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function hydrateLessonRow(array $row): array
    {
        return [
            'id' => (int) $row['btl_ID'],
            'moduleId' => (int) $row['btl_ModuleId'],
            'title' => (string) $row['btl_Title'],
            'sortOrder' => (int) $row['btl_SortOrder'],
        ];
    }
}
