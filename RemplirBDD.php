<?php
// RemplirBDD.php

// 1. ON INCLUT TA CLASSE QUI CONNAIT LE MOT DE PASSE
// Si ce fichier est à la racine du site, le chemin est ./Classes/Connexion.php
require_once './Classes/Connexion.php';

try {
    // 2. ON RÉCUPÈRE LA CONNEXION DÉJÀ CONFIGURÉE (avec adminBDD / admin123)
    $connexion = Connexion::getInstance();
    $manager = $connexion->getManager();
    $dbName = $connexion->getDbName(); // tokafest_db

    echo "<h1>Initialisation de la Base de Données TokaFest</h1>";
    echo "<pre>";
    echo "Connexion réussie via l'utilisateur : adminBDD<br><br>";

    // =========================================================
    // 3. NETTOYAGE TOTAL
    // =========================================================
    $collections = ['admins', 'artistes', 'scenes', 'concerts', 'benevoles', 'stands', 'festivaliers'];

    foreach ($collections as $col) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->delete([]); 
        $manager->executeBulkWrite("$dbName.$col", $bulk);
        echo "Collection '$col' vidée.\n";
    }
    echo "---------------------------------------------------------\n";

    // =========================================================
    // 4. CRÉATION DU SUPER ADMIN (Pour le Login)
    // =========================================================
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert([
        'username' => 'admin',
        'password' => password_hash('admin', PASSWORD_DEFAULT) // Hash indispensable pour password_verify
    ]);
    $manager->executeBulkWrite("$dbName.admins", $bulk);
    echo "✅ Admin site créé (User: admin / Pass: admin)\n";

    // =========================================================
    // 5. CRÉATION DES SCÈNES
    // =========================================================
    $idSceneMain = new MongoDB\BSON\ObjectId();
    $idSceneElectro = new MongoDB\BSON\ObjectId();

    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert([
        '_id' => $idSceneMain,
        'nom_scene' => "Main Stage Horizon",
        'capacite_max' => 15000,
        'est_couverte' => false
    ]);
    $bulk->insert([
        '_id' => $idSceneElectro,
        'nom_scene' => "Electro Dome",
        'capacite_max' => 5000,
        'est_couverte' => true
    ]);
    $manager->executeBulkWrite("$dbName.scenes", $bulk);
    echo "✅ Scènes créées.\n";

    // =========================================================
    // 6. CRÉATION DES ARTISTES
    // =========================================================
    $idArtisteRock = new MongoDB\BSON\ObjectId();
    $idDavidGuetta = new MongoDB\BSON\ObjectId();

    $bulk = new MongoDB\Driver\BulkWrite;

    // Artiste 1 : Mongo Rockers
    $bulk->insert([
        '_id' => $idArtisteRock,
        'nom_scene_artiste' => "The Mongo Rockers",
        'genre_musical' => "Rock Alternatif",
        'description' => "Le groupe légendaire qui casse des briques.",
        'est_tete_affiche' => false,
        'membres' => ["Axel (Chant)", "Slash (Guitare)", "Duff (Basse)"],
        'discographie' => [
            [
                'titreAlbum' => "Origins of NoSQL",
                'anneeSortie' => 2020,
                'tracks' => [
                    ['titre' => "Intro to Sharding", 'dureeSecondes' => 180, 'featuring' => []],
                    ['titre' => "Cluster Crash", 'dureeSecondes' => 245, 'featuring' => [['nomFeat' => "MC Relational"]]]
                ]
            ]
        ]
    ]);

    // Artiste 2 : David Guetta
    $bulk->insert([
        '_id' => $idDavidGuetta,
        'nom_scene_artiste' => "David Guetta",
        'genre_musical' => "EDM / Dance-Pop",
        'description' => "DJ, compositeur et producteur français.",
        'est_tete_affiche' => true,
        'membres' => ["David Guetta (DJ / Producteur)"],
        'discographie' => [
            [
                'titreAlbum' => "Nothing but the Beat",
                'anneeSortie' => 2011,
                'tracks' => [
                    ['titre' => "Titanium", 'dureeSecondes' => 245, 'featuring' => [['nomFeat' => "Sia"]]],
                    ['titre' => "Turn Me On", 'dureeSecondes' => 199, 'featuring' => [['nomFeat' => "Nicki Minaj"]]]
                ]
            ],
            [
                'titreAlbum' => "One Love",
                'anneeSortie' => 2009,
                'tracks' => [
                    ['titre' => "Sexy Bitch", 'dureeSecondes' => 193, 'featuring' => [['nomFeat' => "Akon"]]]
                ]
            ]
        ]
    ]);

    $manager->executeBulkWrite("$dbName.artistes", $bulk);
    echo "✅ Artistes créés.\n";

    // =========================================================
    // 7. CRÉATION DES CONCERTS
    // =========================================================
    $bulk = new MongoDB\Driver\BulkWrite;

    // David Guetta sur Main Stage
    $dateDebut1 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 22:00') * 1000);
    $dateFin1   = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 23:30') * 1000);
    $bulk->insert([
        'date_concert' => $dateDebut1,
        'heure_debut' => $dateDebut1,
        'heure_fin' => $dateFin1,
        'artiste_id' => $idDavidGuetta,
        'scene_id' => $idSceneMain
    ]);

    // Rockers sur Electro
    $dateDebut2 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 20:00') * 1000);
    $dateFin2   = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 21:30') * 1000);
    $bulk->insert([
        'date_concert' => $dateDebut2,
        'heure_debut' => $dateDebut2,
        'heure_fin' => $dateFin2,
        'artiste_id' => $idArtisteRock,
        'scene_id' => $idSceneElectro
    ]);

    $manager->executeBulkWrite("$dbName.concerts", $bulk);
    echo "✅ Concerts programmés.\n";

    // =========================================================
    // 8. AUTRES (Bénévoles, Stands, Festivaliers)
    // =========================================================
    
    // Bénévoles
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert(['nom' => "Martin", 'prenom' => "Paul", 'equipe' => "Sécurité", 'scene_assignee_id' => $idSceneMain]);
    $manager->executeBulkWrite("$dbName.benevoles", $bulk);

    // Stands
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert([
        'nom_stand' => "Frites Belges", 'type_stand' => "Nourriture", 'ouvert' => true,
        'proprietaire' => ['nom_proprioStand' => "Philippe Etchebest", 'num_proprioStand' => "0601020304"]
    ]);
    $manager->executeBulkWrite("$dbName.stands", $bulk);

    // Festivaliers
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert([
        'nom_complet' => "Emma Watson",
        'email' => "emma@hollywood.com",
        'date_naissance' => new MongoDB\BSON\UTCDateTime(strtotime('1990-04-15') * 1000),
        'adresse' => ['rue' => "Beverly Hills", 'ville' => "Los Angeles", 'code_postal' => "90210"],
        'billets_achetes' => [
            [
                'type_billet' => "VIP Gold",
                'date_achat' => new MongoDB\BSON\UTCDateTime(),
                'prix_paye' => 150.00,
                'qr_code_data' => [
                    'hash_billet' => bin2hex(random_bytes(8)),
                    'url_image_qr' => "#",
                    'validation' => ['est_valide' => true, 'date_scan' => null]
                ]
            ]
        ]
    ]);
    $manager->executeBulkWrite("$dbName.festivaliers", $bulk);
    echo "✅ Bénévoles, Stands et Festivaliers ajoutés.\n";

    echo "</pre>";
    echo "<h2 style='color:green'>Succès ! La base a été remplie avec la connexion sécurisée.</h2>";
    echo "<a href='Pages-Admin/login.php'>Aller à la connexion</a>";

} catch (Exception $e) {
    die("❌ Erreur : " . $e->getMessage());
}
?>