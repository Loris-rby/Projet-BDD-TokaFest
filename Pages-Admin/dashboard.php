<?php
session_start();

require_once '../Classes/Connexion.php';
require_once '../Classes/Manager.php';
require_once '../Classes/ArtisteManager.php';
require_once '../Classes/SceneManager.php';
require_once '../Classes/ConcertManager.php';
require_once '../Classes/BenevoleManager.php';
require_once '../Classes/StandManager.php';
// On a retiré FestivalierManager !

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// 1. Connexion Sécurisée (CRUCIAL)
$connexion = Connexion::getInstance();
$manager = $connexion->getManager();
$dbName = $connexion->getDbName();

// 2. Instanciation des Managers restants
$artisteManager    = new ArtisteManager();
$sceneManager      = new SceneManager();
$concertManager    = new ConcertManager();
$benevoleManager   = new BenevoleManager();
$standManager      = new StandManager();


// --- 3. Gestion des Actions (Suppressions) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $deleted = false;

    switch ($_GET['action']) {
        case 'delete_artiste':  
            $deleted = $artisteManager->delete($id); 
            break;
        case 'delete_concert':  
            $deleted = $concertManager->delete($id); 
            break;
        case 'delete_scene':    
            $deleted = $sceneManager->delete($id); 
            break;
        case 'delete_benevole': 
            $deleted = $benevoleManager->delete($id); 
            break;
        case 'delete_stand':    
            $deleted = $standManager->delete($id); 
            break;
    }

    if ($deleted) {
        header("Location: dashboard.php?msg=deleted");
        exit;
    }
}

// --- 4. Récupération des Données ---

// Artistes
$artistes = $artisteManager->findAll(['est_tete_affiche' => -1, 'nom_scene_artiste' => 1]);
$artistesMap = [];
foreach ($artistes as $a) $artistesMap[(string)$a->_id] = $a->nom_scene_artiste;

// Scènes
$scenes = $sceneManager->findAll(['nom_scene' => 1]);
$scenesMap = [];
foreach($scenes as $s) $scenesMap[(string)$s->_id] = $s->nom_scene;

// Concerts (Direct via Manager pour le tri)
$cursorProg = $manager->executeQuery("$dbName.concerts", new MongoDB\Driver\Query([], ['sort' => ['heure_debut' => 1]]));
$programmation = $cursorProg->toArray();

// Mapping Concerts -> Scènes
$concertsByScene = [];
foreach ($programmation as $p) {
    $sid = (string)$p->scene_id;
    $aid = (string)$p->artiste_id;
    $nomArtiste = $artistesMap[$aid] ?? "Inconnu";
    
    $heure = "??:??";
    if (isset($p->heure_debut)) {
        $heure = $p->heure_debut->toDateTime()->format('H:i');
    }
    $concertsByScene[$sid][] = "<strong>$nomArtiste</strong>";
}

// Bénévoles & Stands
$benevoles = $benevoleManager->findAll(['nom' => 1]);
$stands = $standManager->findAll(['nom_stand' => 1]);

// FESTIVALIERS (RECUPÉRATION DIRECTE SANS MANAGER)
$queryFest = new MongoDB\Driver\Query([], ['sort' => ['nom_complet' => 1]]);
$cursorFest = $manager->executeQuery("$dbName.festivaliers", $queryFest);
$tousLesFestivaliers = $cursorFest->toArray();


// --- 5. Statistiques ---
$stats = [
    'artistes'     => count($artistes),
    'concerts'     => count($programmation),
    'scenes'       => count($scenes),
    'benevoles'    => count($benevoles),
    'festivaliers' => count($tousLesFestivaliers),
    'stands'       => count($stands)
];

