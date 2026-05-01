<?php
// Script pour remplacer "Family" par "Assemblées de maison" dans les fichiers de traduction

$poFile = '/Users/mac/ChurchCRM copie/churchcrm-local/locale/messages.po';
$backupFile = '/Users/mac/ChurchCRM copie/churchcrm-local/locale/messages.po.backup2';

// Sauvegarder le fichier actuel
if (!file_exists($backupFile)) {
    copy($poFile, $backupFile);
    echo "Backup created: $backupFile\n";
}

// Lire le fichier
$content = file_get_contents($poFile);

// Remplacements spécifiques pour le français
$replacements = [
    // Termes principaux
    'msgid "Family"\nmsgstr ""' => 'msgid "Family"\nmsgstr "Assemblées de maison"',
    'msgid "Family Members"\nmsgstr ""' => 'msgid "Family Members"\nmsgstr "Membres de l\'assemblée de maison"',
    'msgid "Family Members:"\nmsgstr ""' => 'msgid "Family Members:"\nmsgstr "Membres de l\'assemblée de maison:"',
    'msgid "Family List"\nmsgstr ""' => 'msgid "Family List"\nmsgstr "Liste des assemblées de maison"',
    'msgid "Family Listing"\nmsgstr ""' => 'msgid "Family Listing"\nmsgstr "Liste des assemblées de maison"',
    'msgid "Family Info"\nmsgstr ""' => 'msgid "Family Info"\nmsgstr "Informations de l\'assemblée de maison"',
    'msgid "Family Editor"\nmsgstr ""' => 'msgid "Family Editor"\nmsgstr "Éditeur d\'assemblée de maison"',
    'msgid "Add Family"\nmsgstr ""' => 'msgid "Add Family"\nmsgstr "Ajouter une assemblée de maison"',
    'msgid "Add Family Member"\nmsgstr ""' => 'msgid "Add Family Member"\nmsgstr "Ajouter un membre à l\'assemblée de maison"',
    'msgid "Edit Family"\nmsgstr ""' => 'msgid "Edit Family"\nmsgstr "Modifier l\'assemblée de maison"',
    'msgid "Delete Family"\nmsgstr ""' => 'msgid "Delete Family"\nmsgstr "Supprimer l\'assemblée de maison"',
    'msgid "Family Record"\nmsgstr ""' => 'msgid "Family Record"\nmsgstr "Fiche d\'assemblée de maison"',
    'msgid "Family Name"\nmsgstr ""' => 'msgid "Family Name"\nmsgstr "Nom de l\'assemblée de maison"',
    'msgid "Family Email"\nmsgstr ""' => 'msgid "Family Email"\nmsgstr "Email de l\'assemblée de maison"',
    'msgid "Family Home Phone"\nmsgstr ""' => 'msgid "Family Home Phone"\nmsgstr "Téléphone domicile de l\'assemblée"',
    'msgid "Family Cell Phone"\nmsgstr ""' => 'msgid "Family Cell Phone"\nmsgstr "Téléphone portable de l\'assemblée"',
    'msgid "Family Custom"\nmsgstr ""' => 'msgid "Family Custom"\nmsgstr "Champs personnalisés d\'assemblée"',
    'msgid "Family Custom Fields"\nmsgstr ""' => 'msgid "Family Custom Fields"\nmsgstr "Champs personnalisés d\'assemblée de maison"',
    'msgid "Custom Family Fields"\nmsgstr ""' => 'msgid "Custom Family Fields"\nmsgstr "Champs personnalisés d\'assemblée de maison"',
    'msgid "Family Geographic"\nmsgstr ""' => 'msgid "Family Geographic"\nmsgstr "Données géographiques de l\'assemblée"',
    'msgid "Family Map"\nmsgstr ""' => 'msgid "Family Map"\nmsgstr "Carte des assemblées de maison"',
    'msgid "Family Role"\nmsgstr ""' => 'msgid "Family Role"\nmsgstr "Rôle dans l\'assemblée de maison"',
    'msgid "Family Roles"\nmsgstr ""' => 'msgid "Family Roles"\nmsgstr "Rôles dans l\'assemblée de maison"',
    'msgid "Family View"\nmsgstr ""' => 'msgid "Family View"\nmsgstr "Vue de l\'assemblée de maison"',
];

// Appliquer les remplacements
$replacedCount = 0;
foreach ($replacements as $search => $replace) {
    $newContent = str_replace($search, $replace, $content);
    if ($newContent !== $content) {
        $replacedCount++;
        echo "Replaced: " . substr($search, 0, 50) . "...\n";
    }
    $content = $newContent;
}

// Sauvegarder le contenu modifié
file_put_contents($poFile, $content);

echo "\n✅ Total replacements made: $replacedCount\n";
echo "🔄 Now rebuilding .mo file...\n";

// Compiler le fichier .po en .mo
$moFile = '/Users/mac/ChurchCRM copie/churchcrm-local/locale/fr/LC_MESSAGES/messages.mo';
$cmd = "msgfmt --statistics -o " . escapeshellarg($moFile) . " " . escapeshellarg($poFile) . " 2>&1";
exec($cmd, $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ .mo file compiled successfully\n";
} else {
    echo "❌ Error compiling .mo file\n";
    echo implode("\n", $output) . "\n";
}
?>
