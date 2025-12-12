<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx"
          crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Styles spécifiques pour la page d'inscription */
        .inscription-container {
            max-width: 600px;
            margin: 50px auto;
            background-color: rgba(10, 0, 30, 0.5);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(123, 97, 255, 0.2);
            border: 1px solid rgba(123, 97, 255, 0.3);
        }

        .inscription-container h2 {
            color: white;
            margin-bottom: 30px;
            text-align: center;
            font-family: 'Playfair Display', serif;
            font-size: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            color: #FF6FA3;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            background-color: rgba(30, 10, 60, 0.8);
            border: 1px solid rgba(123, 97, 255, 0.4);
            color: white;
            border-radius: 5px;
            padding: 12px;
            transition: all 0.3s;
        }

        .form-control:focus {
            background-color: rgba(30, 10, 60, 0.9);
            border-color: #7B61FF;
            color: white;
            box-shadow: 0 0 8px rgba(123, 97, 255, 0.3);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .btn-submit {
            background-color: #7B61FF;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
            margin-top: 20px;
        }
        .form-text {
            color: rgba(255, 111, 163, 0.8);
            font-size: 0.85rem;
            margin-top: 5px;
        }


        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #4AA3FF;
            text-decoration: none;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #FF6FA3;
            text-decoration: underline;
        }

        /* Header spécifique pour cette page */
        .inscription-header {
            background: linear-gradient(135deg, rgba(10, 0, 30, 0.8) 0%, rgba(123, 97, 255, 0.1) 100%);
            padding: 40px 0;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(123, 97, 255, 0.3);
        }

        .inscription-header h1 {
            color: white;
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            margin: 0;
        }

        .inscription-header p {
            color: #FF6FA3;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
    <title>Inscription Bénévoles - TokaFest</title>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg bg-transparent">
        <div class="container-fluid">
            <div class="navbar navbar-expand-xl">
                <a class="navbar-brand" href="../index.php">TokaFest</a>
            </div>
            <div class="btn-group dropstart">
                <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="../index.php#histoire">Présentation</a></li>
                    <li><a class="dropdown-item" href="../index.php#themes">Images</a></li>
                    <li><a class="dropdown-item" href="InscriptionBenevole.php">Bénévoles</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a class="dropdown-item" href="../Pages-Admin/dashboard.php">Dashboard</a></li>
                    <?php else: ?>
                        <li><a class="dropdown-item" href="../Pages-Admin/login.php">Admin</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header spécifique -->
    <div class="inscription-header">
        <h1>Rejoignez-nous en tant que Bénévole</h1>
        <p>Aidez-nous à créer une expérience inoubliable pour tous !</p>
    </div>

    

        <h2>Veuillez contacter nos responsables Bénévoles du festival :</h2>
        <p> 07 88 90 56 64</p>

        <div class="back-link">
            <a href="../index.php">← Retour à l'accueil</a>
        </div>


    <!-- Script Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
            crossorigin="anonymous"></script>

    </script>
</body>
</html>