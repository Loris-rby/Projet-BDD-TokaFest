<?php
session_start();

require_once '../Classes/Connexion.php';
require_once '../Classes/Manager.php';
require_once '../Classes/ArtisteManager.php';

if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

$artisteManager = new ArtisteManager();

$id = null;
$nom_artiste = ""; 
$genre = ""; 
$description = ""; 
$est_tete_affiche = false;
$membresList = []; // On stocke les membres sous forme de tableau ici
$discographie = []; 

$pageTitle = "Ajouter un Artiste";
$description = "";
$equipeTechnique = [];

// --- 1. MODE MODIFICATION ---
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $doc = $artisteManager->findById($id);
    
    if ($doc) {
        $nom_artiste = $doc->nom_scene_artiste;
        $genre = $doc->genre_musical;
        $description = $doc->description ?? "";
        $est_tete_affiche = $doc->est_tete_affiche ?? false;

        // Récupération des membres (Tableau simple)
        if (isset($doc->membres) && is_array($doc->membres)) {
            foreach($doc->membres as $m) {
                // On gère le cas où c'est un string ou un objet
                if(is_string($m)) $membresList[] = $m;
                elseif(isset($m->nom)) $membresList[] = $m->nom;
            }
        }

        // Récupération Discographie
        if (isset($doc->discographie)) {
            $discographie = $doc->discographie;
        }

        $pageTitle = "Modifier : " . $nom_artiste;
    }
}

// --- 2. TRAITEMENT DU FORMULAIRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupération propre du tableau HTML 'membres[]'
    // array_filter permet d'enlever les cases vides si l'admin en a laissé une blanche
    $membresArray = [];
    if (isset($_POST['membres']) && is_array($_POST['membres'])) {
        $membresArray = array_values(array_filter($_POST['membres'])); 
    }

    $data = [
        'nom_scene_artiste' => $_POST['nom_scene_artiste'],
        'genre_musical'     => $_POST['genre_musical'],
        'description'       => $_POST['description'],
        'est_tete_affiche'  => isset($_POST['est_tete_affiche']),
        'membres'           => $membresArray // On envoie le tableau direct
    ];

    if ($id) {
        $bulk->update(['_id' => $id], ['$set' => [
            'nom_scene_artiste' => $_POST['nom_scene_artiste'],
            'genre_musical' => $_POST['genre_musical'],
            'est_tete_affiche' => isset($_POST['est_tete_affiche'])
        ]]);
    } else {
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
    <style>
        .membre-row { display: flex; gap: 10px; margin-bottom: 10px; }
        .btn-remove { background: #ff4757; color: white; border: none; padding: 0 15px; cursor: pointer; border-radius: 4px; font-weight: bold; }
        .btn-remove:hover { background: #e84118; }
        .btn-add-member { background: #222; border: 1px dashed #7B61FF; color: #7B61FF; width: 100%; padding: 10px; cursor: pointer; margin-top: 5px; }
        .btn-add-member:hover { background: rgba(123, 97, 255, 0.1); }
    </style>
</head>
<body>
    <nav class="admin-nav">
        <a href="dashboard.php" class="brand-title">TokaFest <span class="brand-subtitle">| Artistes</span></a>
        <div class="user-info">Admin: <?php echo $_SESSION['admin_name']; ?></div>
    </nav>
    <div class="admin-container">
        <div class="admin-card" style="max-width: 700px; margin: 0 auto;">
            <div class="card-header"><h2><?php echo $pageTitle; ?></h2></div>
            <form method="post">
                
                <div class="form-group">
                    <label class="form-label">Nom de scène</label>
                    <input type="text" name="nom_scene_artiste" class="form-input" value="<?php echo htmlspecialchars($nom_artiste); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Genre Musical</label>
                    <input type="text" name="genre_musical" class="form-input" value="<?php echo htmlspecialchars($genre); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" style="height: 80px;"><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="form-group" style="margin: 20px 0;">
                    <label style="color: #F1C40F; display: flex; align-items: center; cursor: pointer; font-weight: bold;">
                        <input type="checkbox" name="est_tete_affiche" style="transform: scale(1.5); margin-right: 15px;" <?php if($est_tete_affiche) echo 'checked'; ?>>
                        Tête d'affiche
                    </label>
                </div>

                <div class="form-group" style="border-top: 1px solid #333; padding-top: 15px; margin-top: 15px;">
                    <label class="form-label">Équipe Technique / Membres</label>
                    
                    <div id="membres-container">
                        <?php 
                        // S'il y a déjà des membres, on crée une ligne pour chaque
                        if (!empty($membresList)): 
                            foreach($membresList as $membre): ?>
                            <div class="membre-row">
                                <input type="text" name="membres[]" class="form-input" value="<?php echo htmlspecialchars($membre); ?>" placeholder="Nom Prénom">
                                <button type="button" class="btn-remove" onclick="removeRow(this)">X</button>
                            </div>
                        <?php endforeach; 
                        else: ?>
                            <div class="membre-row">
                                <input type="text" name="membres[]" class="form-input" placeholder="Nom Prénom">
                                <button type="button" class="btn-remove" onclick="removeRow(this)">X</button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="button" class="btn-add-member" onclick="addMembre()">＋ Ajouter un membre</button>
                </div>

                <?php if (!empty($discographie)): ?>
                <div style="margin-top: 30px; border-top: 1px solid #333; padding-top: 15px;">
                    <label class="form-label" style="color: #7B61FF; margin-bottom: 15px;">📀 Discographie actuelle (Consultation)</label>
                    <?php foreach($discographie as $album): 
                        $titreAlbum = $album->titreAlbum ?? 'Album sans titre';
                        $annee = $album->anneeSortie ?? '-';
                        $tracks = $album->tracks ?? [];
                    ?>
                        <div style="background: #222; border-left: 3px solid #F1C40F; padding: 10px; margin-bottom: 10px; font-size: 0.9em;">
                            <div style="font-weight: bold; color: white;">
                                <?php echo $titreAlbum; ?> <span style="color:#888;">(<?php echo $annee; ?>)</span>
                            </div>
                            <?php if(!empty($tracks)): ?>
                                <ul style="margin: 5px 0 0 20px; padding: 0; color: #ccc;">
                                <?php foreach($tracks as $t): ?>
                                    <li style="margin-bottom: 2px;"><?php echo $t->titre; ?></li>
                                <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn-login" style="margin-top: 25px;">Enregistrer les modifications</button>
                <a href="dashboard.php" style="display:block; text-align:center; margin-top:15px; color:#888; text-decoration:none;">Annuler</a>
            </form>
        </div>
    </div>

    <script>
        function addMembre() {
            const container = document.getElementById('membres-container');
            const div = document.createElement('div');
            div.className = 'membre-row';
            div.innerHTML = `
                <input type="text" name="membres[]" class="form-input" placeholder="Nom Prénom">
                <button type="button" class="btn-remove" onclick="removeRow(this)">X</button>
            `;
            container.appendChild(div);
        }

        function removeRow(btn) {
            const container = document.getElementById('membres-container');
            // On empêche de tout supprimer s'il ne reste qu'une ligne (optionnel, mais mieux pour l'UX)
            if (container.children.length > 1) {
                btn.parentElement.remove();
            } else {
                // Si c'est la dernière ligne, on vide juste le champ
                btn.previousElementSibling.value = '';
            }
        }
    </script>
</body>
</html>
