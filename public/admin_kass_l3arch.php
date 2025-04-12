<?php
session_start();
require_once '../config/database.php';


// Vérifier si l'utilisateur est bien un admin tournoi et que son tournoi_id est 2 (Kass L3arch)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_tournoi' || $_SESSION['tournoi_id'] != 2) {
  header("Location: index.php");
  exit();
}

//  Récupérer les statistiques
$tournoi_id = 2; // ID du tournoi Kass L3arch

$nb_publications = $pdo->query("SELECT COUNT(*) FROM publications")->fetchColumn();
$nb_matchs = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE tournoi_id = ?");
$nb_matchs->execute([$tournoi_id]);
$nb_matchs = $nb_matchs->fetchColumn();

$nb_equipes = $pdo->prepare("SELECT COUNT(*) FROM equipes WHERE tournoi_id = ?");
$nb_equipes->execute([$tournoi_id]);
$nb_equipes = $nb_equipes->fetchColumn();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Kass L3arch - Dashboard</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
    <script src="https://kit.fontawesome.com/yourkitid.js" crossorigin="anonymous"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #212529;
        }

        /* Sidebar */
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

        .sidebar i {
            font-size: 18px;
        }

        .sidebar-header {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            color: white;
            margin-bottom: 20px;
        }

        .content {
            margin-left: 270px;
            padding: 30px;
            transition: 0.3s;
        }

        /* Statistiques */
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

        .stat-icon {
            font-size: 50px;
            opacity: 0.8;
        }

        .bg-blue { background: linear-gradient(135deg, #007bff, #0056b3); }
        .bg-green { background: linear-gradient(135deg, #28a745, #1c7c34); }
        .bg-yellow { background: linear-gradient(135deg, #ffc107, #d39e00); }

        /* Responsive */
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
            .content {
                margin-left: 120px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header"><i class="fas fa-trophy"></i> Admin Kass L3arch</div>
    <a href="admin_kass_l3arch.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="admin_publication_kassL3arch.php"><i class="fas fa-newspaper"></i> Gérer les Publications</a>
    <a href="admin_matchs_kass.php"><i class="fas fa-futbol"></i> Gérer les Matchs</a>
    <a href="admin_resultat_kass.php"><i class="fas fa-users"></i> Gérer scores</a>
    <hr class="text-white">
    <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
</div>

<!-- Contenu principal -->
<div class="content">
    <h2 class="text-center mb-4">Tableau de Bord - Kass L3arch</h2>

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
