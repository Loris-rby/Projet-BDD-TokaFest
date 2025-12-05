<?php

// 1. Connexion au serveur MongoDB
$manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
$dbName = 'tokafest_db';

echo "Connexion à la base de données '$dbName'...\n";

// --- Utilitaires ---

/**
 * Fonction pour convertir une date string en MongoDB\BSON\UTCDateTime
 * MongoDB stocke les dates en millisecondes depuis l'époque Unix.
 */
function getMongoDate($dateString) {
    // strtotime renvoie des secondes, on multiplie par 1000 pour les millisecondes
    return new MongoDB\BSON\UTCDateTime(strtotime($dateString) * 1000);
}

/**
 * Fonction pour supprimer une collection proprement (équivalent de db.collection.drop())
 */
function dropCollection($manager, $db, $collection) {
    try {
        $command = new MongoDB\Driver\Command(["drop" => $collection]);
        $manager->executeCommand($db, $command);
        echo " - Collection '$collection' supprimée.\n";
    } catch (MongoDB\Driver\Exception\Exception $e) {
        // On ignore l'erreur si la collection n'existe pas déjà (ns not found)
    }
}

// --- 2. Nettoyage (Suppression des collections existantes) ---
echo "Suppression des collections existantes...\n";
$collections = ['artistes', 'festivaliers', 'scenes', 'benevoles', 'concerts', 'stands'];
foreach ($collections as $col) {
    dropCollection($manager, $dbName, $col);
}

// --- 3. Création des IDs pour les références (Jointures) ---
// En PHP, on instancie explicitement l'objet ObjectId
$artisteId1 = new MongoDB\BSON\ObjectId();
$artisteId2 = new MongoDB\BSON\ObjectId();
$sceneId1   = new MongoDB\BSON\ObjectId();
$sceneId2   = new MongoDB\BSON\ObjectId();

// --- 4. Insertion dans les Collections ---

// A. Collection ARTISTES
echo "Insertion dans 'artistes'...\n";
$bulk = new MongoDB\Driver\BulkWrite;

$bulk->insert([
    '_id' => $artisteId1,
    'nom_scene_artiste' => "The Digital Nomads",
    'genre_musical' => "Electro-Rock",
    'est_tete_affiche' => true,
    'membres' => [
        ['nom_complet' => "Alice", 'instrument' => "Chant"],
        ['nom_complet' => "Bob", 'instrument' => "Guitare/Synth"]
    ],
    'discographie' => [
        [
            'titre_album' => "Cybernetic Dawn",
            'annee_sortie' => 2022,
            'pistes' => [
                [
                    'titre_piste' => "Reboot My Heart",
                    'duree_secondes' => 245,
                    'featuring' => [
                        ['nom_artiste_invite' => "DJ Code"]
                    ]
                ],
                [
                    'titre_piste' => "Binary Sunset",
                    'duree_secondes' => 310,
                    'featuring' => []
                ]
            ]
        ]
    ]
]);

$bulk->insert([
    '_id' => $artisteId2,
    'nom_scene_artiste' => "Acoustic Echoes",
    'genre_musical' => "Folk",
    'est_tete_affiche' => false,
    'membres' => [
        ['nom_complet' => "Clara", 'instrument' => "Chant/Guitare"]
    ],
    'discographie' => []
]);

$manager->executeBulkWrite("$dbName.artistes", $bulk);


// B. Collection FESTIVALIERS
echo "Insertion dans 'festivaliers'...\n";
$bulk = new MongoDB\Driver\BulkWrite;

$bulk->insert([
    'nom_complet' => "Jean Dupont",
    'email' => "jean.dupont@email.com",
    'date_naissance' => getMongoDate("1995-03-15T00:00:00Z"),
    'adresse' => [
        'rue' => "10 rue de la République",
        'ville' => "Paris",
        'code_postal' => "75001"
    ],
    'billets_achetes' => [
        [
            'type_billet' => "Pass 3 Jours",
            'date_achat' => getMongoDate("2024-05-10T14:30:00Z"),
            'prix_paye' => 120.50,
            'qr_code_data' => [
                'hash_billet' => "a1b2c3d4e5f6...",
                'url_image_qr' => "/qr/a1b2c3.png",
                'validation' => [
                    'est_valide' => true,
                    'date_scan' => null
                ]
            ]
        ]
    ]
]);

