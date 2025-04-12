<?php
session_start();
require_once '../config/database.php';

// Vérifier si un match est sélectionné
if (!isset($_GET['match_id'])) {
  die("Match non trouvé !");
}

$match_id = intval($_GET['match_id']);

// Récupérer les détails du match (avec les ID des équipes)
$query = "SELECT m.*, 
               e1.id AS equipe1_id, e2.id AS equipe2_id,
               e1.nom AS equipe1, e2.nom AS equipe2, 
               e1.logo AS logo1, e2.logo AS logo2,
               m.score_equipe1, m.score_equipe2,
               m.possession_equipe1, m.possession_equipe2,
               m.tirs_equipe1, m.tirs_equipe2,
               m.tirs_cadres_equipe1, m.tirs_cadres_equipe2,
               m.corners_equipe1, m.corners_equipe2,
               m.fautes_equipe1, m.fautes_equipe2,
               m.passes_equipe1, m.passes_equipe2,
               m.interventions_gardien_equipe1, m.interventions_gardien_equipe2,
               m.cartons_jaunes_equipe1, m.cartons_jaunes_equipe2,
               m.cartons_rouges_equipe1, m.cartons_rouges_equipe2,
               m.touches_equipe1, m.touches_equipe2,
               m.tirs_bloques_equipe1, m.tirs_bloques_equipe2
        FROM matches m
        JOIN equipes e1 ON m.equipe1_id = e1.id
        JOIN equipes e2 ON m.equipe2_id = e2.id
        WHERE m.id = ?";


$stmt = $pdo->prepare($query);
$stmt->execute([$match_id]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
  die("Match introuvable !");
}

// Récupérer les événements (buts et cartons) avec les équipes et les joueurs
$query_events = "SELECT e.*, j.nom AS joueur, e.equipe_id
               FROM match_events e
               JOIN joueurs j ON e.joueur_id = j.id
               WHERE e.match_id = ?
               ORDER BY COALESCE(e.minute_but, e.minute_carton) ASC";

$stmt_events = $pdo->prepare($query_events);
$stmt_events->execute([$match_id]);
$evenements = $stmt_events->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du Match</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-4">
    <h2 class="text-center">📋 Détails du Match</h2>

    <div class="card shadow">
        <div class="card-header text-center bg-dark text-white">
            <strong><?= htmlspecialchars($match['equipe1']) ?> 🆚 <?= htmlspecialchars($match['equipe2']) ?></strong>
        </div>
        <div class="card-body text-center">
            <div class="row align-items-center">
                <div class="col-4">
                    <img src="<?= htmlspecialchars($match['logo1']) ?>" class="img-fluid" alt="Équipe 1">
                </div>
                <div class="col-4">
                    <h3><?= htmlspecialchars($match['score_equipe1']) ?> - <?= htmlspecialchars($match['score_equipe2']) ?></h3>
                    <p class="text-muted">
                 <?= date('d/m/Y', strtotime($match['date_match'])) . " " . substr($match['heure'], 0, 5); ?>
               </p>
                </div>
                <div class="col-4">
                    <img src="<?= htmlspecialchars($match['logo2']) ?>" class="img-fluid" alt="Équipe 2">
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-4">⚽ Buteurs</h3>
<div class="row">
    <!-- Buteurs de l'équipe 1 -->
    <div class="col-md-6">
        <h4 class="text-center"><?= htmlspecialchars($match['equipe1']) ?></h4>
        <ul class="list-group">
            <?php
            $hasGoalsEquipe1 = false;
            foreach ($evenements as $event) {
                if ($event['type_event'] === 'but' && $event['equipe_id'] == $match['equipe1_id']) {
                    $hasGoalsEquipe1 = true;
                    echo "<li class='list-group-item'>⏱️ [Minute " . $event['minute_but'] . "] ⚽ " . $event['joueur'] . " a marqué !</li>";
                }
            }
            if (!$hasGoalsEquipe1) {
                echo "<li class='list-group-item text-muted'>Aucun but marqué.</li>";
            }
            ?>
        </ul>
    </div>

    <!-- Buteurs de l'équipe 2 -->
    <div class="col-md-6">
        <h4 class="text-center"><?= htmlspecialchars($match['equipe2']) ?></h4>
        <ul class="list-group">
            <?php
            $hasGoalsEquipe2 = false;
            foreach ($evenements as $event) {
                if ($event['type_event'] === 'but' && $event['equipe_id'] == $match['equipe2_id']) {
                    $hasGoalsEquipe2 = true;
                    echo "<li class='list-group-item'>⏱️ [Minute " . $event['minute_but'] . "] ⚽ " . $event['joueur'] . " a marqué !</li>";
                }
            }
            if (!$hasGoalsEquipe2) {
                echo "<li class='list-group-item text-muted'>Aucun but marqué.</li>";
            }
            ?>
        </ul>
    </div>
</div>



