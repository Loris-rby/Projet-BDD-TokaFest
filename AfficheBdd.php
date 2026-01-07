<?php
header('Content-Type: application/json; charset=utf-8');

// Configuration
$dbName = 'tokafest_db'; // Vérifie bien que c'est le bon nom !

try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

    // 1. On demande à MongoDB la liste réelle des collections
    // Cela permet de voir si la base existe et contient des choses
    $command = new MongoDB\Driver\Command(["listCollections" => 1]);
    
    try {
        $cursorCols = $manager->executeCommand($dbName, $command);
    } catch (MongoDB\Driver\Exception\Exception $e) {
        // Si cette commande échoue, c'est souvent que la base n'existe pas
        die(json_encode(["erreur_critique" => "Impossible de lister les collections. La base '$dbName' existe-t-elle ?", "details" => $e->getMessage()]));
    }

    $data = [];
    $foundCollections = [];

    // 2. On parcourt les collections trouvées
    foreach ($cursorCols as $collectionInfo) {
        $colName = $collectionInfo->name;
        $foundCollections[] = $colName;

        // On ignore les collections système (comme system.indexes)
        if (strpos($colName, 'system.') === 0) continue;

        // 3. On récupère le contenu
        $query = new MongoDB\Driver\Query([]);
        $cursor = $manager->executeQuery("$dbName.$colName", $query);
        
        $documents = [];
        foreach ($cursor as $doc) {
            // Ta méthode de conversion est correcte
            $docJson = MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($doc));
            $documents[] = json_decode($docJson);
        }
        
        $data[$colName] = $documents;
    }

    // Ajout d'infos de debug pour t'aider
    $response = [
        "debug_info" => [
            "base_de_donnee_ciblee" => $dbName,
            "collections_trouvees_dans_mongo" => $foundCollections,
            "message" => empty($foundCollections) ? "Aucune collection trouvée. Erreur de nom de BDD ?" : "Collections chargées."
        ],
        "contenu" => $data
    ];

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(["erreur_generale" => $e->getMessage()]);
}
?>