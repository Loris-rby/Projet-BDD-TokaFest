<?php
// NettoyerDonnees.php

// On utilise ta classe de connexion pour être sûr d'avoir les droits
require_once './Classes/Connexion.php';

try {
    $connexion = Connexion::getInstance();
    $manager = $connexion->getManager();
    $dbName = $connexion->getDbName();

    echo "<h1>🧹 Nettoyage Spécifique (Artistes & Festivaliers)</h1>";
    echo "<pre>";

    // =========================================================
    // 1. SUPPRESSION CIBLÉE
    // =========================================================
    
    // On vide Festivaliers
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->delete([]); 
    $manager->executeBulkWrite("$dbName.festivaliers", $bulk);
    echo "🗑️  Collection 'festivaliers' vidée.\n";

    // On vide Artistes
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->delete([]); 
    $manager->executeBulkWrite("$dbName.artistes", $bulk);
    echo "🗑️  Collection 'artistes' vidée.\n";

    // IMPORTANT : On vide Concerts aussi
    // Pourquoi ? Parce qu'un concert pointe vers un artiste. 
    // Si on supprime les artistes, les concerts deviennent invalides.
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->delete([]); 
    $manager->executeBulkWrite("$dbName.concerts", $bulk);
    echo "🗑️  Collection 'concerts' vidée (logique car artistes supprimés).\n";

    // =========================================================
    // 2. RÉINITIALISATION DES SCÈNES (TokaMain & TokaCtrlB)
    // =========================================================
    
    // D'abord on vide les anciennes scènes pour éviter les doublons
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->delete([]); 
    $manager->executeBulkWrite("$dbName.scenes", $bulk);
    echo "🗑️  Anciennes scènes supprimées.\n";

    // Ensuite on crée tes deux scènes spécifiques
    $bulk = new MongoDB\Driver\BulkWrite;
    
    // Scène 1 : TokaMain
    $bulk->insert([
        'nom_scene' => "TokaMain",
        'capacite_max' => 1500,
        'est_couverte' => false
    ]);

    // Scène 2 : TokaCtrlB
    $bulk->insert([
        'nom_scene' => "TokaCtrlB",
        'capacite_max' => 1800,
        'est_couverte' => false
    ]);

    $manager->executeBulkWrite("$dbName.scenes", $bulk);
    echo "✅ Scènes 'TokaMain' et 'TokaCtrlB' créées.\n";

    // =========================================================
    // 3. CONFIRMATION ADMIN
    // =========================================================
    echo "---------------------------------------------------------\n";
    echo "🔒 La collection 'admins' N'A PAS ÉTÉ TOUCHÉE.\n";
    echo "   Tu peux toujours te connecter avec ton compte habituel.\n";
    
    echo "</pre>";
    echo "<h2 style='color:green'>Prêt ! Tu peux remplir le reste via le site.</h2>";
    echo "<a href='Pages-Admin/login.php'>Aller au Dashboard</a>";

} catch (Exception $e) {
    die("❌ Erreur : " . $e->getMessage());
}
?>