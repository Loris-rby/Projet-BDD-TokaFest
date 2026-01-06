<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

$manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

$id = null;
$nom_artiste = ""; 
$genre = ""; 
$est_tete_affiche = false;
$pageTitle = "Ajouter un Artiste";
$description = "";
$equipeTechnique = [];

if (isset($_GET['id'])) {
    try {
        $id = new MongoDB\BSON\ObjectId($_GET['id']);
        $cursor = $manager->executeQuery('tokafest_db.artistes', new MongoDB\Driver\Query(['_id' => $id]));
        $doc = current($cursor->toArray());
        if ($doc) {
            $nom_artiste = $doc->nom_scene_artiste;
            $genre = $doc->genre_musical;
            $est_tete_affiche = $doc->est_tete_affiche ?? false;
            $pageTitle = "Modifier : " . $nom_artiste;
            $description = $doc->description ?? "";
            $equipeTechnique = $doc->equipe_technique ?? [];
        }
    } catch(Exception $e) {}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_nom = trim($_POST['nom_scene_artiste'] ?? '');
    $posted_genre = trim($_POST['genre_musical'] ?? '');
    $posted_est_tete = isset($_POST['est_tete_affiche']);
    $posted_description = trim($_POST['description'] ?? '');
    $posted_equipe_Technique = array_values(array_filter(array_map('trim', $_POST['equipe_technique'] ?? [])));

    $data = [
        'nom_scene_artiste' => $posted_nom,
        'genre_musical' => $posted_genre,
        'est_tete_affiche' => $posted_est_tete,
        'membres' => [], 'discographie' => [],
        'description' => $posted_description,
        'equipe_technique' => $posted_equipe_Technique
    ];

    $bulk = new MongoDB\Driver\BulkWrite;
    if ($id) {
        $bulk->update(['_id' => $id], ['$set' => [
            'nom_scene_artiste' => $posted_nom,
            'genre_musical' => $posted_genre,
            'est_tete_affiche' => $posted_est_tete,
            'description' => $posted_description,
            'equipe_technique' => $posted_equipe_Technique
        ]]);
    } else {
        $bulk->insert($data);
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
            <div class="card-header"><h2><?php echo $pageTitle; ?></h2></div>
            <form method="post">

            <!-- ENTRER LE NOM DE L'ARTISTE-->
                <div class="form-group">
                    <label class="form-label">Nom de l'artiste</label>
                    <input type="text" name="nom_scene_artiste" class="form-input" value="<?php echo htmlspecialchars($nom_artiste); ?>" required>
                </div>

                <!-- ENTRER LE GENRE MUISCALE-->
                <div class="form-group">
                    <label class="form-label">Genre Musical</label>
                    <input type="text" name="genre_musical" class="form-input" value="<?php echo htmlspecialchars($genre); ?>" required>
                </div>

                <!-- ENTRER LA DESCRIPRION-->
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-input" value="<?php echo htmlspecialchars($description); ?>" required>
                </div>

                <!-- ENTRER L'EQUIPE TECHNIQUE-->
                <div class="form-group">
                    <label class="form-label">Equipe Technique</label>
                    <div id="equipe-technique-list">
                        <div class="member-row" style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
                            <input type="text" name="equipe_technique[]" class="form-input" value="" placeholder="Prénom Nom">
                            <button type="button" class="btn-remove" onclick="removeMember(this)" style="background:#e74c3c; border:none; padding:3px 5px; cursor:pointer;">Supprimer</button>
                        </div>
                    </div>
                    <button type="button" onclick="addMember()" class="btn-login" style="margin-top:8px; width:40px;">+</button>
                    <small class="hint" style=" margin-top:6px;">Saisir le prénom et nom (ex: Jean_Dupont)</small>
                </div>

                <!-- ENTRER SI TETE D'AFFICHE-->
                <div class="form-group" style="margin: 20px 0;">
                    <label style="color: #F1C40F; display: flex; align-items: center; cursor: pointer; font-weight: bold;">
                        <input type="checkbox" name="est_tete_affiche" style="transform: scale(1.5); margin-right: 15px;" <?php if($est_tete_affiche) echo 'checked'; ?>>
                        Tête d'affiche
                    </label>
                </div>
                <button type="submit" class="btn-login">Enregistrer</button>
                <a href="dashboard.php" style="display:block; text-align:center; margin-top:15px; color:#888; text-decoration:none;">Annuler</a>
            </form>
        </div>
    </div>
    </body>
















    <!-- JS pour ajouter/supprimer des membres de l'équipe technique pas fait 

</html>
