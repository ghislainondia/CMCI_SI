#!/usr/bin/env python3
"""
Convertit le fichier membre.csv au format ChurchCRM
"""

import csv
import sys

input_file = sys.argv[1] if len(sys.argv) > 1 else 'membre.csv'
output_file = sys.argv[2] if len(sys.argv) > 2 else 'membre_churchcrm.csv'

# Lire le fichier original
with open(input_file, 'r', encoding='utf-8') as f:
    reader = csv.DictReader(f, delimiter=';')
    rows = list(reader)

# Écrire au format ChurchCRM
with open(output_file, 'w', encoding='utf-8', newline='') as f:
    writer = csv.writer(f)

    # En-têtes ChurchCRM
    headers = ['FamilyID', 'FirstName', 'LastName', 'Gender', 'BirthDate',
               'Classification', 'FamilyRole', 'Address1', 'City', 'HomePhone',
               'MobilePhone', 'Email', 'MembershipDate']

    writer.writerow(headers)

    for i, row in enumerate(rows, start=1):
        first_name = row['Prénom'].strip()
        last_name = row['Nom'].strip()
        birth_date = row['Date d\'anniversaire'].strip()
        phone = row['Contact'].strip()
        location = row['localisation'].strip()

        # Déterminer le genre basé sur le prénom (heuristique simple)
        gender = ''
        if first_name:
            # Terminaisons féminines courantes en français
            feminine_endings = ['e', 'a', 'ine', 'elle', 'ette', 'ie', 'yste']
            if any(first_name.lower().endswith(ending) for ending in feminine_endings):
                gender = 'Female'
            else:
                gender = 'Male'

        # Nettoyer le téléphone
        clean_phone = ''
        if phone:
            clean_phone = ''.join(c for c in phone if c.isdigit() or c == '+')

        # Créer une ligne par défaut
        writer.writerow([
            i,                    # FamilyID (unique pour importer comme famille individuelle)
            first_name,           # FirstName
            last_name,            # LastName
            gender,               # Gender
            birth_date if birth_date and birth_date != 'Actif' else '',  # BirthDate
            'Member',             # Classification
            'Head of Household',  # FamilyRole
            '',                   # Address1
            location,             # City
            clean_phone,          # HomePhone
            clean_phone,          # MobilePhone
            '',                   # Email
            ''                    # MembershipDate
        ])

print(f"Converti {len(rows)} membres vers {output_file}")
