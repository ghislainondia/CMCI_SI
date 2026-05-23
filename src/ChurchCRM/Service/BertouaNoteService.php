<?php

namespace ChurchCRM\Service;

use ChurchCRM\Authentication\AuthenticationManager;
use Propel\Runtime\Propel;
use Slim\Exception\HttpForbiddenException;

class BertouaNoteService
{
    private BertouaAccessService $accessService;

    private BertouaCatalogService $catalogService;

    public function __construct(
        ?BertouaAccessService $accessService = null,
        ?BertouaCatalogService $catalogService = null
    ) {
        $this->accessService = $accessService ?? new BertouaAccessService();
        $this->catalogService = $catalogService ?? new BertouaCatalogService();
    }

    /**
     * @return array<int, array{id: int, personId: int, famId: int, note: string, saisiePar: int, dateSaisie: string}>
     */
    public function getNotesForLesson(int $lessonId, int $groupId): array
    {
        BertouaSchemaService::ensureSchema();
        $this->assertLessonContext($lessonId, $groupId);

        $memberIds = array_column($this->accessService->getAssemblyMembers($groupId), 'id');
        if ($memberIds === []) {
            return [];
        }

        $connection = Propel::getConnection();
        $placeholders = implode(',', array_fill(0, count($memberIds), '?'));
        $sql = "SELECT n.* FROM bertoua_btn_note n
                WHERE n.btn_LeconId = ?
                AND n.btn_PersonId IN ($placeholders)";
        $stmt = $connection->prepare($sql);
        $stmt->execute(array_merge([$lessonId], $memberIds));

        $notes = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $note = $this->hydrateNoteRow($row);
            $notes[(int) $note['personId']] = $note;
        }

        return $notes;
    }

    /**
     * @param array<int, string> $notesByPersonId personId => note text
     */
    public function saveNotes(int $lessonId, int $groupId, array $notesByPersonId): int
    {
        BertouaSchemaService::ensureSchema();
        $this->assertLessonContext($lessonId, $groupId);

        $members = $this->accessService->getAssemblyMembers($groupId);
        $memberMap = [];
        foreach ($members as $member) {
            $memberMap[(int) $member['id']] = (int) $member['famId'];
        }

        $connection = Propel::getConnection();
        $userId = (int) AuthenticationManager::getCurrentUser()->getId();
        $now = date('Y-m-d H:i:s');
        $saved = 0;

        $upsert = $connection->prepare(
            'INSERT INTO bertoua_btn_note (btn_LeconId, btn_PersonId, btn_FamId, btn_Note, btn_SaisiePar, btn_DateSaisie)
             VALUES (:leconId, :personId, :famId, :note, :saisiePar, :dateSaisie)
             ON DUPLICATE KEY UPDATE btn_Note = VALUES(btn_Note), btn_FamId = VALUES(btn_FamId),
             btn_SaisiePar = VALUES(btn_SaisiePar), btn_DateSaisie = VALUES(btn_DateSaisie)'
        );

        $delete = $connection->prepare(
            'DELETE FROM bertoua_btn_note WHERE btn_LeconId = :leconId AND btn_PersonId = :personId'
        );

        foreach ($notesByPersonId as $personId => $noteText) {
            $personId = (int) $personId;
            if (!array_key_exists($personId, $memberMap)) {
                throw new HttpForbiddenException(
                    null,
                    gettext('You cannot save notes for members outside your house assembly.')
                );
            }

            $noteText = trim((string) $noteText);
            if ($noteText === '') {
                $delete->execute(['leconId' => $lessonId, 'personId' => $personId]);
                continue;
            }

            $upsert->execute([
                'leconId' => $lessonId,
                'personId' => $personId,
                'famId' => $memberMap[$personId],
                'note' => $noteText,
                'saisiePar' => $userId,
                'dateSaisie' => $now,
            ]);
            $saved++;
        }

        return $saved;
    }

    private function assertLessonContext(int $lessonId, int $groupId): void
    {
        $this->accessService->assertCanAccessAssemblyGroup($groupId);

        $lesson = $this->catalogService->getLessonById($lessonId);
        if ($lesson === null) {
            throw new HttpForbiddenException(null, gettext('Lesson not found.'));
        }
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function hydrateNoteRow(array $row): array
    {
        return [
            'id' => (int) $row['btn_ID'],
            'leconId' => (int) $row['btn_LeconId'],
            'personId' => (int) $row['btn_PersonId'],
            'famId' => (int) $row['btn_FamId'],
            'note' => (string) ($row['btn_Note'] ?? ''),
            'saisiePar' => (int) $row['btn_SaisiePar'],
            'dateSaisie' => (string) $row['btn_DateSaisie'],
        ];
    }
}
