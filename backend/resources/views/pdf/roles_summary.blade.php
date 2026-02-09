<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Récapitulatif des Rôles et Permissions</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; margin: 30px; }
        .header { text-align: center; border-bottom: 2px solid #ed1c24; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { width: 60px; margin-bottom: 5px; }
        h1 { color: #ed1c24; font-size: 18px; margin: 0; text-transform: uppercase; }
        .subtitle { font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #f2f2f2; color: #333; font-weight: bold; text-align: left; border: 1px solid #ccc; padding: 10px; }
        td { border: 1px solid #ccc; padding: 10px; vertical-align: top; line-height: 1.4; }
        .role-name { font-weight: bold; color: #ed1c24; font-size: 12px; }
        .module { color: #555; font-style: italic; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Configuration Globale des Accès</h1>
        <div class="subtitle">Architecture de Sécurité - PGFE ENABEL</div>
        <div style="margin-top: 5px;">Edité le {{ date('d/m/Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Rôle Utilisateur</th>
                <th style="width: 25%;">Domaine / Module</th>
                <th style="width: 50%;">Actions et Permissions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="role-name">Administrateur Global</td>
                <td class="module">Multi-Établissements</td>
                <td>Contrôle total sur l'ensemble de la plateforme. Gestion technique, maintenance des données globales et création des comptes administrateurs d'écoles.</td>
            </tr>
            <tr>
                <td class="role-name">Admin École</td>
                <td class="module">Établissement (Local)</td>
                <td>Supervision totale de son école. Accès à tous les domaines (Finance, RH, Stock, Pédagogie) pour l'unité locale.</td>
            </tr>
            <tr>
                <td class="role-name">Enseignant</td>
                <td class="module">Pédagogie & Académique</td>
                <td>Gestion des cours. Saisie et modification des notes (cotations). Participation aux processus de délibération. Consultation des informations élèves.</td>
            </tr>
            <tr>
                <td class="role-name">Comptable</td>
                <td class="module">Finance & Comptabilité</td>
                <td>Encaissement des frais scolaires. Gestion des dépenses de l'établissement. Gestion du plan comptable, des journaux et des rapports financiers.</td>
            </tr>
            <tr>
                <td class="role-name">Stoker</td>
                <td class="module">Logistique & Services</td>
                <td>Gestion des stocks (articles, fournitures). Gestion des opérations de vente (boutique) et des locations de matériel ou infrastructures.</td>
            </tr>
            <tr>
                <td class="role-name">RH (Personnel)</td>
                <td class="module">Ressources Humaines</td>
                <td>Gestion du personnel académique et administratif. Suivi des dossiers agents, fiches de poste et dossiers administratifs.</td>
            </tr>
            <tr>
                <td class="role-name">Inspecteur</td>
                <td class="module">Suivi Pédagogique</td>
                <td>Planification et exécution des visites de classe. Contrôle de la qualité de l'enseignement. Consultation des horaires et structures de classe.</td>
            </tr>
            <tr>
                <td class="role-name">Disciplinaire</td>
                <td class="module">Vie Scolaire</td>
                <td>Gestion complète de la discipline. Saisie des manquements, des retards, des sanctions et des mérites. Suivi comportemental des élèves.</td>
            </tr>
            <tr>
                <td class="role-name">Tiers</td>
                <td class="module">Consultation</td>
                <td>Accès par défaut limité. Consultation restreinte selon le profil (ex: parent, visiteur) sans droit de modification sur les données métier.</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Document Officiel PGFE v2 - Système de Gestion Intégrée
    </div>
</body>
</html>
