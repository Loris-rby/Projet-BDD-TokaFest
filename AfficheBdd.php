<?php
// AfficheBDD.php
// Affiche tout le contenu de la base en format JSON brut

// En-tête pour que le navigateur sache que c'est du JSON
header('Content-Type: application/json; charset=utf-8');

require_once './Classes/Connexion.php';

try {
    // 1. Connexion Sécurisée via ton Singleton
    $connexion = Connexion::getInstance();
    $manager = $connexion->getManager();
    $dbName = $connexion->getDbName();

    // 2. Lister les collections de la base
    $command = new MongoDB\Driver\Command(["listCollections" => 1]);
    $cursorCols = $manager->executeCommand($dbName, $command);

    $data = [];

    // 3. Boucle sur chaque collection trouvée
    foreach ($cursorCols as $col) {
        $colName = $col->name;

        // On ignore les fichiers système de Mongo
        if (strpos($colName, 'system.') === 0) continue;

        // 4. On récupère tous les documents de la collection
        $query = new MongoDB\Driver\Query([]);
        $cursorDocs = $manager->executeQuery("$dbName.$colName", $query);
        
        $documents = [];
        foreach ($cursorDocs as $doc) {
            // Conversion BSON -> PHP -> JSON -> PHP Array
            // Cette manipulation permet de transformer les ObjectId et UTCDateTime en format lisible
            $jsonDoc = MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($doc));
            $documents[] = json_decode($jsonDoc);
        }
        
        $data[$colName] = $documents;
    }

    // 5. Affichage final
    $response = [
        "info" => "Contenu complet de la base de données '$dbName'",
        "timestamp" => date('Y-m-d H:i:s'),
        "donnees" => $data
    ];

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // En cas d'erreur, on l'affiche en JSON aussi
    echo json_encode([
        "erreur" => true,
        "message" => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>