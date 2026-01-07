<?php
// RemplirJeuDeDonnees.php

require_once './Classes/Connexion.php';

try {
    $connexion = Connexion::getInstance();
    $manager = $connexion->getManager();
    $dbName = $connexion->getDbName();

    echo "<h1>🚀 Remplissage du Jeu de Données TokaFest</h1>";
    echo "<pre>";

    // =========================================================
    // 0. RÉCUPÉRATION DES SCÈNES (Créées précédemment)
    // =========================================================
    // On doit trouver leurs IDs pour lier les concerts
    $filterMain = ['nom_scene' => 'TokaMain'];
    $queryMain = new MongoDB\Driver\Query($filterMain);
    $resMain = $manager->executeQuery("$dbName.scenes", $queryMain)->toArray();
    
    $filterCtrl = ['nom_scene' => 'TokaCtrlB'];
    $queryCtrl = new MongoDB\Driver\Query($filterCtrl);
    $resCtrl = $manager->executeQuery("$dbName.scenes", $queryCtrl)->toArray();

    if (empty($resMain) || empty($resCtrl)) {
        die("❌ ERREUR : Lance d'abord le script 'NettoyerDonnees.php' pour créer les scènes !");
    }

    $idTokaMain = $resMain[0]->_id;
    $idTokaCtrl = $resCtrl[0]->_id;

    echo "✅ Scènes récupérées : TokaMain & TokaCtrlB\n";

    // =========================================================
    // 1. CRÉATION DES ARTISTES
    // =========================================================
    
    // On génère les IDs à l'avance pour les utiliser dans les concerts
    $idDaftPunk = new MongoDB\BSON\ObjectId();
    $idGuetta   = new MongoDB\BSON\ObjectId();
    $idGims     = new MongoDB\BSON\ObjectId();
    
    $idLuvbox   = new MongoDB\BSON\ObjectId();
    $idShad     = new MongoDB\BSON\ObjectId();
    $idDjPhp    = new MongoDB\BSON\ObjectId();

    $bulk = new MongoDB\Driver\BulkWrite;

    // --- SUR TOKAMAIN ---
    
    // 1. Daft Punk
    $bulk->insert([
        '_id' => $idDaftPunk,
        'nom_scene_artiste' => "Daft Punk",
        'genre_musical' => "French Touch",
        'description' => "Robots légendaires.",
        'est_tete_affiche' => true,
        'membres' => ["Yann Jaquelin", "Jean Jacques"],
        'discographie' => [
            [
                'titreAlbum' => "Discovery", 'anneeSortie' => 2001,
                'tracks' => [
                    ['titre' => "One More Time", 'dureeSecondes' => 320, 'featuring' => []],
                    ['titre' => "Harder, Better, Faster, Stronger", 'dureeSecondes' => 224, 'featuring' => []]
                ]
            ]
        ]
    ]);

    // 2. David Guetta
    $bulk->insert([
        '_id' => $idGuetta,
        'nom_scene_artiste' => "David Guetta",
        'genre_musical' => "EDM",
        'description' => "Le DJ français n°1.",
        'est_tete_affiche' => true,
        'membres' => ["Pierre Roulet", "Jean Charles"],
        'discographie' => [
            [
                'titreAlbum' => "Nothing but the Beat", 'anneeSortie' => 2011,
                'tracks' => [
                    ['titre' => "Titanium", 'dureeSecondes' => 245, 'featuring' => [['nomFeat' => "Sia"]]]
                ]
            ]
        ]
    ]);

    // 3. Gims
    $bulk->insert([
        '_id' => $idGims,
        'nom_scene_artiste' => "Gims",
        'genre_musical' => "Pop Urbaine",
        'description' => "Le roi de la pop urbaine française.",
        'est_tete_affiche' => true,
        'membres' => ["Franck Fort", "Sofia Zaho", "Dadju"],
        'discographie' => [
            [
                'titreAlbum' => "Ceinture Noire", 'anneeSortie' => 2018,
                'tracks' => [
                    ['titre' => "La Même", 'dureeSecondes' => 199, 'featuring' => [['nomFeat' => "Vianney"]]]
                ]
            ]
        ]
    ]);

    // --- SUR TOKACTRLB ---

    // 4. Luvbox (Shranz)
    $bulk->insert([
        '_id' => $idLuvbox,
        'nom_scene_artiste' => "Luvbox",
        'genre_musical' => "Schranz",
        'description' => "Hard techno sans concession.",
        'est_tete_affiche' => false,
        'membres' => ["Arthur Kerviel"],
        'discographie' => [
            [
                'titreAlbum' => "Schranz Fury", 'anneeSortie' => 2023,
                'tracks' => [['titre' => "160 BPM or Die", 'dureeSecondes' => 300, 'featuring' => []]]
            ]
        ]
    ]);

    // 5. ShAd (Tech House)
    $bulk->insert([
        '_id' => $idShad,
        'nom_scene_artiste' => "ShAd",
        'genre_musical' => "Tech House",
        'description' => "Groovy vibes all night long.",
        'est_tete_affiche' => false,
        'membres' => ["Julie Lemoine"],
        'discographie' => []
    ]);

    // 6. DJ php (Live Coding)
    $bulk->insert([
        '_id' => $idDjPhp,
        'nom_scene_artiste' => "DJ php",
        'genre_musical' => "Live Coding",
        'description' => "Il code la musique en temps réel. Syntax Error est son drop.",
        'est_tete_affiche' => false,
        'membres' => ["Bob Martin", "Alice Durant"],
        'discographie' => [
            [
                'titreAlbum' => "While(true) { Dance(); }", 'anneeSortie' => 2025,
                'tracks' => [['titre' => "Segmentation Fault", 'dureeSecondes' => 404, 'featuring' => []]]
            ]
        ]
    ]);

    $manager->executeBulkWrite("$dbName.artistes", $bulk);
    echo "✅ 6 Artistes créés.\n";


    // =========================================================
    // 2. CRÉATION DES CONCERTS
    // =========================================================
    $bulk = new MongoDB\Driver\BulkWrite;
    $now = time();

    // -- TOKAMAIN --
    // Gims (18h-19h30)
    $d1 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 18:00') * 1000);
    $f1 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 19:30') * 1000);
    $bulk->insert(['heure_debut' => $d1, 'heure_fin' => $f1, 'artiste_id' => $idGims, 'scene_id' => $idTokaMain]);

    // David Guetta (20h-21h30)
    $d2 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 20:00') * 1000);
    $f2 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 21:30') * 1000);
    $bulk->insert(['heure_debut' => $d2, 'heure_fin' => $f2, 'artiste_id' => $idGuetta, 'scene_id' => $idTokaMain]);

    // Daft Punk (22h-23h30)
    $d3 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 22:00') * 1000);
    $f3 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 23:30') * 1000);
    $bulk->insert(['heure_debut' => $d3, 'heure_fin' => $f3, 'artiste_id' => $idDaftPunk, 'scene_id' => $idTokaMain]);

    // -- TOKACTRLB --
    // DJ php (19h-20h)
    $d4 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 19:00') * 1000);
    $f4 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 20:00') * 1000);
    $bulk->insert(['heure_debut' => $d4, 'heure_fin' => $f4, 'artiste_id' => $idDjPhp, 'scene_id' => $idTokaCtrl]);

    // ShAd (20h30-22h)
    $d5 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 20:30') * 1000);
    $f5 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 22:00') * 1000);
    $bulk->insert(['heure_debut' => $d5, 'heure_fin' => $f5, 'artiste_id' => $idShad, 'scene_id' => $idTokaCtrl]);

    // Luvbox (22h30-00h)
    $d6 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 22:30') * 1000);
    $f6 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 23:59') * 1000);
    $bulk->insert(['heure_debut' => $d6, 'heure_fin' => $f6, 'artiste_id' => $idLuvbox, 'scene_id' => $idTokaCtrl]);

    $manager->executeBulkWrite("$dbName.concerts", $bulk);
    echo "✅ 6 Concerts programmés.\n";


    // =========================================================
    // 3. CRÉATION DES FESTIVALIERS
    // =========================================================
    $bulk = new MongoDB\Driver\BulkWrite;

    // 1. Loris Rouby (Né le 9 juin 2006) - Pass VIP - VALIDE
    $bulk->insert([
        'nom_complet' => "Loris Rouby",
        'email' => "loris.rouby@test.com",
        'date_naissance' => new MongoDB\BSON\UTCDateTime(strtotime('2006-06-09') * 1000),
        'adresse' => ['rue' => "10 Rue du Code", 'ville' => "Lyon", 'code_postal' => "69000"],
        'billets_achetes' => [
            [
                'type_billet' => "Pass VIP",
                'date_achat' => new MongoDB\BSON\UTCDateTime(),
                'prix_paye' => 250.00,
                'qr_code_data' => [
                    'hash_billet' => bin2hex(random_bytes(8)),
                    'validation' => ['est_valide' => true, 'date_scan' => null]
                ]
            ]
        ]
    ]);

    // 2. Charlotte Encarnacao (Née le 13 juin 2006) - Pass 3 Jours - INVALIDE (Scanné)
    $bulk->insert([
        'nom_complet' => "Charlotte Encarnacao",
        'email' => "charlotte.e@test.com",
        'date_naissance' => new MongoDB\BSON\UTCDateTime(strtotime('2006-06-13') * 1000),
        'adresse' => ['rue' => "25 Avenue de la Dance", 'ville' => "Paris", 'code_postal' => "75001"],
        'billets_achetes' => [
            [
                'type_billet' => "Pass 3 Jours",
                'date_achat' => new MongoDB\BSON\UTCDateTime(),
                'prix_paye' => 120.00,
                'qr_code_data' => [
                    'hash_billet' => bin2hex(random_bytes(8)),
                    'validation' => [
                        'est_valide' => false, // Déjà rentrée !
                        'date_scan' => new MongoDB\BSON\UTCDateTime()
                    ]
                ]
            ]
        ]
    ]);

    // 3. Inconnu 1 (Lucas) - Pass 1 Jour - VALIDE
    $bulk->insert([
        'nom_complet' => "Lucas Dupont",
        'email' => "lucas.d@test.com",
        'date_naissance' => new MongoDB\BSON\UTCDateTime(strtotime('1998-05-20') * 1000),
        'adresse' => ['rue' => "Impasse des Lilas", 'ville' => "Bordeaux", 'code_postal' => "33000"],
        'billets_achetes' => [
            [
                'type_billet' => "Pass 1 Jour",
                'date_achat' => new MongoDB\BSON\UTCDateTime(),
                'prix_paye' => 50.00,
                'qr_code_data' => [
                    'hash_billet' => bin2hex(random_bytes(8)),
                    'validation' => ['est_valide' => true, 'date_scan' => null]
                ]
            ]
        ]
    ]);

    // 4. Inconnu 2 (Sophie) - Pass 2 Jours - VALIDE
    $bulk->insert([
        'nom_complet' => "Sophie Martin",
        'email' => "sophie.m@test.com",
        'date_naissance' => new MongoDB\BSON\UTCDateTime(strtotime('2001-12-12') * 1000),
        'adresse' => ['rue' => "Boulevard Gambetta", 'ville' => "Nice", 'code_postal' => "06000"],
        'billets_achetes' => [
            [
                'type_billet' => "Pass 2 Jours",
                'date_achat' => new MongoDB\BSON\UTCDateTime(),
                'prix_paye' => 90.00,
                'qr_code_data' => [
                    'hash_billet' => bin2hex(random_bytes(8)),
                    'validation' => ['est_valide' => true, 'date_scan' => null]
                ]
            ]
        ]
    ]);

    // 5. Inconnu 3 (Arthur) - Pass VIP - INVALIDE (Fraude ou déjà scanné)
    $bulk->insert([
        'nom_complet' => "Arthur Rimbaud",
        'email' => "poete@test.com",
        'date_naissance' => new MongoDB\BSON\UTCDateTime(strtotime('1854-10-20') * 1000), // Un vampire ?
        'adresse' => ['rue' => "Rue Ivre", 'ville' => "Charleville", 'code_postal' => "08000"],
        'billets_achetes' => [
            [
                'type_billet' => "Pass VIP",
                'date_achat' => new MongoDB\BSON\UTCDateTime(),
                'prix_paye' => 250.00,
                'qr_code_data' => [
                    'hash_billet' => bin2hex(random_bytes(8)),
                    'validation' => [
                        'est_valide' => false, 
                        'date_scan' => new MongoDB\BSON\UTCDateTime(strtotime('-2 hours') * 1000)
                    ]
                ]
            ]
        ]
    ]);

    $manager->executeBulkWrite("$dbName.festivaliers", $bulk);
    echo "✅ 5 Festivaliers créés (Loris, Charlotte + 3 autres).\n";


    // =========================================================
    // 4. CRÉATION DES BÉNÉVOLES
    // =========================================================
    $bulk = new MongoDB\Driver\BulkWrite;
    
    $bulk->insert(['nom' => "Dupont", 'prenom' => "Jean", 'equipe' => "Bar", 'scene_assignee_id' => $idTokaMain]);
    $bulk->insert(['nom' => "Durand", 'prenom' => "Marie", 'equipe' => "Sécurité", 'scene_assignee_id' => $idTokaCtrl]);
    $bulk->insert(['nom' => "Curie", 'prenom' => "Pierre", 'equipe' => "Nettoyage", 'scene_assignee_id' => null]);

    $manager->executeBulkWrite("$dbName.benevoles", $bulk);
    echo "✅ 3 Bénévoles créés.\n";


    echo "</pre>";
    echo "<h2 style='color:green'>🎉 Jeu de données complet chargé avec succès !</h2>";
    echo "<a href='Pages-Admin/dashboard.php'>Voir le résultat sur le Dashboard</a>";

} catch (Exception $e) {
    die("❌ Erreur : " . $e->getMessage());
}
?>