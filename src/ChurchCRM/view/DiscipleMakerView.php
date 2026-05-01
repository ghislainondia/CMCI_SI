<?php

namespace ChurchCRM\View;

use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\Service\DiscipleMakerService;

class DiscipleMakerView
{
    private $discipleMakerService;

    public function __construct()
    {
        $this->discipleMakerService = new DiscipleMakerService();
    }

    /**
     * Obtenir les données du faiseur de disciple pour l'affichage
     *
     * @param int $personId ID de la personne
     * @return array Données du faiseur de disciple
     */
    public function getDiscipleMakerData(int $personId): array
    {
        $discipleMaker = $this->discipleMakerService->getDiscipleMaker($personId);

        return [
            'hasDiscipleMaker' => $discipleMaker !== null,
            'discipleMaker' => $discipleMaker,
            'canEdit' => $this->canEditDiscipleMaker()
        ];
    }

    /**
     * Obtenir la liste des disciples d'une personne pour l'affichage
     *
     * @param int $personId ID de la personne (faiseur de disciple)
     * @return array Liste des disciples
     */
    public function getDisciplesData(int $personId): array
    {
        $disciples = $this->discipleMakerService->getDisciples($personId);
        $stats = $this->discipleMakerService->getDisciplesStats($personId);

        return [
            'disciples' => $disciples,
            'stats' => $stats,
            'totalDisciples' => count($disciples)
        ];
    }

    /**
     * Vérifier si l'utilisateur actuel peut modifier les faiseurs de disciple
     *
     * @return bool
     */
    private function canEditDiscipleMaker(): bool
    {
        // Vérifier si l'utilisateur a la permission EditRecords
        $currentUser = \ChurchCRM\Authentication\AuthenticationManager::getCurrentUser();
        return $currentUser->isEditRecords();
    }

    /**
     * Renderer le sélecteur de faiseur de disciple
     *
     * @param int $currentPersonId ID de la personne actuelle
     * @param int|null $currentMakerId ID du faiseur actuel
     * @return string HTML du sélecteur
     */
    public function renderDiscipleMakerSelector(int $currentPersonId, ?int $currentMakerId): string
    {
        $potentialMakers = $this->discipleMakerService->getPotentialDiscipleMakers();

        $html = '<select id="discipleMakerSelect" class="form-select" data-current-person="' . $currentPersonId . '">';
        $html .= '<option value="">-- Aucun faiseur de disciple --</option>';

        foreach ($potentialMakers as $maker) {
            // Exclure la personne actuelle de la liste (ne peut pas être son propre faiseur)
            if ($maker['id'] === $currentPersonId) {
                continue;
            }

            $selected = ($maker['id'] === $currentMakerId) ? ' selected' : '';
            $discipleCount = isset($maker['disciplesCount']) ? " ({$maker['disciplesCount']} disciples)" : '';

            $html .= sprintf(
                '<option value="%d"%s>%s%s</option>',
                $maker['id'],
                $selected,
                htmlspecialchars($maker['fullName']),
                $discipleCount
            );
        }

        $html .= '</select>';

        return $html;
    }
}
