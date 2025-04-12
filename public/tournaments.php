<?php
session_start();
require_once '../config/database.php'; // Connexion à la base de données



// Récupérer tous les tournois de la base de données, y compris les informations de l'admin
$tournois = $pdo->query("
    SELECT t.*, u.nom AS admin_nom, u.email AS admin_email
    FROM tournois t
    LEFT JOIN users u ON t.admin_tournoi_id = u.id
    ORDER BY t.date_debut DESC
")->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tournois</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Arial', sans-serif;
        }
        .tournoi-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .tournoi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 8px 18px rgba(0, 0, 0, 0.15);
        }

        .tournoi-card h3 {
            color: #1d3557;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .tournoi-card .date {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 20px;
        }

        .tournoi-card .btn-primary {
            background-color: #ff6f00;
            border-color: #ff6f00;
            transition: background-color 0.3s ease;
        }

        .tournoi-card .btn-primary:hover {
            background-color: #ff8c00;
            border-color: #ff8c00;
        }

        .modal-content {
            background-color: #f8f9fa;
            border-radius: 10px;
        }

        .modal-header {
            background-color: #ff6f00;
            color: #fff;
            border-radius: 10px 10px 0 0;
        }

        .modal-title {
            font-weight: bold;
        }

        .modal-footer {
            border-top: none;
        }

        .container {
            margin-top: 50px;
        }

        .card-title {
            font-size: 1.5rem;
        }
    </style>
</head>
<body>

<!-- Inclure la barre de navigation -->
<?php include 'navbar.php'; ?>

<div class="container">
    <h2 class="text-center mb-5 text-primary">🏆 Nos Tournois</h2>



    <div class="row">
        <?php foreach ($tournois as $tournoi) : ?>
            <div class="col-md-4 mb-4">
                <div class="tournoi-card">
                    <h3><?= htmlspecialchars($tournoi['nom']) ?></h3>
                    <p class="date">Du <?= htmlspecialchars($tournoi['date_debut']) ?> au <?= htmlspecialchars($tournoi['date_fin']) ?></p>
                    <!-- Lien vers les matchs du tournoi -->
                    <?php if (strtolower($tournoi['nom']) == 'botola pro') : ?>
                      <a href="botola_matches.php?tournoi_id=<?= $tournoi['id'] ?>" class="btn btn-primary">Voir les matchs</a>
                      <?php else : ?>
                          <a href="kass_l3arch_matches.php?tournoi_id=<?= $tournoi['id'] ?>" class="btn btn-primary">Voir les matchs</a>
                      <?php endif; ?>

                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#viewTournoiModal<?= $tournoi['id'] ?>">Voir Détails</button>
                </div>
            </div>

            <!-- Modal Détails Tournoi -->
            <div class="modal fade" id="viewTournoiModal<?= $tournoi['id'] ?>" tabindex="-1" aria-labelledby="viewTournoiModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewTournoiModalLabel"><?= htmlspecialchars($tournoi['nom']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Nom :</strong> <?= htmlspecialchars($tournoi['nom']) ?></p>
                            <p><strong>Dates :</strong> Du <?= htmlspecialchars($tournoi['date_debut']) ?> au <?= htmlspecialchars($tournoi['date_fin']) ?></p>
                            <p><strong>Admin :</strong> <?= !empty($tournoi['admin_nom']) ? htmlspecialchars($tournoi['admin_nom']) : 'Non défini' ?></p>
                            <p><strong>Email :</strong> <?= !empty($tournoi['admin_email']) ? htmlspecialchars($tournoi['admin_email']) : 'Non défini' ?></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
