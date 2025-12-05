<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

$manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

$id = null;
$nom_scene = ""; $capacite_max = ""; $est_couverte = false;
$pageTitle = "Ajouter une Scène";

if (isset($_GET['id'])) {
    try {
        $id = new MongoDB\BSON\ObjectId($_GET['id']);
        $cursor = $manager->executeQuery('tokafest_db.scenes', new MongoDB\Driver\Query(['_id' => $id]));
        $scene = current($cursor->toArray());
        if ($scene) {
            $nom_scene = $scene->nom_scene;
            $capacite_max = $scene->capacite_max;
            $est_couverte = $scene->est_couverte;
            $pageTitle = "Modifier : " . $nom_scene;
        }
    } catch(Exception $e) {}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $document = [
        'nom_scene' => $_POST['nom_scene'],
        'capacite_max' => (int)$_POST['capacite_max'],
        'est_couverte' => isset($_POST['est_couverte'])
    ];
    $bulk = new MongoDB\Driver\BulkWrite;
    if ($id) $bulk->update(['_id' => $id], ['$set' => $document]);
    else     $bulk->insert($document);

    $manager->executeBulkWrite('tokafest_db.scenes', $bulk);
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
        <a href="dashboard.php" class="brand-title">TokaFest <span class="brand-subtitle">| Scènes</span></a>
        <div class="user-info">Admin: <?php echo $_SESSION['admin_name']; ?></div>
    </nav>
    <div class="admin-container">
        <div class="admin-card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header"><h2><?php echo $pageTitle; ?></h2></div>
            <form method="post">
                <div class="form-group"><label class="form-label">Nom de la scène</label><input type="text" name="nom_scene" class="form-input" value="<?php echo htmlspecialchars($nom_scene); ?>" required></div>
                <div class="form-group"><label class="form-label">Capacité Max</label><input type="number" name="capacite_max" class="form-input" value="<?php echo htmlspecialchars($capacite_max); ?>" required></div>
                <div class="form-group" style="margin: 20px 0;">
                    <label style="color: #ccc; display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="est_couverte" style="transform: scale(1.5); margin-right: 10px;" <?php if($est_couverte) echo 'checked'; ?>> Scène couverte
                    </label>
                </div>
                <button type="submit" class="btn-login">Enregistrer</button>
                <a href="dashboard.php" style="display:block; text-align:center; margin-top:15px; color:#888; text-decoration:none;">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>