// Helper Durée
function formatDuree($debut, $fin) {
    $seconds = $fin->getTimestamp() - $debut->getTimestamp();
    $heures = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    return ($heures > 0) ? $heures . 'h' . sprintf("%02d", $minutes) : $minutes . ' min';
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
        .badge-purple { background-color: #7B61FF; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8em; }
        .badge { padding: 2px 8px; border-radius: 4px; font-size: 0.8em; background: #333; color: white; }
    </style>
</head>
<body>

    <nav class="admin-nav">
        <a href="dashboard.php" class="brand-title">TokaFest <span class="brand-subtitle">| Dashboard</span></a>
        <div class="user-info">
            Admin: <strong><?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin'; ?></strong>
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
                <p class="stat-number"><?php echo $stats['festivaliers']; ?></p>
                <p class="stat-label">Festivaliers</p>
            </div>
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
                            <span style="font-weight:bold;"><?php echo $debut->format('H:i') . "-" . $fin->format('H:i'); ?></span> 
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
                <h2>🎸 Artistes & Groupes</h2>
                <a href="artiste_edit.php" class="btn-add" style="background-color: #F1C40F; color: black;">＋ Ajouter</a>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="20%">Identité & Description</th>
                        <th width="15%">Équipe Technique</th>
                        <th>Discographie</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($artistes as $a): 
                        $desc = $a->description ?? '';
                        $membres = $a->membres ?? [];
                        $discographie = $a->discographie ?? [];
                    ?>
                    <tr>
                        <td style="vertical-align: top;">
                            <strong style="font-size: 1.2em; color: white;"><?php echo $a->nom_scene_artiste; ?></strong>
                            <br>
                            <span class="badge" style="margin-top:5px; display:inline-block;"><?php echo $a->genre_musical; ?></span>
                            
                            <div style="margin-top: 8px;">
                                <?php if(isset($a->est_tete_affiche) && $a->est_tete_affiche): ?>
                                    <span class="badge badge-headliner">⭐ Headliner</span>
                                <?php else: ?>
                                    <span style="color:#666; font-size:0.8em;">Standard</span>
                                <?php endif; ?>
                            </div>

                            <?php if(!empty($desc)): ?>
                                <div style="margin-top: 10px; font-size: 0.85em; color: #aaa; font-style: italic;">
                                    "<?php echo substr($desc, 0, 100) . (strlen($desc)>100 ? '...' : ''); ?>"
                                </div>
                            <?php endif; ?>
                        </td>

                        <td style="vertical-align: top; color: #ccc;">
                            <?php if(!empty($membres)): ?>
                                <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9em;">
                                <?php foreach($membres as $membre): ?>
                                    <li style="margin-bottom: 3px;"><?php echo is_string($membre) ? $membre : ($membre->nom ?? '?'); ?></li>
                                <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <span style="color:#555; font-style: italic;">Non renseigné</span>
                            <?php endif; ?>
                        </td>

                        <td style="vertical-align: top;">
                            <?php if(!empty($discographie)): ?>
                                <?php foreach($discographie as $album): 
                                    $titreAlbum = $album->titreAlbum ?? 'Album Inconnu';
                                    $annee = $album->anneeSortie ?? '-';
                                    $tracks = $album->tracks ?? [];
                                ?>
                                    <div style="background: rgba(255,255,255,0.05); padding: 8px; border-radius: 4px; margin-bottom: 8px;">
                                        <div style="color: #F1C40F; font-weight: bold; border-bottom: 1px solid #444; padding-bottom: 4px; margin-bottom: 4px;">
                                            <?php echo $titreAlbum; ?> <span style="color:#888; font-weight:normal;">(<?php echo $annee; ?>)</span>
                                        </div>

                                        <?php if(!empty($tracks)): ?>
                                            <ul style="list-style: none; padding-left: 10px; margin: 0;">
                                            <?php foreach($tracks as $track): 
                                                $titreTrack = $track->titre ?? 'Piste Inconnue';
                                                $duree = isset($track->dureeSecondes) ? gmdate("i:s", $track->dureeSecondes) : '--:--';
                                                $feats = $track->featuring ?? [];
                                            ?>
                                                <li style="font-size: 0.9em; margin-bottom: 3px; color: #ddd;">
                                                    <?php echo $titreTrack; ?> <span style="color:#666; font-size:0.8em;">(<?php echo $duree; ?>)</span>
                                                    <?php if(!empty($feats)): ?>
                                                        <span style="font-size: 0.85em; color: #7B61FF;">
                                                            Feat: <?php echo implode(", ", array_column((array)$feats, 'nomFeat')); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <div style="font-size: 0.8em; color: #666; padding-left: 10px;">Aucune piste.</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color:#555; font-style: italic;">Discographie vide</span>
                            <?php endif; ?>
                        </td>

                        <td style="vertical-align: top;">
                            <a href="artiste_edit.php?id=<?php echo $a->_id; ?>" class="btn-delete" style="color:white; border-color:#F1C40F; margin-bottom:5px; display:inline-block;">Modifier</a>
                            <a href="dashboard.php?action=delete_artiste&id=<?php echo $a->_id; ?>" class="btn-delete" onclick="return confirm('Supprimer cet artiste ?');">X</a>
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
                        <td style="font-weight: bold;"><?php echo $s->nom_scene; ?></td>
                        <td style="font-size: 0.9em; color: #aaa;">
                            <?php echo number_format($s->capacite_max, 0, ',', ' '); ?><br>
                            <?php echo $s->est_couverte ? "Couverte" : "☀ Plein air"; ?>
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
                <h2>🛍️ Stands</h2>
                <a href="stand_edit.php" class="btn-add">＋ Nouveau Stand</a>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nom du Stand</th>
                        <th>Type</th>
                        <th>Ouvert</th>
                        <th>Proprietaire</th>
                        <th>Actions</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($stands as $st): ?>
                    <tr>
                        <td style="font-weight: bold; color: white;"><?php echo $st->nom_stand; ?></td>
                        <td><span class="badge"><?php echo $st->type_stand; ?></span></td>
                        <td><?php echo $st->ouvert ? "✓ Ouvert" : "✗ Fermé"; ?></td>
                        <td><?php echo $st->proprietaire->nom_proprioStand . " ( " . $st->proprietaire->num_proprioStand . " )"; ?></td>                        <td>
                            <a href="stand_edit.php?id=<?php echo $st->_id; ?>" class="btn-delete" style="color:white; border-color:#7B61FF;">Modifier</a>
                            <a href="dashboard.php?action=delete_stand&id=<?php echo $st->_id; ?>" class="btn-delete" onclick="return confirm('Supprimer ce stand ?');">X</a>
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

        <div class="admin-card">
            <div class="section-header">
                <h2>🎫 Festivaliers</h2>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Identité</th>
                        <th>Adresse</th>
                        <th>Billets</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($tousLesFestivaliers as $f): 
                        $naissance = isset($f->date_naissance) ? $f->date_naissance->toDateTime()->format('d/m/Y') : '';
                        $billets = isset($f->billets_achetes) ? $f->billets_achetes : [];
                    ?>
                    <tr>
                        <td style="vertical-align: top;">
                            <strong style="color: white; font-size: 1.1em;"><?php echo $f->nom_complet; ?></strong><br>
                            <span style="color: #aaa; font-size: 0.9em;"><?php echo $f->email; ?></span>
                            <?php if($naissance): ?>
                                <br><span style="color: #666; font-size: 0.8em;"><?php echo $naissance; ?></span>
                            <?php endif; ?>
                        </td>
                        
                        <td style="vertical-align: top; color: #ccc;">
                            <?php 
                                echo ($f->adresse->rue ?? '') . "<br>" . 
                                     ($f->adresse->code_postal ?? '') . " " . 
                                     ($f->adresse->ville ?? ''); 
                            ?>
                        </td>

                        <td style="vertical-align: top;">
                            <?php if(count($billets) > 0): ?>
                                <ul style="list-style: none; padding: 0; margin: 0;">
                                <?php foreach($billets as $b): 
                                    $type = $b->type_billet ?? 'Inconnu';
                                    $prix = isset($b->prix_paye) ? $b->prix_paye . '€' : '0€';
                                    $dateAchat = isset($b->date_achat) ? $b->date_achat->toDateTime()->format('d/m/Y H:i') : '-';
                                    
                                    $qrData = $b->qr_code_data ?? null;
                                    $hash = $qrData->hash_billet ?? 'N/A';
                                    $urlQr = $qrData->url_image_qr ?? '#';
                                    
                                    $isValide = $qrData->validation->est_valide ?? false;
                                    $dateScan = isset($qrData->validation->date_scan) ? $qrData->validation->date_scan->toDateTime()->format('d/m à H:i') : null;
                                    $colorStatus = $isValide ? '#2ed573' : '#ff4757';
                                    $txtStatus = $isValide ? 'VALIDE' : 'DÉJÀ SCANNÉ';
                                ?>
                                    <li style="margin-bottom: 10px; background: rgba(255,255,255,0.05); padding: 8px; border-radius: 4px; font-size: 0.9em;">
                                        <div style="font-weight: bold; color: #fff; margin-bottom: 2px;">
                                            <?php echo $type; ?> <span style="color: #F1C40F;">(<?php echo $prix; ?>)</span>
                                        </div>
                                        <div style="color: #aaa; margin-bottom: 4px; font-size: 0.85em;">
                                            Acheté le : <?php echo $dateAchat; ?>
                                        </div>
                                        <div style="font-family: monospace; color: #888; margin-bottom: 4px; word-break: break-all;">
                                            #<?php echo substr($hash, 0, 12); ?>... <br>
                                            <a href="<?php echo $urlQr; ?>" target="_blank" style="color: #7B61FF;">Voir le QR</a>
                                        </div>
                                        <div style="border-top: 1px solid #444; padding-top: 4px; margin-top: 4px;">
                                            Statut : <span style="color: <?php echo $colorStatus; ?>; font-weight: bold;"><?php echo $txtStatus; ?></span>
                                            <?php if(!$isValide && $dateScan): ?>
                                                <br><span style="color: #eccc68;">Scanné le : <?php echo $dateScan; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <span style="color:#555; font-style: italic;">Aucun billet</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>