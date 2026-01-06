<?php
session_start();

// 1. Chargement des classes
require_once '../Classes/Connexion.php';
require_once '../Classes/Manager.php';
require_once '../Classes/ConcertManager.php';
require_once '../Classes/ArtisteManager.php';
require_once '../Classes/SceneManager.php';

// Sécurité
if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: login.php"); 
    exit; 
}

// 2. Instanciation des Managers
$concertManager = new ConcertManager();
$artisteManager = new ArtisteManager();
$sceneManager   = new SceneManager();

// 3. Récupération des listes pour les menus déroulants
// On trie pour que l'affichage soit alphabétique
$artistes = $artisteManager->findAll(['nom_scene_artiste' => 1]);
$scenes   = $sceneManager->findAll(['nom_scene' => 1]);

$id = null;
$artiste_id = ""; 
$scene_id = ""; 
$heure_debut = ""; 
$heure_fin = "";
$pageTitle = "Programmer un Concert";

// --- MODE MODIFICATION (Récupération des données) ---
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Appel via le Manager
    $doc = $concertManager->findById($id);
    
    if ($doc) {
        // On convertit les ObjectId en string pour les pré-sélectionner dans le HTML
        $artiste_id = (string)$doc->artiste_id;
        $scene_id   = (string)$doc->scene_id;
        
        // Formatage Date MongoDB -> HTML input datetime-local
        $heure_debut = $doc->heure_debut->toDateTime()->format('Y-m-d\TH:i');
        $heure_fin   = $doc->heure_fin->toDateTime()->format('Y-m-d\TH:i');
        
        $pageTitle = "Modifier le concert";
    }
}

// --- TRAITEMENT DU FORMULAIRE (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Conversion Date HTML -> MongoDB UTCDateTime (Millisecondes)
    $start = new MongoDB\BSON\UTCDateTime(strtotime($_POST['heure_debut']) * 1000);
    $end   = new MongoDB\BSON\UTCDateTime(strtotime($_POST['heure_fin']) * 1000);

    // Préparation des données
    $data = [
        // Important : On cast en ObjectId pour que la jointure fonctionne
        'artiste_id'  => new MongoDB\BSON\ObjectId($_POST['artiste_id']),
        'scene_id'    => new MongoDB\BSON\ObjectId($_POST['scene_id']),
        'heure_debut' => $start,
        'heure_fin'   => $end,
        'annule'      => false
    ];

    if ($id) {
        $concertManager->update($id, $data);
    } else {
        $concertManager->create($data);
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
        <a href="dashboard.php" class="brand-title">TokaFest <span class="brand-subtitle">| Programmation</span></a>
        <div class="user-info">Admin: <?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin'; ?></div>
    </nav>
    <div class="admin-container">
        <div class="admin-card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header"><h2><?php echo $pageTitle; ?></h2></div>
            <form method="post">
                
                <div class="form-group">
                    <label class="form-label">Artiste</label>
                    <select name="artiste_id" class="form-input" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach($artistes as $a): ?>
                            <option value="<?php echo $a->_id; ?>" <?php if((string)$a->_id == $artiste_id) echo 'selected'; ?>>
                                <?php echo $a->nom_scene_artiste; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Scène</label>
                    <select name="scene_id" class="form-input" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach($scenes as $s): ?>
                            <option value="<?php echo $s->_id; ?>" <?php if((string)$s->_id == $scene_id) echo 'selected'; ?>>
                                <?php echo $s->nom_scene; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display:flex; gap:20px;">
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Début</label>
                        <input type="datetime-local" name="heure_debut" class="form-input" value="<?php echo $heure_debut; ?>" required>
                    </div>
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Fin</label>
                        <input type="datetime-local" name="heure_fin" class="form-input" value="<?php echo $heure_fin; ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn-login" style="margin-top:20px;">Valider</button>
                <a href="dashboard.php" style="display:block; text-align:center; margin-top:15px; color:#888; text-decoration:none;">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>