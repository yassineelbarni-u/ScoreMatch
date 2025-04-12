<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin_global') {
    header("Location: index.php");
    exit();
}

require_once '../config/database.php';

// Récupérer les statistiques
$nb_equipes = $pdo->query("SELECT COUNT(*) FROM equipes")->fetchColumn();
$nb_matchs = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
$nb_staff = $pdo->query("SELECT COUNT(*) FROM staff")->fetchColumn();
$nb_joueurs = $pdo->query("SELECT COUNT(*) FROM joueurs")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Global - Dashboard</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
    <script src="https://kit.fontawesome.com/yourkitid.js" crossorigin="anonymous"></script> <!-- FontAwesome -->
    <style>
        /* Styles pour la sidebar */
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
.bg-red { background: linear-gradient(135deg, #dc3545, #a71d2a); }

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
    <div class="sidebar-header"><i class="fas fa-futbol"></i> Admin Global</div>
    <a href="admin_equipes.php"><i class="fas fa-users"></i> Gérer les Équipes</a>

    <a href="admin_staff.php"><i class="fas fa-user-tie"></i> Gérer le Staff</a>
    <a href="admin_joueurs.php"><i class="fas fa-user"></i> Gérer les Joueurs</a>
    <a href="admin_arbitres.php"><i class="fas fa-trophy"></i> Gérer les arbites</a>
    <a href="admin_tournois.php"><i class="fas fa-street-view"></i> Gérer tournois</a>
    <a href="admin_stades.php"><i class="fas fa-futbol"></i> Gérer les stades</a>
    <a href="admin_compte.php"><i class="fas fa-user-shield"></i> Gérer les Admins Tournoi</a>
    <a href="admin_user.php"><i class="fas fa-users"></i> Gérer les Utilisateurs</a>


    <hr class="text-white">
    <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Se Déconnecter</a>
</div>



<!-- Contenu principal -->
<div class="content">
    <h2 class="text-center mb-4">Tableau de Bord - Admin Global</h2>

    <!-- Statistiques -->
    <div class="row">
        <div class="col-md-3">
            <div class="card stat-card bg-blue">
                <div class="card-body">
                    <i class="fas fa-users stat-icon"></i>
                    <h5 class="card-title">Équipes</h5>
                    <p class="fs-3"><?= $nb_equipes; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-green">
                <div class="card-body">
                    <i class="fas fa-futbol stat-icon"></i>
                    <h5 class="card-title">Matchs</h5>
                    <p class="fs-3"><?= $nb_matchs; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-yellow">
                <div class="card-body">
                    <i class="fas fa-user-tie stat-icon"></i>
                    <h5 class="card-title">Staff</h5>
                    <p class="fs-3"><?= $nb_staff; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-red">
                <div class="card-body">
                    <i class="fas fa-user stat-icon"></i>
                    <h5 class="card-title">Joueurs</h5>
                    <p class="fs-3"><?= $nb_joueurs; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function updateMatchCount() {
        fetch("get_match_count.php")
            .then(response => response.json())
            .then(data => {
                document.getElementById("match-count").innerText = data.count;
            })
            .catch(error => console.error("Erreur lors de la récupération des matchs :", error));
    }

    // Mettre à jour toutes les 5 secondes
    setInterval(updateMatchCount, 5000);
</script>

</body>
</html>
