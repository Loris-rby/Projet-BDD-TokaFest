<?php
session_start();

require_once '../Classes/Connexion.php';
require_once '../Classes/Manager.php';
require_once '../Classes/StandManager.php';

if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: login.php"); 
    exit; 
}

$standManager = new StandManager();

$id = null;
$nom_stand = ""; 
$type_stand = ""; 
$ouvert = false; 

$nom_proprioStand = "";
$num_proprioStand = ""; 

$pageTitle = "Ajouter un Stand";

// Modification Stand
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $doc = $standManager->findById($id); 
    
    if ($doc) {
        $nom_stand = $doc->nom_stand;
        $type_stand = $doc->type_stand;
        $ouvert = $doc->ouvert ?? false;

        // On vérifie si l'objet existe avant d'accéder à ses propriétés
        if (isset($doc->proprietaire)) {
            $nom_proprioStand = $doc->proprietaire->nom_proprioStand ?? "";
            $num_proprioStand = $doc->proprietaire->num_proprioStand ?? "";
        }

        $pageTitle = "Modifier : " . $nom_stand;
    }
}

// Formulaire Ajout/Modif Stand
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = [
        'nom_stand'  => $_POST['nom_stand'],
        'type_stand' => $_POST['type_stand'],
        'ouvert'     => isset($_POST['ouvert']), 
        'proprietaire' => [
            'nom_proprioStand' => $_POST['nom_proprioStand'],
            'num_proprioStand' => $_POST['num_proprioStand']
        ]
    ];

    if ($id) {
        $standManager->update($id, $data);
    } else {
        $standManager->create($data);
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
        <a href="dashboard.php" class="brand-title">TokaFest <span class="brand-subtitle">| Stands</span></a>
        <div class="user-info">Admin: <?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin'; ?></div>
    </nav>
    <div class="admin-container">
        <div class="admin-card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header"><h2><?php echo $pageTitle; ?></h2></div>
            <form method="post">
                
                <div class="form-group">
                    <label class="form-label">Nom du stand</label>
                    <input type="text" name="nom_stand" class="form-input" value="<?php echo htmlspecialchars($nom_stand); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Type de stand</label>
                    <input type="text" name="type_stand" class="form-input" value="<?php echo htmlspecialchars($type_stand); ?>" required>
                </div>
                
                <div class="form-group" style="margin: 20px 0;">
                    <label style="color: #19a606ff; display: flex; align-items: center; cursor: pointer; font-weight: bold;">
                        <input type="checkbox" name="ouvert" style="transform: scale(1.5); margin-right: 15px;" <?php if($ouvert) echo 'checked'; ?>>
                        Ouvert
                    </label>
                </div>

                <div style="border-top: 1px solid #333; padding-top: 20px; margin-top: 20px;">
                    <h3 style="color: #7B61FF; font-size: 1em; margin-bottom: 15px;">Information Propriétaire</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Nom du propriétaire</label>
                        <input type="text" name="nom_proprioStand" class="form-input" value="<?php echo htmlspecialchars($nom_proprioStand); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Numéro du propriétaire</label>
                        <input type="text" name="num_proprioStand" class="form-input" value="<?php echo htmlspecialchars($num_proprioStand); ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn-login" style="margin-top: 20px;">Enregistrer</button>
                <a href="dashboard.php" style="display:block; text-align:center; margin-top:15px; color:#888; text-decoration:none;">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>