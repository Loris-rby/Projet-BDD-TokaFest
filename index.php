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
    <link rel="stylesheet" href="css/style.css">
    <title>Projet</title>
</head>
<body>
    <header>
        <video autoplay muted loop>
        <source src="images/video.mp4" type="video/mp4">
        Votre navigateur ne supporte pas la vidéo HTML5.
        </video>
        <nav class="navbar navbar-expand-lg bg-transparent">
            <div class="container-fluid">
                <div class="navbar navbar-expand-xl">
                    <a class="navbar-brand" href="index.php">TokaFest</a>
                </div>
                <div class="btn-group dropstart">
                    <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="index.php#histoire">Présentation</a></li>
                        <li><a class="dropdown-item" href="index.php#themes">Images</a></li>
                        <li><a class="dropdown-item" href="Pages-Benevoles/InscriptionBenevole.php">Benevoles</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a class="dropdown-item" href="Pages-Admin/dashboard.php">Dashboard</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="Pages-Admin/login.php">Admin</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <p>Venez découvrir le Festival TokaFest en Dordogne !</p>
                </div>
            </div>
        </div>
    </div>
</header>

<section id="histoire">
    <br><br><br><h1>Présentation de TokaFest</h1>
    <div class="container-fluid">
        <!-- Première rangée : Paragraphe à gauche, Image à droite -->
        <div class="row">
            <div class="col-md-6 text-light paragrapheun">
                <p>
                    <br><b>✨ TokaFest : Le Spot Électro qui Réveille la Dordogne ! </b><br><br>
                    Attachez vos ceintures (et surtout vos baskets !) : le TokaFest, c'est bien plus qu'un simple festival,
                     c'est la pépite électro cachée en plein cœur du Périgord qui est en train de se faire un nom. Oubliez 
                     les gros mastodontes impersonnels ; ici, on est sur un événement à taille humaine, né de la passion de 
                     dingues de son qui voulaient faire vibrer la Dordogne autrement.<br>
                    Imaginez un week-end où le chant des oiseaux laisse place aux beats pulsés de la techno, de la house entraînante 
                    et de pépites micro-house qui font hocher la tête sans même s'en rendre compte. Le TokaFest, c'est le lieu de rendez-vous 
                    de ceux qui aiment la musique électronique pointue, celle qui fait voyager les esprits et bouger les corps jusqu'au lever du 
                    soleil. La programmation est toujours un savant mélange entre des têtes d'affiche que tout le monde veut voir, et des jeunes 
                    talents qui sont la relève de demain. C'est l'occasion parfaite de dire : "J'y étais avant que ça devienne trop connu !"
                </p>
            </div>
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <img src="images/1.jpeg" alt="img de paysage" class="img-fluid half-width">
            </div>
        </div>

        <!-- Deuxième rangée : Image à gauche, Paragraphe à droite -->
        <div class="row">
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <img src="images/5.jpeg" alt="img de paysage" class="img-fluid half-width">
            </div>
            <div class="col-md-6 text-light paragraphedeux">
                <p>
                    <br><br><br><b>Mais qui est la Tokazic Family ? </b><br><br>
                    Nous visons tous les acteurs engagés dans la protection de l’environnement, notamment :<br>
                    <b>Les citoyens engagés :</b> ceux qui souhaitent participer activement à des actions locales et
                    contribuer à leur communauté.<br>
                    Les associations environnementales : qui peuvent promouvoir leurs événements et recruter des bénévoles motivés.<br>
                    Les collectivités locales : qui peuvent coordonner et faire connaître les initiatives de leur territoire.<br><br>
                    <b>Notre engagement envers l'ODD 13 : Lutte contre le changement climatique</b><br><br>
                    Planète Locale s’inscrit dans la réalisation de l’Objectif de Développement Durable 13 en favorisant l’engagement
                    et la mobilisation citoyenne. Plus nous serons nombreux à agir, plus nos actions auront un impact fort et durable.
                    En rendant les initiatives locales plus visibles, Planète Locale contribue à construire une communauté consciente et
                    active dans la lutte contre le changement climatique.
                    Rejoignez-nous sur Planète Locale et devenez acteur du changement !
                </p>
            </div>
        </div>
    </div>
</section>
<section id="themes">
    <br><br><br><h2 class="titre-theme">Retours en images</h2>
    <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner"> 
            <div class="carousel-item active"> 
                <img src="images/16.jpg" class="d-block w-100" alt="image d'une action 1"> 
            </div>
            <div class="carousel-item">
                <img src="images/15.jpg" class="d-block w-100" alt="image d'une action 2">
            </div>
            <div class="carousel-item">
                <img src="images/17.jpg" class="d-block w-100" alt="image d'une action 3 ">
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span> 
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
            <br><br><br>
        </button>
    </div>
</section>

<!--Script Bootstrap-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>
</body>
</html>
