<?php
session_start();

require_once 'Classes/Connexion.php';
require_once 'Classes/Manager.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}


echo "<h1>Initialisation de la Base de Données TokaFest</h1>";
echo "<pre>";

// ---------------------------------------------------------
// 2. CRÉATION DES SCÈNES (On garde les IDs pour les concerts)
// ---------------------------------------------------------
$idSceneMain = new MongoDB\BSON\ObjectId();
$idSceneElectro = new MongoDB\BSON\ObjectId();

$bulk = new MongoDB\Driver\BulkWrite;

$bulk->insert([
    '_id' => $idSceneMain,
    'nom_scene' => "Main Stage Horizon",
    'capacite_max' => 15000,
    'est_couverte' => false,
    'localisation_gps' => ['lat' => 45.1234, 'lon' => -0.5678]
]);

$bulk->insert([
    '_id' => $idSceneElectro,
    'nom_scene' => "Electro Dome",
    'capacite_max' => 5000,
    'est_couverte' => true,
    'localisation_gps' => ['lat' => 45.1240, 'lon' => -0.5680]
]);

$manager->executeBulkWrite("$dbName.scenes", $bulk);
echo "✅ Scènes créées.\n";

// ---------------------------------------------------------
// 3. CRÉATION DES ARTISTES (Avec Discographie Profondeur 4)
// ---------------------------------------------------------
$idArtisteRock = new MongoDB\BSON\ObjectId();
$idArtisteDJ = new MongoDB\BSON\ObjectId();

$bulk = new MongoDB\Driver\BulkWrite;

// Artiste 1 : Groupe de Rock (Tête d'affiche)
$bulk->insert([
    '_id' => $idArtisteRock,
    'nom_scene_artiste' => "The Mongo Rockers",
    'genre_musical' => "Rock Alternatif",
    'description' => "Le groupe légendaire qui casse des briques... et des bases de données.",
    'est_tete_affiche' => true,
    'membres' => ["Axel (Chant)", "Slash (Guitare)", "Duff (Basse)"], // Tableau simple
    'discographie' => [
        [
            'titreAlbum' => "Origins of NoSQL",
            'anneeSortie' => 2020,
            'tracks' => [
                [
                    'titre' => "Intro to Sharding",
                    'dureeSecondes' => 180,
                    'featuring' => []
                ],
                [
                    'titre' => "Cluster Crash",
                    'dureeSecondes' => 245,
                    'featuring' => [
                        ['nomFeat' => "MC Relational"],
                        ['nomFeat' => "Lil' Table"]
                    ]
                ]
            ]
        ],
        [
            'titreAlbum' => "High Availability",
            'anneeSortie' => 2023,
            'tracks' => [
                [
                    'titre' => "Replica Set Love",
                    'dureeSecondes' => 210,
                    'featuring' => []
                ]
            ]
        ]
    ]
]);

// Artiste 2 : DJ (Pas tête d'affiche)
$bulk->insert([
    '_id' => $idArtisteDJ,
    'nom_scene_artiste' => "DJ JSON",
    'genre_musical' => "Electro / House",
    'description' => "Il mixe les objets comme personne.",
    'est_tete_affiche' => false,
    'membres' => ["DJ JSON (Platines)"],
    'discographie' => [
        [
            'titreAlbum' => "Brackets & Braces",
            'anneeSortie' => 2024,
            'tracks' => [
                [
                    'titre' => "Parse Error",
                    'dureeSecondes' => 195,
                    'featuring' => [['nomFeat' => "Validat0r"]]
                ]
            ]
        ]
    ]
]);

$manager->executeBulkWrite("$dbName.artistes", $bulk);
echo "✅ Artistes créés (avec discographies complexes).\n";

// ---------------------------------------------------------
// 4. CRÉATION DES CONCERTS (Lien Artiste <-> Scène)
// ---------------------------------------------------------
$bulk = new MongoDB\Driver\BulkWrite;

// Concert 1 : The Mongo Rockers sur Main Stage
// Date : Demain à 20h00
$dateDebut1 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 20:00') * 1000);
$dateFin1   = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 21:30') * 1000);

$bulk->insert([
    'date_concert' => $dateDebut1, // Redondant mais pratique pour tri rapide
    'heure_debut' => $dateDebut1,
    'heure_fin' => $dateFin1,
    'artiste_id' => $idArtisteRock, // Jointure manuelle
    'scene_id' => $idSceneMain      // Jointure manuelle
]);

// Concert 2 : DJ JSON sur Electro Dome
// Date : Demain à 22h00
$dateDebut2 = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 22:00') * 1000);
$dateFin2   = new MongoDB\BSON\UTCDateTime(strtotime('+1 day 23:30') * 1000);

$bulk->insert([
    'date_concert' => $dateDebut2,
    'heure_debut' => $dateDebut2,
    'heure_fin' => $dateFin2,
    'artiste_id' => $idArtisteDJ,
    'scene_id' => $idSceneElectro
]);

