<?php

namespace ChurchCRM\Service;

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\dto\SystemConfig;
use ChurchCRM\dto\ChurchVocabulary;
use ChurchCRM\model\ChurchCRM\FamilyQuery;
use ChurchCRM\model\ChurchCRM\GroupQuery;
use ChurchCRM\model\ChurchCRM\ListOptionQuery;
use ChurchCRM\model\ChurchCRM\Person2group2roleP2g2rQuery;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\Utils\LoggerUtils;
use Propel\Runtime\Propel;

class MeetingService
{
    public const ORGANIZER_FAMILY = 'family';
    public const ORGANIZER_GROUP = 'group';
    public const ORGANIZER_CHURCH = 'church';

    private $logger;

    public function __construct()
    {
        $this->logger = LoggerUtils::getAppLogger();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllMeetings(): array
    {
        $connection = Propel::getConnection();
        $stmt = $connection->query(
            'SELECT * FROM meeting_mtg ORDER BY mtg_DateTime DESC, mtg_Name ASC'
        );

        $meetings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $meetings[] = $this->hydrateMeetingRow($row);
        }

        return $meetings;
    }

    public function getMeetingById(int $meetingId): ?array
    {
        $connection = Propel::getConnection();
        $stmt = $connection->prepare('SELECT * FROM meeting_mtg WHERE mtg_ID = :id');
        $stmt->execute(['id' => $meetingId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $meeting = $this->hydrateMeetingRow($row);
        $meeting['attendance'] = $this->getAttendance($meetingId);

        return $meeting;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, array{personId: int, isPresent: bool}> $attendance
     */
    public function createMeeting(array $data, array $attendance): int
    {
        $connection = Propel::getConnection();
        $userId = AuthenticationManager::getCurrentUser()->getId();
        $now = date('Y-m-d H:i:s');

        $stmt = $connection->prepare(
            'INSERT INTO meeting_mtg (
                mtg_Name, mtg_DateTime, mtg_OrganizerType, mtg_OrganizerId, mtg_Remarks,
                mtg_DateEntered, mtg_DateLastEdited, mtg_EnteredBy, mtg_EditedBy
            ) VALUES (
                :name, :meetingDateTime, :organizerType, :organizerId, :remarks,
                :dateEntered, :dateLastEdited, :enteredBy, :editedBy
            )'
        );
        $stmt->execute([
            'name' => $data['name'],
            'meetingDateTime' => $data['meetingDateTime'],
            'organizerType' => $data['organizerType'],
            'organizerId' => (int) $data['organizerId'],
            'remarks' => $data['remarks'] ?? null,
            'dateEntered' => $now,
            'dateLastEdited' => $now,
            'enteredBy' => $userId,
            'editedBy' => $userId,
        ]);

        $meetingId = (int) $connection->lastInsertId();
        $this->saveAttendance($meetingId, $attendance);

        return $meetingId;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, array{personId: int, isPresent: bool}> $attendance
     */
    public function updateMeeting(int $meetingId, array $data, array $attendance): bool
    {
        if ($this->getMeetingById($meetingId) === null) {
            return false;
        }

        $connection = Propel::getConnection();
        $userId = AuthenticationManager::getCurrentUser()->getId();

        $stmt = $connection->prepare(
            'UPDATE meeting_mtg SET
                mtg_Name = :name,
                mtg_DateTime = :meetingDateTime,
                mtg_OrganizerType = :organizerType,
                mtg_OrganizerId = :organizerId,
                mtg_Remarks = :remarks,
                mtg_DateLastEdited = :dateLastEdited,
                mtg_EditedBy = :editedBy
            WHERE mtg_ID = :id'
        );
        $stmt->execute([
            'name' => $data['name'],
            'meetingDateTime' => $data['meetingDateTime'],
            'organizerType' => $data['organizerType'],
            'organizerId' => (int) $data['organizerId'],
            'remarks' => $data['remarks'] ?? null,
            'dateLastEdited' => date('Y-m-d H:i:s'),
            'editedBy' => $userId,
            'id' => $meetingId,
        ]);

        $this->saveAttendance($meetingId, $attendance);

        return true;
    }

    public function deleteMeeting(int $meetingId): bool
    {
        $connection = Propel::getConnection();
        $stmt = $connection->prepare('DELETE FROM meeting_mtg WHERE mtg_ID = :id');
        $stmt->execute(['id' => $meetingId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAttendance(int $meetingId): array
    {
        $connection = Propel::getConnection();
        $stmt = $connection->prepare(
            'SELECT mat.*, per.per_FirstName, per.per_LastName
             FROM meeting_attendance_mat mat
             INNER JOIN person_per per ON mat.mat_PersonId = per.per_ID
             WHERE mat.mat_MeetingId = :meetingId
             ORDER BY per.per_LastName, per.per_FirstName'
        );
        $stmt->execute(['meetingId' => $meetingId]);

        $rows = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $person = PersonQuery::create()->findPk((int) $row['mat_PersonId']);
            $rows[] = [
                'id' => (int) $row['mat_ID'],
                'personId' => (int) $row['mat_PersonId'],
                'isPresent' => (bool) $row['mat_IsPresent'],
                'firstName' => $row['per_FirstName'],
                'lastName' => $row['per_LastName'],
                'fullName' => $person ? $person->getFullName() : trim($row['per_FirstName'] . ' ' . $row['per_LastName']),
            ];
        }

        return $rows;
    }

    /**
     * @return array{families: array, groups: array, organizations: array}
     */
    public function getOrganizerOptions(): array
    {
        $families = [];
        foreach (FamilyQuery::create()->orderByName()->find() as $family) {
            $families[] = [
                'type' => self::ORGANIZER_FAMILY,
                'id' => (int) $family->getId(),
                'name' => $family->getName(),
                'value' => self::ORGANIZER_FAMILY . ':' . $family->getId(),
            ];
        }

        $groups = [];
        $organizations = [];
        $organizationTypeIds = $this->getOrganizationGroupTypeIds();

        foreach (GroupQuery::create()->orderByName()->find() as $group) {
            $entry = [
                'type' => self::ORGANIZER_GROUP,
                'id' => (int) $group->getId(),
                'name' => $group->getName(),
                'value' => self::ORGANIZER_GROUP . ':' . $group->getId(),
            ];

            if (in_array((int) $group->getType(), $organizationTypeIds, true)) {
                $organizations[] = [
                    'type' => self::ORGANIZER_GROUP,
                    'id' => (int) $group->getId(),
                    'name' => $group->getName(),
                    'value' => self::ORGANIZER_GROUP . ':' . $group->getId(),
                ];
            } else {
                $groups[] = $entry;
            }
        }

        $churchName = SystemConfig::getValue('sChurchName');
        if ($churchName === '') {
            $churchName = gettext('Church');
        }
        array_unshift($organizations, [
            'type' => self::ORGANIZER_CHURCH,
            'id' => 0,
            'name' => $churchName,
            'value' => self::ORGANIZER_CHURCH . ':0',
        ]);

        return [
            'families' => $families,
            'groups' => $groups,
            'organizations' => $organizations,
        ];
    }

    public function resolveOrganizerLabel(string $type, int $id): string
    {
        switch ($type) {
            case self::ORGANIZER_FAMILY:
                $family = FamilyQuery::create()->findPk($id);
                return $family ? ChurchVocabulary::houseAssembly() . ': ' . $family->getName() : gettext('Unknown');
            case self::ORGANIZER_GROUP:
                $group = GroupQuery::create()->findPk($id);
                return $group ? gettext('Group') . ': ' . $group->getName() : gettext('Unknown');
            case self::ORGANIZER_CHURCH:
                $churchName = SystemConfig::getValue('sChurchName');
                return gettext('Organization') . ': ' . ($churchName !== '' ? $churchName : gettext('Church'));
            default:
                return gettext('Unknown');
        }
    }

    /**
     * @return array<int, array{personId: int, fullName: string}>
     */
    public function getSuggestedMembers(string $organizerType, int $organizerId): array
    {
        $personIds = [];

        switch ($organizerType) {
            case self::ORGANIZER_FAMILY:
                if ($organizerId > 0) {
                    foreach (PersonQuery::create()->filterByFamId($organizerId)->find() as $person) {
                        $personIds[] = (int) $person->getId();
                    }
                }
                break;
            case self::ORGANIZER_GROUP:
                if ($organizerId > 0) {
                    foreach (Person2group2roleP2g2rQuery::create()->filterByGroupId($organizerId)->find() as $p2g2r) {
                        $personIds[] = (int) $p2g2r->getPersonId();
                    }
                }
                break;
            case self::ORGANIZER_CHURCH:
            default:
                break;
        }

        $personIds = array_values(array_unique(array_filter($personIds)));

        $members = [];
        foreach ($personIds as $personId) {
            $person = PersonQuery::create()->findPk($personId);
            if ($person !== null) {
                $members[] = [
                    'personId' => $personId,
                    'fullName' => $person->getFullName(),
                ];
            }
        }

        usort($members, static function (array $a, array $b): int {
            return strcasecmp($a['fullName'], $b['fullName']);
        });

        return $members;
    }

    /**
     * @return array<int, array{personId: int, fullName: string}>
     */
    public function getAllPeopleForPicker(): array
    {
        $members = [];
        foreach (PersonQuery::create()->orderByLastName()->orderByFirstName()->find() as $person) {
            $members[] = [
                'personId' => (int) $person->getId(),
                'fullName' => $person->getFullName(),
            ];
        }

        return $members;
    }

    /**
     * @param string $value "family:12" format
     * @return array{type: string, id: int}|null
     */
    public function parseOrganizerValue(string $value): ?array
    {
        if (!preg_match('/^(family|group|church):(\d+)$/', $value, $matches)) {
            return null;
        }

        return [
            'type' => $matches[1],
            'id' => (int) $matches[2],
        ];
    }

    /**
     * @param array<int, array{personId?: int, isPresent?: bool}> $rawAttendance
     * @return array<int, array{personId: int, isPresent: bool}>
     */
    public function normalizeAttendanceInput(array $rawAttendance): array
    {
        $normalized = [];
        foreach ($rawAttendance as $row) {
            $personId = (int) ($row['personId'] ?? 0);
            if ($personId <= 0) {
                continue;
            }
            $normalized[$personId] = [
                'personId' => $personId,
                'isPresent' => !empty($row['isPresent']),
            ];
        }

        return array_values($normalized);
    }

    /**
     * @return array<int>
     */
    private function getOrganizationGroupTypeIds(): array
    {
        $ids = [];
        foreach (ListOptionQuery::create()->filterById(3)->find() as $option) {
            $name = strtolower($option->getOptionName());
            if (
                str_contains($name, 'org')
                || str_contains($name, 'organisation')
                || str_contains($name, 'organization')
                || str_contains($name, 'ministr')
            ) {
                $ids[] = (int) $option->getOptionId();
            }
        }

        return $ids;
    }

    /**
     * @param array<int, array{personId: int, isPresent: bool}> $attendance
     */
    private function saveAttendance(int $meetingId, array $attendance): void
    {
        $connection = Propel::getConnection();
        $deleteStmt = $connection->prepare('DELETE FROM meeting_attendance_mat WHERE mat_MeetingId = :meetingId');
        $deleteStmt->execute(['meetingId' => $meetingId]);

        if (empty($attendance)) {
            return;
        }

        $insertStmt = $connection->prepare(
            'INSERT INTO meeting_attendance_mat (mat_MeetingId, mat_PersonId, mat_IsPresent)
             VALUES (:meetingId, :personId, :isPresent)'
        );

        foreach ($attendance as $row) {
            $insertStmt->execute([
                'meetingId' => $meetingId,
                'personId' => $row['personId'],
                'isPresent' => $row['isPresent'] ? 1 : 0,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function hydrateMeetingRow(array $row): array
    {
        $organizerType = (string) $row['mtg_OrganizerType'];
        $organizerId = (int) $row['mtg_OrganizerId'];

        return [
            'id' => (int) $row['mtg_ID'],
            'name' => $row['mtg_Name'],
            'meetingDateTime' => $row['mtg_DateTime'],
            'organizerType' => $organizerType,
            'organizerId' => $organizerId,
            'organizerValue' => $organizerType . ':' . $organizerId,
            'organizerLabel' => $this->resolveOrganizerLabel($organizerType, $organizerId),
            'remarks' => $row['mtg_Remarks'] ?? '',
            'dateEntered' => $row['mtg_DateEntered'],
            'dateLastEdited' => $row['mtg_DateLastEdited'],
        ];
    }
}
