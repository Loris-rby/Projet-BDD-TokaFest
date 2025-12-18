<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

$manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

$id = null;
$nom_stand = ""; 
$type_stand = ""; 
$ouvert = false; 

$proprietaire = []; 
$nom_proprioStand = "";
$num_proprioStand = ""; 

$pageTitle = "Ajouter un Stand";

if (isset($_GET['id'])) {
    try {
        $id = new MongoDB\BSON\ObjectId($_GET['id']);
        $cursor = $manager->executeQuery('tokafest_db.stands', new MongoDB\Driver\Query(['_id' => $id]));
        $doc = current($cursor->toArray());
        if ($doc) {
            $nom_stand = $doc->nom_stand;
            $type_stand = $doc->type_stand;
            $ouvert = $doc->ouvert ?? false;

            $proprietaire = $doc->proprietaire;
            $nom_proprioStand = $doc->nom_proprioStand;
            $num_proprioStand = $doc->num_proprioStand;

            $pageTitle = "Modifier : " . $nom_stand;
        }
    } catch (Exception $e) {}
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom_stand' => $_POST['nom_stand'],
        'type_stand' => $_POST['type_stand'],
        'ouvert' => isset($_POST['ouvert']),
        'proprietaire' => [
            'nom_proprioStand' => $_POST['nom_proprioStand'],
            'num_proprioStand' => $_POST['num_proprioStand']
        ]
    ];

    $bulk = new MongoDB\Driver\BulkWrite;
    if ($id) $bulk->update(['_id' => $id], ['$set' => $data]);
    else     $bulk->insert($data);

    $manager->executeBulkWrite('tokafest_db.stands', $bulk);
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
        <div class="user-info">Admin: <?php echo $_SESSION['admin_name']; ?></div>
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
                <div class="form-group">
                    <label class="form-label">Nom du propriétaire</label>
                    <input type="text" name="nom_proprioStand" class="form-input" value="<?php echo htmlspecialchars($nom_proprioStand); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Numéro du propriétaire</label>
                    <input type="text" name="num_proprioStand" class="form-input" value="<?php echo htmlspecialchars($num_proprioStand); ?>" required>
                </div>


                <button type="submit" class="btn-login">Enregistrer</button>
                <a href="dashboard.php" style="display:block; text-align:center; margin-top:15px; color:#888; text-decoration:none;">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>