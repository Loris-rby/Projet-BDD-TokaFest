<?php

// En-tête pour que le navigateur sache que c'est du JSON
header('Content-Type: application/json; charset=utf-8');

require_once './Classes/Connexion.php';

try {
    // Connexion Sécurisée via le Singleton
    $connexion = Connexion::getInstance();
    $manager = $connexion->getManager();
    $dbName = $connexion->getDbName();

    // Lister les collections de la base
    $command = new MongoDB\Driver\Command(["listCollections" => 1]);
    $cursorCols = $manager->executeCommand($dbName, $command);

    $data = [];

    // Boucle sur chaque collection trouvée
    foreach ($cursorCols as $col) {
        $colName = $col->name;

        // On ignore les fichiers système de Mongo
        if (strpos($colName, 'system.') === 0) continue;

        // On récupère tous les documents de la collection
        $query = new MongoDB\Driver\Query([]);
        $cursorDocs = $manager->executeQuery("$dbName.$colName", $query);
        
        $documents = [];
        foreach ($cursorDocs as $doc) {
            $jsonDoc = MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($doc));
            $documents[] = json_decode($jsonDoc);
        }
        
        $data[$colName] = $documents;
    }

    // Affichage final
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