$manager->executeBulkWrite("$dbName.festivaliers", $bulk);


// C. Collection SCENES
echo "Insertion dans 'scenes'...\n";
$bulk = new MongoDB\Driver\BulkWrite;

$bulk->insert([
    '_id' => $sceneId1,
    'nom_scene' => "Scène Principale 'Volcan'",
    'capacite_max' => 15000,
    'est_couverte' => false,
    'coordonnees_gps' => [
        'latitude' => 45.123,
        'longitude' => -1.456
    ]
]);

$bulk->insert([
    '_id' => $sceneId2,
    'nom_scene' => "La Tente 'Oasis'",
    'capacite_max' => 3000,
    'est_couverte' => true,
    'coordonnees_gps' => [
        'latitude' => 45.125,
        'longitude' => -1.458
    ]
]);

$manager->executeBulkWrite("$dbName.scenes", $bulk);


// D. Collection BENEVOLES
echo "Insertion dans 'benevoles'...\n";
$bulk = new MongoDB\Driver\BulkWrite;

$bulk->insert([
    'nom' => "Martin",
    'prenom' => "Lucie",
    'telephone' => "0612345678",
    'equipe' => "Accueil",
    'scene_assignee_id' => $sceneId1, // Utilisation de la variable ID générée plus haut
    'horaires_shifts' => [
        [
            'jour' => getMongoDate("2024-07-20T00:00:00Z"),
            'heure_debut' => "14:00",
            'heure_fin' => "20:00"
        ],
        [
            'jour' => getMongoDate("2024-07-21T00:00:00Z"),
            'heure_debut' => "16:00",
            'heure_fin' => "22:00"
        ]
    ]
]);

$manager->executeBulkWrite("$dbName.benevoles", $bulk);


// E. Collection CONCERTS
echo "Insertion dans 'concerts'...\n";
$bulk = new MongoDB\Driver\BulkWrite;

$bulk->insert([
    'artiste_id' => $artisteId1, // Référence
    'scene_id' => $sceneId1,     // Référence
    'heure_debut' => getMongoDate("2024-07-20T22:00:00Z"),
    'heure_fin' => getMongoDate("2024-07-20T23:30:00Z"),
    'annule' => false
]);

$bulk->insert([
    'artiste_id' => $artisteId2, // Référence
    'scene_id' => $sceneId2,     // Référence
    'heure_debut' => getMongoDate("2024-07-20T19:00:00Z"),
    'heure_fin' => getMongoDate("2024-07-20T20:00:00Z"),
    'annule' => false
]);

$manager->executeBulkWrite("$dbName.concerts", $bulk);


// F. Collection STANDS
echo "Insertion dans 'stands'...\n";
$bulk = new MongoDB\Driver\BulkWrite;

$bulk->insert([
    'nom_stand' => "Le Camion Fumeur",
    'type_stand' => "Food",
    'proprietaire' => [
        'nom_contact' => "Paul Bernard",
        'email' => "contact@camionfumeur.com"
    ],
    'ouvert' => true
]);

$bulk->insert([
    'nom_stand' => "Toka-Merch",
    'type_stand' => "Merch",
    'proprietaire' => [
        'nom_contact' => "TokaFest Asso",
        'email' => "merch@tokafest.com"
    ],
    'ouvert' => true
]);

$manager->executeBulkWrite("$dbName.stands", $bulk);

echo "\n******************************************************************\n";
echo "JEU DE DONNÉES CRÉÉ AVEC SUCCÈS DANS LA BASE '$dbName' !\n";
echo "******************************************************************\n";

// Vérification rapide : Lister un artiste pour prouver que ça a marché
$query = new MongoDB\Driver\Query([], ['limit' => 1]);
$cursor = $manager->executeQuery("$dbName.artistes", $query);

echo "\nVérification (Premier artiste trouvé) :\n";
foreach ($cursor as $doc) {
    print_r($doc);
}

?>