$manager->executeBulkWrite("$dbName.concerts", $bulk);
echo "✅ Concerts programmés.\n";

// ---------------------------------------------------------
// 5. CRÉATION DES BÉNÉVOLES
// ---------------------------------------------------------
$bulk = new MongoDB\Driver\BulkWrite;

$bulk->insert([
    'nom' => "Martin",
    'prenom' => "Paul",
    'email' => "paul.martin@test.com",
    'age' => 25,
    'equipe' => "Sécurité",
    'scene_assignee_id' => $idSceneMain // Assigné à la Main Stage
]);

$bulk->insert([
    'nom' => "Bernard",
    'prenom' => "Sophie",
    'email' => "sophie.b@test.com",
    'age' => 30,
    'equipe' => "Bar",
    'scene_assignee_id' => null // Pas de scène spécifique (Volant)
]);

$manager->executeBulkWrite("$dbName.benevoles", $bulk);
echo "✅ Bénévoles ajoutés.\n";

// ---------------------------------------------------------
// 6. CRÉATION DES STANDS
// ---------------------------------------------------------
$bulk = new MongoDB\Driver\BulkWrite;

$bulk->insert([
    'nom_stand' => "Burger du Chef",
    'type_stand' => "Nourriture",
    'ouvert' => true,
    'proprietaire' => [
        'nom_proprioStand' => "Alain Ducasse",
        'num_proprioStand' => "0601020304"
    ]
]);

$bulk->insert([
    'nom_stand' => "T-Shirts Officiels",
    'type_stand' => "Merchandising",
    'ouvert' => true,
    'proprietaire' => [
        'nom_proprioStand' => "Sophie Mode",
        'num_proprioStand' => "0708091011"
    ]
]);

$manager->executeBulkWrite("$dbName.stands", $bulk);
echo "✅ Stands installés.\n";

// ---------------------------------------------------------
// 7. CRÉATION DES FESTIVALIERS (Données Complexes)
// ---------------------------------------------------------
$bulk = new MongoDB\Driver\BulkWrite;

$bulk->insert([
    'nom_complet' => "Jean Dupont",
    'email' => "jean.dupont@email.com",
    'date_naissance' => new MongoDB\BSON\UTCDateTime(strtotime('1995-05-15') * 1000),
    'adresse' => [
        'rue' => "12 Rue de la Paix",
        'ville' => "Paris",
        'code_postal' => "75001"
    ],
    'billets_achetes' => [
        [
            'type_billet' => "Pass 3 Jours",
            'date_achat' => new MongoDB\BSON\UTCDateTime(strtotime('-2 months') * 1000),
            'prix_paye' => 110.00,
            'qr_code_data' => [
                'hash_billet' => bin2hex(random_bytes(16)),
                'url_image_qr' => "/qr/fake_qr_1.png",
                'validation' => [
                    'est_valide' => true, // Billet valide
                    'date_scan' => null
                ]
            ]
        ]
    ]
]);

$bulk->insert([
    'nom_complet' => "Marie Curie",
    'email' => "marie.curie@science.com",
    'date_naissance' => new MongoDB\BSON\UTCDateTime(strtotime('1988-11-07') * 1000),
    'adresse' => [
        'rue' => "5 Avenue des Champs",
        'ville' => "Lyon",
        'code_postal' => "69002"
    ],
    'billets_achetes' => [
        [
            'type_billet' => "Pass 1 Jour",
            'date_achat' => new MongoDB\BSON\UTCDateTime(strtotime('-1 week') * 1000),
            'prix_paye' => 45.00,
            'qr_code_data' => [
                'hash_billet' => bin2hex(random_bytes(16)),
                'url_image_qr' => "/qr/fake_qr_2.png",
                'validation' => [
                    'est_valide' => false, // Billet déjà scanné
                    'date_scan' => new MongoDB\BSON\UTCDateTime(time() * 1000)
                ]
            ]
        ],
        [
            'type_billet' => "VIP Gold",
            'date_achat' => new MongoDB\BSON\UTCDateTime(strtotime('-1 day') * 1000),
            'prix_paye' => 150.00,
            'qr_code_data' => [
                'hash_billet' => bin2hex(random_bytes(16)),
                'url_image_qr' => "/qr/fake_qr_3.png",
                'validation' => [
                    'est_valide' => true,
                    'date_scan' => null
                ]
            ]
        ]
    ]
]);

$manager->executeBulkWrite("$dbName.festivaliers", $bulk);
echo "✅ Festivaliers enregistrés (avec billets).\n";

echo "</pre>";
echo "<h2 style='color:green'>Terminé ! La base de données est prête.</h2>";
echo "<a href='Pages-Admin/dashboard.php'>Aller au Dashboard</a>";
?>