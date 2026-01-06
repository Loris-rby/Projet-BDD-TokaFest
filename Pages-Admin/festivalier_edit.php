<?php
session_start();

require_once '../Classes/Connexion.php';
require_once '../Classes/Manager.php';
require_once '../Classes/FestivalierManager.php';

if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

$festivalierManager = new FestivalierManager();

// --- CONFIGURATION DES PRIX ---
// Tu peux modifier les tarifs ici !
$grilleTarifaire = [
    "Pass 1 Jour"  => 45.00,
    "Pass 2 Jours" => 80.00,
    "Pass 3 Jours" => 110.00
];

$id = null;
$nom_complet = "";
$email = "";
$date_naissance = "";
$rue = ""; $ville = ""; $code_postal = "";
$billets_existants = []; 

$pageTitle = "Ajouter un Festivalier";

// --- MODE MODIFICATION ---
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $doc = $festivalierManager->findById($id);
    
    if ($doc) {
        $nom_complet = $doc->nom_complet;
        $email = $doc->email;
        if (isset($doc->date_naissance)) $date_naissance = $doc->date_naissance->toDateTime()->format('Y-m-d');
        if (isset($doc->adresse)) {
            $rue = $doc->adresse->rue ?? "";
            $ville = $doc->adresse->ville ?? "";
            $code_postal = $doc->adresse->code_postal ?? "";
        }
        if (isset($doc->billets_achetes)) $billets_existants = $doc->billets_achetes;
        $pageTitle = "Consultation : " . $nom_complet;
    }
}

// --- TRAITEMENT DU FORMULAIRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $dateMongo = null;
    if (!empty($_POST['date_naissance'])) {
        $dateMongo = new MongoDB\BSON\UTCDateTime(strtotime($_POST['date_naissance']) * 1000);
    }

    $liste_billets = $billets_existants;

    // AJOUT D'UN BILLET (Si un type est choisi)
    if (!empty($_POST['new_billet_type'])) {
        $typeChoisi = $_POST['new_billet_type'];
        
        // On récupère le prix fixe depuis la grille (sécurité serveur)
        $prixFixe = isset($grilleTarifaire[$typeChoisi]) ? $grilleTarifaire[$typeChoisi] : 0;

        $nouveauBillet = [
            'type_billet' => $typeChoisi,
            'date_achat'  => new MongoDB\BSON\UTCDateTime(),
            'prix_paye'   => (float)$prixFixe,
            'qr_code_data' => [
                'hash_billet'  => bin2hex(random_bytes(8)), 
                'url_image_qr' => '/qr/default.png',
                'validation'   => [
                    'est_valide' => true,
                    'date_scan'  => null
                ]
            ]
        ];
        $liste_billets[] = $nouveauBillet;
    }

    $data = [
        'nom_complet' => $_POST['nom_complet'],
        'email' => $_POST['email'],
        'date_naissance' => $dateMongo,
        'adresse' => [
            'rue' => $_POST['rue'],
            'ville' => $_POST['ville'],
            'code_postal' => $_POST['code_postal']
        ],
        'billets_achetes' => $liste_billets 
    ];

    if ($id) $festivalierManager->update($id, $data);
    else     $festivalierManager->create($data);

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
</head>
<body>
    <nav class="admin-nav">
        <a href="dashboard.php" class="brand-title">TokaFest <span class="brand-subtitle">| Festivaliers</span></a>
        <div class="user-info">Admin: <?php echo $_SESSION['admin_name']; ?></div>
    </nav>
    <div class="admin-container">
        <div class="admin-card" style="max-width: 700px; margin: 0 auto;">
            <div class="card-header"><h2><?php echo $pageTitle; ?></h2></div>
            <form method="post">
                
                <h3 style="color: #7B61FF; border-bottom: 1px solid #333; margin-bottom: 15px;">👤 Identité</h3>
                <div class="form-group"><label class="form-label">Nom Complet</label><input type="text" name="nom_complet" class="form-input" value="<?php echo htmlspecialchars($nom_complet); ?>" required></div>
                <div style="display:flex; gap:20px;">
                    <div class="form-group" style="flex:1"><label class="form-label">Email</label><input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($email); ?>" required></div>
                    <div class="form-group" style="flex:1"><label class="form-label">Naissance</label><input type="date" name="date_naissance" class="form-input" value="<?php echo $date_naissance; ?>" required></div>
                </div>

                <h3 style="color: #7B61FF; border-bottom: 1px solid #333; margin: 20px 0 15px 0;">📍 Adresse</h3>
                <div class="form-group"><label class="form-label">Rue</label><input type="text" name="rue" class="form-input" value="<?php echo htmlspecialchars($rue); ?>"></div>
                <div style="display:flex; gap:20px;">
                    <div class="form-group" style="flex:2"><label class="form-label">Ville</label><input type="text" name="ville" class="form-input" value="<?php echo htmlspecialchars($ville); ?>"></div>
                    <div class="form-group" style="flex:1"><label class="form-label">Code Postal</label><input type="text" name="code_postal" class="form-input" value="<?php echo htmlspecialchars($code_postal); ?>"></div>
                </div>

                <div style="background: #222; padding: 15px; border-radius: 8px; margin-top: 30px; border: 1px dashed #555;">
                    <label class="form-label" style="color: #F1C40F;">＋ Ajouter un billet (Calcul Auto)</label>
                    <div style="display:flex; gap:10px;">
                        
                        <select name="new_billet_type" id="billetSelect" class="form-input" onchange="updatePrice()">
                            <option value="">-- Choisir le Pass --</option>
                            <?php foreach($grilleTarifaire as $nomPass => $prixPass): ?>
                                <option value="<?php echo $nomPass; ?>" data-prix="<?php echo $prixPass; ?>">
                                    <?php echo $nomPass; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <input type="text" id="billetPriceDisplay" class="form-input" placeholder="Prix (€)" style="width: 100px; background-color: #333; color: #fff;" readonly>
                    </div>
                </div>

                <?php if (!empty($billets_existants)): ?>
                    <div style="margin-top: 20px;">
                        <label class="form-label">Billets actuels :</label>
                        <ul style="color: #ccc; font-size: 0.9em; padding-left: 20px;">
                        <?php foreach($billets_existants as $b): ?>
                            <li>
                                <strong><?php echo $b->type_billet; ?></strong> 
                                (<?php echo $b->prix_paye; ?> €) - 
                                Hash: <span style="font-family:monospace; color:#F1C40F;"><?php echo $b->qr_code_data->hash_billet; ?></span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn-login" style="margin-top:25px;">Enregistrer / Mettre à jour</button>
                <a href="dashboard.php" style="display:block; text-align:center; margin-top:15px; color:#888; text-decoration:none;">Retour</a>
            </form>
        </div>
    </div>

    <script>
        function updatePrice() {
            var select = document.getElementById('billetSelect');
            var priceInput = document.getElementById('billetPriceDisplay');
            
            // On récupère l'option sélectionnée
            var selectedOption = select.options[select.selectedIndex];
            
            // On récupère l'attribut data-prix
            var price = selectedOption.getAttribute('data-prix');
            
            if (price) {
                priceInput.value = price + " €";
            } else {
                priceInput.value = "";
            }
        }
    </script>
</body>
</html>