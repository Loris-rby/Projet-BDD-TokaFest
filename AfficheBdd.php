<?php
// On force le navigateur à comprendre que c'est du texte brut (JSON)
header('Content-Type: application/json; charset=utf-8');

// Connexion (Exactement comme ton script qui marche)
try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    $dbName = 'tokafest_db';
} catch (Exception $e) {
    die(json_encode(["erreur" => "Echec connexion : " . $e->getMessage()]));
}

// Liste de tes collections
$collections = ['artistes', 'festivaliers', 'scenes', 'benevoles', 'concerts', 'stands'];
$data = [];

// On parcourt chaque collection pour tout aspirer
foreach ($collections as $col) {
    try {
        $query = new MongoDB\Driver\Query([]); 
        $cursor = $manager->executeQuery("$dbName.$col", $query);
        
        // On transforme les objets bizarres de MongoDB en tableaux simples
        $documents = [];
        foreach ($cursor as $doc) {
            // Petite astuce pour rendre les _id ($oid) et les dates ($date) lisibles
            $docJson = MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($doc));
            $documents[] = json_decode($docJson);
        }
        
        $data[$col] = $documents;
        
    } catch (Exception $e) {
        $data[$col] = ["erreur" => $e->getMessage()];
    }
}

// Affichage final
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>