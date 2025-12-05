<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

$manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

$id = null;
$nom_artiste = ""; 
$genre = ""; 
$est_tete_affiche = false;
$pageTitle = "Ajouter un Artiste";

// --- 1. MODE MODIFICATION : Charger les données ---
if (isset($_GET['id'])) {
    try {
        $id = new MongoDB\BSON\ObjectId($_GET['id']);
        $cursor = $manager->executeQuery('tokafest_db.artistes', new MongoDB\Driver\Query(['_id' => $id]));
        $artiste = current($cursor->toArray());
        
        if ($artiste) {
            $nom_artiste = $artiste->nom_scene_artiste;
            $genre = $artiste->genre_musical;
            $est_tete_affiche = $artiste->est_tete_affiche ?? false;
            $pageTitle = "Modifier : " . $nom_artiste;
        }
    } catch(Exception $e) {}
}

// --- 2. TRAITEMENT DU FORMULAIRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $document = [
        'nom_scene_artiste' => $_POST['nom_scene_artiste'],
        'genre_musical' => $_POST['genre_musical'],
        'est_tete_affiche' => isset($_POST['est_tete_affiche']), // Case à cocher
        // On garde la structure existante pour les membres/discographie vide pour l'instant
        'membres' => [], 
        'discographie' => []
    ];

    $bulk = new MongoDB\Driver\BulkWrite;
    if ($id) {
        // En modification, on utilise $set pour ne pas écraser membres/disco si ils existent déjà
        $bulk->update(['_id' => $id], ['$set' => [
            'nom_scene_artiste' => $_POST['nom_scene_artiste'],
            'genre_musical' => $_POST['genre_musical'],
            'est_tete_affiche' => isset($_POST['est_tete_affiche'])
        ]]);
    } else {
        // En création
        $bulk->insert($document);
    }

    $manager->executeBulkWrite('tokafest_db.artistes', $bulk);
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="admin-nav">
        <a href="dashboard.php" class="brand-title">TokaFest <span class="brand-subtitle">| Artistes</span></a>
        <div class="user-info">Admin: <?php echo $_SESSION['admin_name']; ?></div>
    </nav>

    <div class="admin-container">
        <div class="admin-card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header">
                <h2><?php echo $pageTitle; ?></h2>
            </div>

            <form method="post">
                <div class="form-group">
                    <label class="form-label">Nom de l'artiste / Groupe</label>
                    <input type="text" name="nom_scene_artiste" class="form-input" value="<?php echo htmlspecialchars($nom_artiste); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Genre Musical</label>
                    <input type="text" name="genre_musical" class="form-input" value="<?php echo htmlspecialchars($genre); ?>" placeholder="Ex: Electro-Rock, Jazz, Techno..." required>
                </div>

                <div class="form-group" style="margin: 20px 0;">
                    <label style="color: #F1C40F; display: flex; align-items: center; cursor: pointer; font-weight: bold;">
                        <input type="checkbox" name="est_tete_affiche" style="transform: scale(1.5); margin-right: 15px;" <?php if($est_tete_affiche) echo 'checked'; ?>>
                        ⭐ Est une Tête d'affiche (Headliner)
                    </label>
                </div>

                <button type="submit" class="btn-login" style="margin-top: 20px;">Enregistrer l'Artiste</button>
                <a href="dashboard.php" style="display:block; text-align:center; margin-top:15px; color:#888; text-decoration:none;">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>