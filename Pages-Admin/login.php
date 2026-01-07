<?php
session_start();

require_once '../Classes/Connexion.php'; 

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $connexion = Connexion::getInstance();
        $manager = $connexion->getManager();
        $dbName = $connexion->getDbName();

        $query = new MongoDB\Driver\Query(['username' => $username]);
        
        $cursor = $manager->executeQuery("$dbName.admins", $query);
        $user = current($cursor->toArray());

        if ($user && password_verify($password, $user->password)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = $user->username;
            header("Location: dashboard.php");
            exit;
        } else {
            $message = "Identifiants incorrects.";
        }
    } catch (Exception $e) {
        $message = "Erreur technique lors de la connexion.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Login Admin - TokaFest</title>
    
    <link rel="stylesheet" href="../css/admin.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    
    <div class="login-wrapper">
        <div class="login-box">
            <h2>Admin</h2>
            <form method="post">
                <div class="form-group">
                    <label class="form-label">Identifiant</label>
                    <input type="text" name="username" class="form-input" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <button type="submit" class="btn-admin">Se connecter</button>
            </form>
            
            <div style="margin-top: 20px;">
                <a href="../index.php" style="color: #888; text-decoration: none; font-size: 0.9em;">← Retour au site</a>
            </div>
        </div>
    </div>

</body>
</html>