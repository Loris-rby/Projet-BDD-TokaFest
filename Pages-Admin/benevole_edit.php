<?php
session_start();

require_once '../Classes/Connexion.php';
require_once '../Classes/Manager.php';
require_once '../Classes/BenevoleManager.php';
require_once '../Classes/SceneManager.php';

if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: login.php"); 
    exit; 
}

$benevoleManager = new BenevoleManager();
$sceneManager = new SceneManager();

// Récupération des scènes pour le menu déroulant
$scenes = $sceneManager->findAll(['nom_scene' => 1]);

$id = null;
$nom = ""; 
$prenom = ""; 
$telephone = ""; 
$equipe = ""; 
$scene_assignee_id = "";
$pageTitle = "Ajouter un Bénévole";

// Modification Benevole
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $doc = $benevoleManager->findById($id);
    
    if ($doc) {
        $nom = $doc->nom;
        $prenom = $doc->prenom;
        $telephone = $doc->telephone;
        $equipe = $doc->equipe;
        $scene_assignee_id = isset($doc->scene_assignee_id) ? (string)$doc->scene_assignee_id : "";
        $pageTitle = "Modifier : " . $prenom . " " . $nom;
    }
}

// Formulaire Ajout/Modif Benevole
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $sceneObj = !empty($_POST['scene_id']) ? new MongoDB\BSON\ObjectId($_POST['scene_id']) : null;
    
    $data = [
        'nom' => $_POST['nom'],
        'prenom' => $_POST['prenom'],
        'telephone' => $_POST['telephone'],
        'equipe' => $_POST['equipe'],
        'scene_assignee_id' => $sceneObj
    ];

    if ($id) {
        $benevoleManager->update($id, $data);
    } else {
        $benevoleManager->create($data);
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
        <a href="dashboard.php" class="brand-title">TokaFest <span class="brand-subtitle">| Bénévoles</span></a>
        <div class="user-info">Admin: <?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin'; ?></div>
    </nav>
    <div class="admin-container">
        <div class="admin-card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header"><h2><?php echo $pageTitle; ?></h2></div>
            <form method="post">
                <div style="display:flex; gap:20px;">
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-input" value="<?php echo htmlspecialchars($prenom); ?>" required>
                    </div>
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-input" value="<?php echo htmlspecialchars($nom); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-input" value="<?php echo htmlspecialchars($telephone); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Équipe</label>
                    <select name="equipe" class="form-input">
                        <?php foreach(['Accueil','Bar','Sécurité','Technique','Nettoyage','Medical'] as $e): ?>
                            <option value="<?php echo $e; ?>" <?php if($equipe == $e) echo 'selected'; ?>><?php echo $e; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Affectation (Scène)</label>
                    <select name="scene_id" class="form-input">
                        <option value="">-- Aucune (Volant) --</option>
                        <?php foreach($scenes as $s): ?>
                            <option value="<?php echo $s->_id; ?>" <?php if((string)$s->_id === $scene_assignee_id) echo "selected"; ?>>
                                <?php echo $s->nom_scene; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-login" style="margin-top:20px;">Enregistrer</button>
                <a href="dashboard.php" style="display:block; text-align:center; margin-top:15px; color:#888; text-decoration:none;">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>