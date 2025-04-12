<?php
session_start();
require_once '../config/database.php';

$date_filter = isset($_GET['date']) ? $_GET['date'] : null;

// Requête SQL pour récupérer les résultats triés par date
$query = "SELECT m.*, 
                 e1.nom AS equipe1, e2.nom AS equipe2, 
                 e1.logo AS logo1, e2.logo AS logo2,
                 m.score_equipe1, m.score_equipe2,
                 DATE(m.date_match) AS match_date
          FROM matches m
          JOIN equipes e1 ON m.equipe1_id = e1.id
          JOIN equipes e2 ON m.equipe2_id = e2.id
          WHERE m.score_equipe1 IS NOT NULL AND m.score_equipe2 IS NOT NULL";

if ($date_filter) {
    $query .= " AND DATE(m.date_match) = :date_filter";
}

$query .= " ORDER BY m.date_match DESC";

$stmt = $pdo->prepare($query);

if ($date_filter) {
    $stmt->bindParam(':date_filter', $date_filter);
}

$stmt->execute();
$matchs_resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats des Matchs</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="../public/assets/css/resultats.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-4">
    <h2 class="text-center">📅 Historique des Résultats</h2>
    
    <!-- Sélection de la date -->
    <form method="GET" id="dateFilterForm" class="text-center mb-3">
        <label for="filter_date" class="fw-bold">📅 Sélectionner une date :</label>
        <input type="date" id="filter_date" name="date" class="form-control d-inline-block w-auto" value="<?= isset($_GET['date']) ? $_GET['date'] : '' ?>">
        <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
    </form>

    <div class="match-list">
        <?php
        if (empty($matchs_resultats)) {
            echo "<p class='text-center text-muted'>Aucun match trouvé pour cette date.</p>";
        }

        $last_date = null;

        foreach ($matchs_resultats as $match) {
            $current_date = date('d/m/Y', strtotime($match['match_date']));

            // Affichage de la date comme titre si elle change
            if ($current_date !== $last_date) {
                echo "<div class='date-separator'>📅 " . $current_date . "</div>";
                $last_date = $current_date;
            }
            ?>
            <div class="match-item" onclick="goToDetails(<?= $match['id'] ?>)">
                <div class="match-info">
                <span class="match-date">
    <?= substr($match['heure'], 0, 5); ?>
</span>

                </div>
                <div class="match-content">
                    <div class="team">
                        <img src="<?= htmlspecialchars($match['logo1']) ?>" alt="Équipe 1">
                    </div>
                    <div class="match-score">
                        <?= htmlspecialchars($match['score_equipe1']) ?> - <?= htmlspecialchars($match['score_equipe2']) ?>
                    </div>
                    <div class="team">
                        <img src="<?= htmlspecialchars($match['logo2']) ?>" alt="Équipe 2">
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<script>
function goToDetails(matchId) {
    window.location.href = "detail_resultat.php?match_id=" + matchId;
}
</script>

</body>
</html>
