<?php
session_start();

require_once '../Classes/Connexion.php';
require_once '../Classes/Manager.php';
require_once '../Classes/ArtisteManager.php';

if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: login.php"); 
    exit; 
}
$artisteManager = new ArtisteManager();

$id = null;
$nom_artiste = ""; 
$genre = ""; 
$est_tete_affiche = false;
$pageTitle = "Ajouter un Artiste";

// --- Modification---
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $doc = $artisteManager->findById($id);
    
    if ($doc) {
        $nom_artiste = $doc->nom_scene_artiste;
        $genre = $doc->genre_musical;
        $est_tete_affiche = $doc->est_tete_affiche ?? false;
        $pageTitle = "Modifier : " . $nom_artiste;
    }
}

// --- Nouvel artiste ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = [
        'nom_scene_artiste' => $_POST['nom_scene_artiste'],
        'genre_musical'     => $_POST['genre_musical'],
        'est_tete_affiche'  => isset($_POST['est_tete_affiche'])
    ];

    if ($id) {
        $artisteManager->update($id, $data);
    } else {
        $data['membres'] = [];
        $data['discographie'] = [];
        $artisteManager->create($data);
    }

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
        <div class="user-info">Admin: <?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin'; ?></div>
    </nav>
    <div class="admin-container">
        <div class="admin-card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header"><h2><?php echo $pageTitle; ?></h2></div>
            <form method="post">
                <div class="form-group">
                    <label class="form-label">Nom de l'artiste</label>
                    <input type="text" name="nom_scene_artiste" class="form-input" value="<?php echo htmlspecialchars($nom_artiste); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Genre Musical</label>
                    <input type="text" name="genre_musical" class="form-input" value="<?php echo htmlspecialchars($genre); ?>" required>
                </div>
                <div class="form-group" style="margin: 20px 0;">
                    <label style="color: #F1C40F; display: flex; align-items: center; cursor: pointer; font-weight: bold;">
                        <input type="checkbox" name="est_tete_affiche" style="transform: scale(1.5); margin-right: 15px;" <?php if($est_tete_affiche) echo 'checked'; ?>>
                        ⭐ Tête d'affiche
                    </label>
                </div>
                <button type="submit" class="btn-login">Enregistrer</button>
                <a href="dashboard.php" style="display:block; text-align:center; margin-top:15px; color:#888; text-decoration:none;">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>