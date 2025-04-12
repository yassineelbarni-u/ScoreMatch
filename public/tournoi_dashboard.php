<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est un admin_tournoi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_tournoi') {
    header("Location: index.php");
    exit();
}

// Récupérer les statistiques liées au tournoi
$nb_publications = $pdo->query("SELECT COUNT(*) FROM publications")->fetchColumn();
$nb_matchs = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
$nb_equipes = $pdo->query("SELECT COUNT(*) FROM equipes")->fetchColumn();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Tournoi - Dashboard</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
    <script src="https://kit.fontawesome.com/yourkitid.js" crossorigin="anonymous"></script> <!-- FontAwesome -->

    <style>
        /* Importation de Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

body {
    font-family: 'Poppins', sans-serif;
    background-color: #f8f9fa;
    color: #212529;
}

/* Styles pour la Sidebar */
.sidebar {
    height: 100vh;
    width: 250px;
    position: fixed;
    top: 0;
    left: 0;
    background: linear-gradient(180deg, #212529 0%, #343a40 100%);
    padding-top: 20px;
    transition: 0.3s ease-in-out;
    box-shadow: 2px 0px 10px rgba(0, 0, 0, 0.2);
    overflow-y: auto;
}

.sidebar a {
    padding: 12px;
    text-decoration: none;
    font-size: 17px;
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: 0.3s;
    border-radius: 5px;
}

.sidebar a:hover {
    background-color: rgba(255, 255, 255, 0.1);
    padding-left: 18px;
    transition: 0.3s ease-in-out;
}

/* Style pour l'icône de la sidebar */
.sidebar i {
    font-size: 18px;
}

/* Style du titre */
.sidebar-header {
    text-align: center;
    font-size: 22px;
    font-weight: bold;
    color: white;
    margin-bottom: 20px;
}

/* Animation au chargement */
.sidebar a, .sidebar-header {
    opacity: 0;
    transform: translateX(-20px);
    animation: slideIn 0.6s ease-in-out forwards;
}

/* Effet d'animation pour les liens */
@keyframes slideIn {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Style du contenu principal */
.content {
    margin-left: 270px;
    padding: 30px;
    transition: 0.3s;
}

/* Style des cartes statistiques */
.stat-card {
    border-radius: 15px;
    padding: 25px;
    color: white;
    text-align: center;
    transition: all 0.3s ease-in-out;
    box-shadow: 3px 3px 15px rgba(0, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: scale(1.05);
    box-shadow: 3px 3px 20px rgba(0, 0, 0, 0.3);
}

/* Icônes dans les cartes */
.stat-icon {
    font-size: 50px;
    opacity: 0.8;
}

/* Effet d'animation */
.stat-card::before {
    content: "";
    position: absolute;
    top: -100px;
    left: -100px;
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.1);
    transform: rotate(45deg);
    transition: 0.5s;
}

.stat-card:hover::before {
    top: -50px;
    left: -50px;
}

/* Couleurs des cartes */
.bg-blue { background: linear-gradient(135deg, #007bff, #0056b3); }
.bg-green { background: linear-gradient(135deg, #28a745, #1c7c34); }
.bg-yellow { background: linear-gradient(135deg, #ffc107, #d39e00); }

/* Style du bouton de déconnexion */
.sidebar .text-danger {
    font-weight: bold;
    transition: all 0.3s ease-in-out;
}

.sidebar .text-danger:hover {
    background-color: rgba(255, 0, 0, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        width: 100px;
    }
    .sidebar a {
        font-size: 14px;
        padding: 10px;
        justify-content: center;
    }
    .sidebar i {
        font-size: 20px;
    }
    .sidebar .sidebar-header {
        font-size: 18px;
    }
    .content {
        margin-left: 120px;
        padding: 15px;
    }
}

    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header"><i class="fas fa-trophy"></i> Admin Tournoi</div>
    <a href="tournoi_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="admin_publication.php"><i class="fas fa-newspaper"></i> Gérer les Publications</a>
    <a href="admin_matchs.php"><i class="fas fa-futbol"></i> Gérer les Matchs</a>
    <a href="admin_resultat.php"><i class="fas fa-users"></i> Gérer scores</a>
    <hr class="text-white">
    <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
</div>

<!-- Contenu principal -->
<div class="content">
    <h2 class="text-center mb-4">Tableau de Bord - botola</h2>

    <!-- Statistiques -->
    <div class="row">
        <div class="col-md-4">
            <div class="card stat-card bg-blue">
                <div class="card-body">
                    <i class="fas fa-newspaper stat-icon"></i>
                    <h5 class="card-title">Publications</h5>
                    <p class="fs-3"><?= $nb_publications; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-green">
                <div class="card-body">
                    <i class="fas fa-futbol stat-icon"></i>
                    <h5 class="card-title">Matchs</h5>
                    <p class="fs-3"><?= $nb_matchs; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-yellow">
                <div class="card-body">
                    <i class="fas fa-users stat-icon"></i>
                    <h5 class="card-title">Équipes</h5>
                    <p class="fs-3"><?= $nb_equipes; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