<h3 class="mt-4">Cartons</h3>
<div class="row">
    <!-- Cartons de l'équipe 1 -->
    <div class="col-md-6">
        <h4 class="text-center"><?= htmlspecialchars($match['equipe1']) ?></h4>
        <ul class="list-group">
            <?php
            $hasCardsEquipe1 = false;
            foreach ($evenements as $event) {
                if ($event['type_event'] === 'carton' && $event['equipe_id'] == $match['equipe1_id']) {
                    $hasCardsEquipe1 = true;
                    echo "<li class='list-group-item'>⏱️ [Minute " . $event['minute_carton'] . "] " . $event['joueur'] . " a reçu un carton " . ucfirst($event['carton']) . ".</li>";

                }
            }
            if (!$hasCardsEquipe1) {
                echo "<li class='list-group-item text-muted'>Aucun carton reçu.</li>";
            }
            ?>
        </ul>
    </div>

    <!-- Cartons de l'équipe 2 -->
    <div class="col-md-6">
        <h4 class="text-center"><?= htmlspecialchars($match['equipe2']) ?></h4>
        <ul class="list-group">
            <?php
            $hasCardsEquipe2 = false;
            foreach ($evenements as $event) {
                if ($event['type_event'] === 'carton' && $event['equipe_id'] == $match['equipe2_id']) {
                    $hasCardsEquipe2 = true;
                    echo "<li class='list-group-item'>⏱️ [Minute " . $event['minute_carton'] . "] " . $event['joueur'] . " a reçu un carton " . ucfirst($event['carton']) . ".</li>";
                }
            }
            if (!$hasCardsEquipe2) {
                echo "<li class='list-group-item text-muted'>Aucun carton reçu.</li>";
            }
            ?>
        </ul>
    </div>
</div>



<h3 class="mt-4">📊 Statistiques du Match</h3>
<div class="table-responsive">
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>Statistique</th>
                <th><?= htmlspecialchars($match['equipe1']) ?></th>
                <th><?= htmlspecialchars($match['equipe2']) ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Possession de balle (%)</td>
                <td><?= $match['possession_equipe1'] ?>%</td>
                <td><?= $match['possession_equipe2'] ?>%</td>
            </tr>
            <tr>
                <td>Tirs</td>
                <td><?= $match['tirs_equipe1'] ?></td>
                <td><?= $match['tirs_equipe2'] ?></td>
            </tr>
            <tr>
                <td>Tirs cadrés</td>
                <td><?= $match['tirs_cadres_equipe1'] ?></td>
                <td><?= $match['tirs_cadres_equipe2'] ?></td>
            </tr>
            <tr>
                <td>Corners</td>
                <td><?= $match['corners_equipe1'] ?></td>
                <td><?= $match['corners_equipe2'] ?></td>
            </tr>
            <tr>
                <td>Fautes</td>
                <td><?= $match['fautes_equipe1'] ?></td>
                <td><?= $match['fautes_equipe2'] ?></td>
            </tr>
            <tr>
                <td>Passes complétées</td>
                <td><?= $match['passes_equipe1'] ?></td>
                <td><?= $match['passes_equipe2'] ?></td>
            </tr>
            <tr>
                <td>Interventions du gardien</td>
                <td><?= $match['interventions_gardien_equipe1'] ?></td>
                <td><?= $match['interventions_gardien_equipe2'] ?></td>
            </tr>
            <tr>
                <td>Cartons jaunes</td>
                <td><?= $match['cartons_jaunes_equipe1'] ?></td>
                <td><?= $match['cartons_jaunes_equipe2'] ?></td>
            </tr>
            <tr>
                <td>Cartons rouges</td>
                <td><?= $match['cartons_rouges_equipe1'] ?></td>
                <td><?= $match['cartons_rouges_equipe2'] ?></td>
            </tr>
            <tr>
                <td>Touches</td>
                <td><?= $match['touches_equipe1'] ?></td>
                <td><?= $match['touches_equipe2'] ?></td>
            </tr>
            <tr>
                <td>Tirs bloqués</td>
                <td><?= $match['tirs_bloques_equipe1'] ?></td>
                <td><?= $match['tirs_bloques_equipe2'] ?></td>
            </tr>
        <tr>
            <th>Pénaltys concédés</th>
            <td><?= $match['penaltys_concedes_equipe1'] ?></td>
            <td><?= $match['penaltys_concedes_equipe2'] ?></td>
        </tr>
            
        <tr>
            <th>Pénaltys réussis</th>
            <td><?= $match['penaltys_reussis_equipe1'] ?></td>
            <td><?= $match['penaltys_reussis_equipe2'] ?></td>
        </tr>
        <tr>
            <th>Hors-jeu</th>
            <td><?= $match['hors_jeu_equipe1'] ?></td>
            <td><?= $match['hors_jeu_equipe2'] ?></td>
        </tr>


        </tbody>
    </table>
</div>




    <a href="resultats.php" class="btn btn-secondary mt-4">⬅ Retour aux résultats</a>
</div>

</body>
</html>
