<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

// --- Gestion des Suppressions ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    try {
        $filter = ['_id' => new MongoDB\BSON\ObjectId($_GET['id'])];
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->delete($filter);

        $collection = '';
        switch ($_GET['action']) {
            case 'delete_artiste':  $collection = 'tokafest_db.artistes'; break;
            case 'delete_concert':  $collection = 'tokafest_db.concerts'; break;
            case 'delete_scene':    $collection = 'tokafest_db.scenes'; break;
            case 'delete_benevole': $collection = 'tokafest_db.benevoles'; break;
        }

        if ($collection) {
            $manager->executeBulkWrite($collection, $bulk);
        }
        header("Location: dashboard.php");
        exit;
    } catch (Exception $e) {}
}

// --- Récupération des Données ---

// Artistes
$cursorArtistes = $manager->executeQuery('tokafest_db.artistes', new MongoDB\Driver\Query([], ['sort' => ['est_tete_affiche' => -1, 'nom_scene_artiste' => 1]]));
$artistes = $cursorArtistes->toArray();

$artistesMap = [];
foreach ($artistes as $a) $artistesMap[(string)$a->_id] = $a->nom_scene_artiste;

// Scènes
$cursorScenes = $manager->executeQuery('tokafest_db.scenes', new MongoDB\Driver\Query([], ['sort' => ['nom_scene' => 1]]));
$scenes = $cursorScenes->toArray();

$scenesMap = [];
foreach($scenes as $s) $scenesMap[(string)$s->_id] = $s->nom_scene;

// Concerts (Line-up)
$cursorProg = $manager->executeQuery('tokafest_db.concerts', new MongoDB\Driver\Query([], ['sort' => ['heure_debut' => 1]]));
$programmation = $cursorProg->toArray();

$concertsByScene = [];
foreach ($programmation as $p) {
    $sid = (string)$p->scene_id;
    $aid = (string)$p->artiste_id;
    $nomArtiste = $artistesMap[$aid] ?? "Inconnu";
    $heure = $p->heure_debut->toDateTime()->format('H:i');
    $concertsByScene[$sid][] = "<span style='color:#ccc'>$heure</span> <strong>$nomArtiste</strong>";
}

// Bénévoles
$cursorBenevoles = $manager->executeQuery('tokafest_db.benevoles', new MongoDB\Driver\Query([], ['sort' => ['nom' => 1]]));
$benevoles = $cursorBenevoles->toArray();

// --- Statistiques ---
$stats = [
    'artistes'  => count($artistes),
    'concerts'  => count($programmation),
    'scenes'    => count($scenes),
    'benevoles' => count($benevoles)
];

