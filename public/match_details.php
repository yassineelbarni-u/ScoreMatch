<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'ID du match est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: matches.php");
    exit();
}

$match_id = $_GET['id'];

// Récupérer les détails du match
$query = "
    SELECT m.id, m.date_match, m.heure, 
           m.equipe1_id, m.equipe2_id,  -- Ajout de ces colonnes
           e1.nom AS equipe1, e1.logo AS logo1, 
           e2.nom AS equipe2, e2.logo AS logo2, 
           s.nom AS stade, s.ville, s.capacite, s.image AS stade_logo
    FROM matches m
    JOIN equipes e1 ON m.equipe1_id = e1.id
    JOIN equipes e2 ON m.equipe2_id = e2.id
    LEFT JOIN stades s ON m.stade_id = s.id
    WHERE m.id = ?
";


$stmt = $pdo->prepare($query);
$stmt->execute([$match_id]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
    echo "<p>Match introuvable.</p>";
    exit();
}

// Récupérer le nombre total de matchs joués dans le tournoi
$total_matchs = $pdo->query("SELECT COUNT(*) AS total FROM matches")->fetch(PDO::FETCH_ASSOC)['total'];

// Récupérer le nombre de matchs joués par chaque équipe
$query = "
    SELECT e.nom, COUNT(m.id) AS matchs_joues
    FROM matches m
    JOIN equipes e ON e.id = m.equipe1_id OR e.id = m.equipe2_id
    WHERE e.id IN (?, ?)
    GROUP BY e.nom
