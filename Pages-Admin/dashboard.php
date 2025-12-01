<?php
session_start();

// Si pas connecté, on redirige vers login.php (qui est dans le même dossier)
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

// Action Supprimer
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    try {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->delete(['_id' => new MongoDB\BSON\ObjectId($_GET['id'])]);
        $manager->executeBulkWrite('tokafest_db.benevoles', $bulk);
        // On reste sur la même page
        header("Location: dashboard.php");
        exit;
    } catch (Exception $e) {}
}

$cursorBenevoles = $manager->executeQuery('tokafest_db.benevoles', new MongoDB\Driver\Query([], ['sort' => ['nom' => 1]]));
$cursorScenes = $manager->executeQuery('tokafest_db.scenes', new MongoDB\Driver\Query([]));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - TokaFest</title>
    
    <link rel="stylesheet" href="../css/admin.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

    <nav class="navbar" style="border-radius: 0; margin-bottom: 30px;">
        <div class="navbar-brand">
            <a href="../index.php" style="color: white; text-decoration: none;">TokaFest</a> 
            <span style="font-size: 0.6em; color: #888;">| Dashboard</span>
        </div>
        <div>
            <span style="color:white; margin-right:15px;">Admin: <?php echo $_SESSION['admin_name']; ?></span>
            <a href="logout.php" class="btn-logout">Déconnexion</a>
        </div>
    </nav>

    <div class="admin-container">

        <div class="admin-card">
            <div class="card-header">
                <h2>Bénévoles</h2>
            </div>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Équipe</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cursorBenevoles as $b): ?>
                    <tr>
                        <td style="font-weight:bold; color:white;">
                            <?php echo $b->nom . " " . $b->prenom; ?>
                        </td>
                        <td><span class="badge"><?php echo $b->equipe; ?></span></td>
                        <td><?php echo $b->telephone; ?></td>
                        <td>
                            <a href="dashboard.php?action=delete&id=<?php echo $b->_id; ?>" 
                               class="btn-delete"
                               onclick="return confirm('Supprimer ce bénévole ?');">
                               Supprimer
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-card">
            <div class="card-header">
                <h2>Scènes</h2>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Capacité</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cursorScenes as $s): ?>
                    <tr>
                        <td style="color: #7B61FF;"><?php echo $s->nom_scene; ?></td>
                        <td><?php echo number_format($s->capacite_max, 0, ',', ' '); ?></td>
                        <td>
                            <?php echo $s->est_couverte ? "⛺ Couverte" : "☀ Plein air"; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>