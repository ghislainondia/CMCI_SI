<?php
// Générer des hashes de mot de passe pour ChurchCRM
$password = "password123";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Mot de passe: $password\n";
echo "Hash: $hash\n";