";
$stmt = $pdo->prepare($query);
$stmt->execute([$match['equipe1'], $match['equipe2']]);
$matchs_par_equipe = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php

    // Récupérer les joueurs de l'équipe 1
    try {
        // Récupérer les joueurs de l'équipe 1 du match

// Récupérer les joueurs sélectionnés pour l'équipe 1 avec leur position
$stmt_joueurs_equipe1 = $pdo->prepare("
    SELECT j.id, j.nom, j.prenom, mj.position
    FROM joueurs j
    JOIN match_joueurs mj ON j.id = mj.joueur_id
    WHERE mj.match_id = ? AND j.equipe_id = ?
    ORDER BY j.nom
");
$stmt_joueurs_equipe1->execute([$match_id, $match['equipe1_id']]);
$joueurs_equipe1 = $stmt_joueurs_equipe1->fetchAll(PDO::FETCH_ASSOC);


// Récupérer les joueurs sélectionnés pour l'équipe 2 avec leur position
$stmt_joueurs_equipe2 = $pdo->prepare("
    SELECT j.id, j.nom, j.prenom, mj.position
    FROM joueurs j
    JOIN match_joueurs mj ON j.id = mj.joueur_id
    WHERE mj.match_id = ? AND j.equipe_id = ?
    ORDER BY j.nom
");
$stmt_joueurs_equipe2->execute([$match_id, $match['equipe2_id']]);
$joueurs_equipe2 = $stmt_joueurs_equipe2->fetchAll(PDO::FETCH_ASSOC);


  } catch (PDOException $e) {
      die("Erreur lors de la récupération des joueurs : " . $e->getMessage());
  }

  function afficherJoueursDynamiques($joueurs, $positions) {
    $joueursFiltrés = array_filter($joueurs, function ($joueur) use ($positions) {
        return in_array($joueur['position'], $positions);
    });

    $output = "";
    foreach ($joueursFiltrés as $joueur) {
        $output .= "<div class='joueur'>" . htmlspecialchars($joueur['nom']) . "</div>";
    }

    return $output;
}
function getSchemaTactique($joueurs) {
    $count = [
        "Gardien" => 0,
        "Défenseur gauche" => 0,
        "Défenseur central" => 0,
        "Défenseur droit" => 0,
        "Milieu défensif" => 0,
        "Milieu central" => 0,
        "Milieu offensif" => 0,
        "Ailier gauche" => 0,
        "Ailier droit" => 0,
        "Attaquant" => 0
    ];

    foreach ($joueurs as $joueur) {
        if (isset($count[$joueur['position']])) {
            $count[$joueur['position']]++;
        }
    }

    // Calculer le schéma de jeu en fonction du nombre de joueurs par ligne
    return [
        "gardien" => $count["Gardien"],
        "defense" => $count["Défenseur gauche"] + $count["Défenseur central"] + $count["Défenseur droit"],
        "milieu" => $count["Milieu défensif"] + $count["Milieu central"] + $count["Milieu offensif"],
        "attaque" => $count["Ailier gauche"] + $count["Ailier droit"] + $count["Attaquant"]
    ];
}
  
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Détails du Match</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">

    <style>
      /* 🌙 Mode Sombre */
.dark-mode {
    background-color: #121212;
    color: white;
}

.dark-mode .card {
    background-color: #1e1e1e;
    color: white;
    border: 1px solid #444;
}

.dark-mode .text-muted {
    color: #bbb !important;
}

.dark-mode .table {
    background-color: #1c1c1c;
    color: white;
    border: 1px solid #444;
}

.dark-mode .table thead {
    background-color: #333;
    color: white;
}

.dark-mode .btn-secondary {
    background-color: #444;
    border-color: #444;
}

.dark-mode .btn-primary {
    background-color: #ff5722;
    border-color: #ff5722;
}

.dark-mode .btn-primary:hover {
    background-color: #e64a19;
    border-color: #e64a19;
}

.dark-mode input {
    background-color: #1e1e1e;
    color: white;
    border: 1px solid #444;
}

.dark-mode input::placeholder {
    color: #bbb;
}

.dark-mode img {
    filter: brightness(0.8);
}


/* 🌱 Terrain de football */
.terrain {
    width: 100%;
    height: 500px;
    background: #2e7d32;
    border-radius: 15px;
    position: relative;
    margin: 20px auto;
    padding: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* 🌟 Zone interne du terrain */
.terrain-inner {
    width: 95%;
    height: 95%;
    background: #388e3c;
    border-radius: 15px;
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    align-items: center;
    position: relative;
    padding: 10px 0;
}

/* 📍 Disposition des joueurs */
.ligne {
    display: flex;
    justify-content: center;
    width: 100%;
}

/* Ajustement des lignes */
.ligne.gardien { margin-top: 10px; }
.ligne.defense { margin-top: 30px; }
.ligne.milieu { margin-top: 30px; }
.ligne.attaque { margin-top: 30px; }

/* 📌 Style des joueurs */
.joueur {
    background: white;
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: bold;
    text-align: center;
    color: black;
    min-width: 100px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
}

/* 🎯 Spécial Gardien */
.joueur.gardien {
    background: #ff5722;
    color: white;
}

/* 📌 Emplacement vide */
.joueur.vide {
    background: transparent;
    box-shadow: none;
    visibility: hidden;
    width: 100px;
}

/* Style pour les logos */
.team-logo {
    max-width: 150px;
    height: auto;
    margin: 1rem auto;
    transition: transform 0.3s ease;
}

.team-logo:hover {
    transform: scale(1.05);
}

/* Style de la carte principale */
.main-match-card {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Style du score */
.score-display {
    font-size: 3.5rem;
    font-weight: bold;
    color: #2c3e50;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

/* Style des statistiques */
.stats-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.stat-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: bold;
}

/* Style des événements */
.event-card {
    border-left: 4px solid;
    transition: transform 0.2s ease;
}

.event-card:hover {
    transform: translateX(5px);
}

.goal-event {
    border-color: #28a745;
    background-color: rgba(40, 167, 69, 0.1);
}

.yellow-card {
    border-color: #ffc107;
    background-color: rgba(255, 193, 7, 0.1);
}

.red-card {
    border-color: #dc3545;
    background-color: rgba(220, 53, 69, 0.1);
}

/* Responsive design */
@media (max-width: 768px) {
    .score-display {
        font-size: 2.5rem;
    }
    
    .team-logo {
        max-width: 100px;
    }
}
</style>



    </style>
</head>
<body class="<?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-mode' : '' ?>">

  <!-- Inclure la barre de navigation -->
<?php include 'navbar.php'; ?>



<!-- Détails du match -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Détails du Match</h2>

        <div class="card shadow p-4">
            <div class="text-center">
                <p class="text-muted"><strong>Date :</strong> <?= htmlspecialchars($match['date_match']) ?></p>
                <p class="text-muted"><strong>Heure :</strong> <?= htmlspecialchars($match['heure']) ?></p>
            </div>

            <div class="row text-center align-items-center">
                <div class="col-md-4">
                    <img src="<?= htmlspecialchars($match['logo1']) ?>" alt="<?= htmlspecialchars($match['equipe1']) ?>" width="100">
                    <h4><?= htmlspecialchars($match['equipe1']) ?></h4>
                </div>
                <div class="col-md-4">
                    <h3>VS</h3>
                </div>
                <div class="col-md-4">
                    <img src="<?= htmlspecialchars($match['logo2']) ?>" alt="<?= htmlspecialchars($match['equipe2']) ?>" width="100">
                    <h4><?= htmlspecialchars($match['equipe2']) ?></h4>
                </div>
            </div>
        <div class="text-center mt-4">

        <h5><strong>Stade :</strong> <?= htmlspecialchars($match['stade']) ?> (<?= htmlspecialchars($match['ville']) ?>)</h5>
        <p><strong>Capacité :</strong> <?= number_format($match['capacite']) ?> spectateurs</p>

       <?php if (!empty($match['stade_logo'])) : ?>
            <div class="mt-3">
               <img src="<?= htmlspecialchars($match['stade_logo']) ?>" alt="Logo du stade" width="150" class="img-fluid rounded">
            </div>
      <?php endif; ?>
  </div>
        </div>

        <!-- Statistiques -->
        <div class="card shadow p-4 mt-4">
            <h3 class="text-center mb-4">Statistiques du Match</h3>
            <ul>
                <li><strong>Nombre total de matchs dans le tournoi :</strong> <?= $total_matchs ?></li>
                <?php foreach ($matchs_par_equipe as $stat) : ?>
                    <li><strong><?= htmlspecialchars($stat['nom']) ?> :</strong> <?= $stat['matchs_joues'] ?> matchs joués</li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>



<!-- Liste des joueurs de chaque équipe -->
<section class="py-5">
    <div class="container">
        <h3 class="text-center mb-4"> Joueurs des Équipes</h3>

        <div class="row">
            <!-- Joueurs de l'équipe 1 -->
            <div class="col-md-6">
                <h4 class="text-center"><?= htmlspecialchars($match['equipe1']) ?></h4>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Nom du joueur</th> <!-- Seulement le nom avec un lien -->
                                <th>position</th>
                            </tr>
                            
                        </thead>
                        <tbody>
    <?php if (!empty($joueurs_equipe1)) : ?>
        <?php foreach ($joueurs_equipe1 as $joueur) : ?>
            <tr>
                <td>
                    <a href="joueur_details.php?id=<?= htmlspecialchars($joueur['id']) ?>" 
                       class="text-decoration-none">
                        <?= htmlspecialchars($joueur["nom"]) . " " . htmlspecialchars($joueur["prenom"]) ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($joueur["position"]) ?></td> <!-- Ajout de la position -->
            </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr><td colspan="2" class="text-center">Aucun joueur trouvé.</td></tr>
    <?php endif; ?>
</tbody>

                    </table>
                </div>
            </div>

            <!-- Joueurs de l'équipe 2 -->
            <div class="col-md-6">
                <h4 class="text-center"><?= htmlspecialchars($match['equipe2']) ?></h4>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Nom du joueur</th> <!-- Seulement le nom avec un lien -->
                                <th>position</th>
                            </tr>
                        </thead>
                        <tbody>
    <?php if (!empty($joueurs_equipe2)) : ?>
        <?php foreach ($joueurs_equipe2 as $joueur) : ?>
            <tr>
                <td>
                    <a href="joueur_details.php?id=<?= htmlspecialchars($joueur['id']) ?>" 
                       class="text-decoration-none">
                        <?= htmlspecialchars($joueur["nom"]) . " " . htmlspecialchars($joueur["prenom"]) ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($joueur["position"]) ?></td> <!-- Affichage de la position -->
            </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr><td colspan="2" class="text-center">Aucun joueur trouvé.</td></tr>
    <?php endif; ?>
              </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$schema_equipe1 = getSchemaTactique($joueurs_equipe1);
$schema_equipe2 = getSchemaTactique($joueurs_equipe2);
?>




<!-- 📌 Affichage des terrains pour les deux équipes -->
<section class="py-5">
    <div class="container">
        <h3 class="text-center mb-4">Disposition des Joueurs sur le Terrain</h3>
        <div class="row">
            <!-- Terrain Équipe 1 -->
            <div class="col-md-6">
                <h4 class="text-center"><?= htmlspecialchars($match['equipe1']) ?></h4>
                <div class="terrain">
                    <div class="terrain-inner">
                        <div class="ligne gardien">
                            <?= afficherJoueursDynamiques($joueurs_equipe1, ["Gardien"]) ?>
                        </div>
                        <div class="ligne defense">
                            <?= afficherJoueursDynamiques($joueurs_equipe1, ["Défenseur gauche", "Défenseur central", "Défenseur droit"]) ?>
                        </div>
                        <div class="ligne milieu">
                            <?= afficherJoueursDynamiques($joueurs_equipe1, ["Milieu défensif", "Milieu central", "Milieu offensif"]) ?>
                        </div>
                        <div class="ligne attaque">
                            <?= afficherJoueursDynamiques($joueurs_equipe1, ["Ailier gauche", "Ailier droit", "Attaquant"]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terrain Équipe 2 -->
            <div class="col-md-6">
                <h4 class="text-center"><?= htmlspecialchars($match['equipe2']) ?></h4>
                <div class="terrain">
                    <div class="terrain-inner">
                        <div class="ligne gardien">
                            <?= afficherJoueursDynamiques($joueurs_equipe2, ["Gardien"]) ?>
                        </div>
                        <div class="ligne defense">
                            <?= afficherJoueursDynamiques($joueurs_equipe2, ["Défenseur gauche", "Défenseur central", "Défenseur droit"]) ?>
                        </div>
                        <div class="ligne milieu">
                            <?= afficherJoueursDynamiques($joueurs_equipe2, ["Milieu défensif", "Milieu central", "Milieu offensif"]) ?>
                        </div>
                        <div class="ligne attaque">
                            <?= afficherJoueursDynamiques($joueurs_equipe2, ["Ailier gauche", "Ailier droit", "Attaquant"]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Pied de page -->
<footer class="bg-dark text-white text-center py-3">
    <p class="mb-0">&copy; 2025 Gestion des Matchs - Tous droits réservés.</p>
</footer>

<!-- Bootstrap JS -->
<script src="../bootstrap-5.3.3-dist/js/bootstrap.js"></script>

</body>
</html>