function formatDuree($debut, $fin) {
    return floor(($fin->getTimestamp() - $debut->getTimestamp()) / 60) . ' min';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - TokaFest</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: linear-gradient(145deg, #1a1a1a, #0a0a0a); border: 1px solid #333; border-left: 4px solid #7B61FF; padding: 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        .stat-number { font-size: 2.5em; font-weight: bold; color: white; margin: 0; }
        .stat-label { color: #888; font-size: 0.9em; text-transform: uppercase; letter-spacing: 1px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-add { background-color: #7B61FF; color: white; padding: 10px 20px; text-decoration: none; border-radius: 30px; font-weight: bold; transition: 0.3s; font-size: 0.9em; }
        .btn-add:hover { background-color: #9d8aff; box-shadow: 0 0 15px rgba(123, 97, 255, 0.4); }
        .badge-headliner { background-color: #F1C40F; color: black; font-weight: bold; }
        .list-concerts { list-style: none; padding: 0; margin: 0; font-size: 0.9em; }
        .list-concerts li { margin-bottom: 5px; padding-bottom: 5px; border-bottom: 1px solid #222; }
    </style>
</head>
<body>

    <nav class="admin-nav">
        <a href="dashboard.php" class="brand-title">TokaFest <span class="brand-subtitle">| Dashboard</span></a>
        <div class="user-info">
            Admin: <strong><?php echo $_SESSION['admin_name']; ?></strong>
            <a href="logout.php" class="btn-logout" style="margin-left: 15px;">Déconnexion</a>
        </div>
    </nav>

    <div class="admin-container">

        <div class="stats-grid">
            <div class="stat-card" style="border-left-color: #F1C40F;">
                <p class="stat-number"><?php echo $stats['artistes']; ?></p>
                <p class="stat-label">Artistes</p>
            </div>
            <div class="stat-card">
                <p class="stat-number"><?php echo $stats['concerts']; ?></p>
                <p class="stat-label">Concerts</p>
            </div>
            <div class="stat-card" style="border-left-color: #FF6FA3;">
                <p class="stat-number"><?php echo $stats['scenes']; ?></p>
                <p class="stat-label">Scènes</p>
            </div>
            <div class="stat-card" style="border-left-color: #2ed573;">
                <p class="stat-number"><?php echo $stats['benevoles']; ?></p>
                <p class="stat-label">Bénévoles</p>
            </div>
        </div>

        <div class="admin-card">
            <div class="section-header">
                <h2>🎸 Artistes & Groupes</h2>
                <a href="artiste_edit.php" class="btn-add" style="background-color: #F1C40F; color: black;">＋ Ajouter</a>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Genre</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($artistes as $a): ?>
                    <tr>
                        <td style="font-weight: bold; color: white;"><?php echo $a->nom_scene_artiste; ?></td>
                        <td><span class="badge"><?php echo $a->genre_musical; ?></span></td>
                        <td>
                            <?php if(isset($a->est_tete_affiche) && $a->est_tete_affiche): ?>
                                <span class="badge badge-headliner">⭐ Headliner</span>
                            <?php else: ?>
                                <span style="color:#666; font-size:0.9em;">Standard</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="artiste_edit.php?id=<?php echo $a->_id; ?>" class="btn-delete" style="color:white; border-color:#F1C40F;">Modifier</a>
                            <a href="dashboard.php?action=delete_artiste&id=<?php echo $a->_id; ?>" class="btn-delete" onclick="return confirm('Supprimer cet artiste ?');">X</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-card">
            <div class="section-header">
                <h2>📅 Line-up & Horaires</h2>
                <a href="concert_edit.php" class="btn-add" style="background-color: #ff4757;">＋ Programmer</a>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Horaire</th>
                        <th>Artiste</th>
                        <th>Scène</th>
                        <th>Durée</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($programmation as $prog): 
                        $aId = (string)$prog->artiste_id;
                        $sId = (string)$prog->scene_id;
                        $debut = $prog->heure_debut->toDateTime();
                        $fin = $prog->heure_fin->toDateTime();
                    ?>
                    <tr>
                        <td style="color: #ccc; font-family: monospace;">
                            <span style="color: #7B61FF; font-weight:bold;"><?php echo $debut->format('H:i'); ?></span> 
                            <small>(<?php echo $debut->format('d/m'); ?>)</small>
                        </td>
                        <td style="font-weight: bold; color: white;"><?php echo $artistesMap[$aId] ?? "Inconnu"; ?></td>
                        <td><span class="badge badge-purple"><?php echo $scenesMap[$sId] ?? "Inconnue"; ?></span></td>
                        <td><?php echo formatDuree($debut, $fin); ?></td>
                        <td>
                            <a href="concert_edit.php?id=<?php echo $prog->_id; ?>" class="btn-delete" style="color:white; border-color:#7B61FF;">Modifier</a>
                            <a href="dashboard.php?action=delete_concert&id=<?php echo $prog->_id; ?>" class="btn-delete" onclick="return confirm('Supprimer ce créneau ?');">X</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-card">
            <div class="section-header">
                <h2>🏟️ Scènes</h2>
                <a href="scene_edit.php" class="btn-add">＋ Nouvelle Scène</a>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="20%">Scène</th>
                        <th>Infos</th>
                        <th>Concerts Prévus</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($scenes as $s): $sid = (string)$s->_id; ?>
                    <tr>
                        <td style="color: #7B61FF; font-weight: bold;"><?php echo $s->nom_scene; ?></td>
                        <td style="font-size: 0.9em; color: #aaa;">
                            👥 <?php echo number_format($s->capacite_max, 0, ',', ' '); ?><br>
                            <?php echo $s->est_couverte ? "⛺ Couverte" : "☀ Plein air"; ?>
                        </td>
                        <td>
                            <?php if(isset($concertsByScene[$sid])): ?>
                                <ul class="list-concerts">
                                    <?php foreach($concertsByScene[$sid] as $c): ?><li><?php echo $c; ?></li><?php endforeach; ?>
                                </ul>
                            <?php else: ?><span style="font-style:italic; color:#555;">Aucun concert</span><?php endif; ?>
                        </td>
                        <td>
                            <a href="scene_edit.php?id=<?php echo $sid; ?>" class="btn-delete" style="color:white; border-color:#7B61FF;">Modifier</a>
                            <a href="dashboard.php?action=delete_scene&id=<?php echo $sid; ?>" class="btn-delete" onclick="return confirm('Supprimer cette scène ?');">X</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-card">
            <div class="section-header">
                <h2>🧑‍🤝‍🧑 Bénévoles</h2>
                <a href="benevole_edit.php" class="btn-add">＋ Nouveau Bénévole</a>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Équipe</th>
                        <th>Affectation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($benevoles as $b): 
                        $sId = isset($b->scene_assignee_id) ? (string)$b->scene_assignee_id : null;
                    ?>
                    <tr>
                        <td style="font-weight: bold; color: white;"><?php echo $b->prenom . " " . strtoupper($b->nom); ?></td>
                        <td><span class="badge"><?php echo $b->equipe; ?></span></td>
                        <td style="color:#ccc;"><?php echo ($sId && isset($scenesMap[$sId])) ? $scenesMap[$sId] : "—"; ?></td>
                        <td>
                            <a href="benevole_edit.php?id=<?php echo $b->_id; ?>" class="btn-delete" style="color:white; border-color:#7B61FF;">Modifier</a>
                            <a href="dashboard.php?action=delete_benevole&id=<?php echo $b->_id; ?>" class="btn-delete" onclick="return confirm('Supprimer ce bénévole ?');">X</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>