<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Récupérer tous les matchs qui n'ont pas encore commencé
$query = "
    SELECT m.id, e1.nom AS equipe1, e2.nom AS equipe2, 
           e1.logo AS logo1, e2.logo AS logo2, 
           m.date_match, m.heure, e1.id AS equipe1_id, e2.id AS equipe2_id
    FROM matches m
    JOIN equipes e1 ON m.equipe1_id = e1.id
    JOIN equipes e2 ON m.equipe2_id = e2.id
    LEFT JOIN votes v ON v.match_id = m.id AND v.user_id = ?
    WHERE (m.date_match > CURDATE() OR (m.date_match = CURDATE() AND m.heure > CURTIME()))
          AND v.id IS NULL
    ORDER BY m.date_match ASC, m.heure ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$matchs = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Vérifier si un vote est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['match_id'], $_POST['equipe_votee_id']) && $isLoggedIn) {
    $matchId = intval($_POST['match_id']);
    $equipeVoteeId = intval($_POST['equipe_votee_id']);

    // Vérifier si l'utilisateur a déjà voté pour ce match
    $stmt = $pdo->prepare("SELECT * FROM votes WHERE user_id = ? AND match_id = ?");
    $stmt->execute([$userId, $matchId]);

    if ($stmt->rowCount() == 0) {
        // Enregistrer le vote
        $stmt = $pdo->prepare("INSERT INTO votes (user_id, match_id, equipe_votee_id) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $matchId, $equipeVoteeId]);

        $_SESSION['message'] = "✅ Votre vote a été enregistré avec succès.";
    } else {
        $_SESSION['message'] = "⚠️ Vous avez déjà voté pour ce match.";
    }

    header("Location: votes.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Voter pour un Match</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body>
  <!-- Inclure la barre de navigation -->
<?php include 'navbar.php'; ?>

<!-- Barre de navigation -->

<div id="message-container" class="container mt-3">




</div>
 

<!-- Affichage des messages -->
<?php if (isset($_SESSION['message'])) : ?>
    <div class="alert alert-info text-center">
        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<!-- Section des matchs -->
<section class="py-5">
    <div class="container">
        <h2 class="mb-4 text-center">Votez pour un Match à venir</h2>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if (!empty($matchs)): ?>
                  <?php foreach ($matchs as $match) : ?>
    <div class="card mb-4 shadow match-card">
        <div class="card-body text-center">
            <p class="text-muted mb-1"><?= htmlspecialchars($match['date_match']) . " | " . htmlspecialchars($match['heure']) ?></p>
            <div class="d-flex align-items-center justify-content-center">
                <div class="me-3 text-center">
                    <img src="<?= htmlspecialchars($match['logo1']) ?>" alt="Logo <?= htmlspecialchars($match['equipe1']) ?>" class="img-fluid" style="width: 60px;">
                    <p class="mt-2"><strong><?= htmlspecialchars($match['equipe1']) ?></strong></p>
                </div>
                <h3 class="mx-3">VS</h3>
                <div class="ms-3 text-center">
                    <img src="<?= htmlspecialchars($match['logo2']) ?>" alt="Logo <?= htmlspecialchars($match['equipe2']) ?>" class="img-fluid" style="width: 60px;">
                    <p class="mt-2"><strong><?= htmlspecialchars($match['equipe2']) ?></strong></p>
                </div>
            </div>
            <?php if ($isLoggedIn): ?>
                <button class="btn btn-primary me-2 vote-btn" data-match="<?= $match['id'] ?>" data-team="<?= $match['equipe1_id'] ?>">Voter <?= htmlspecialchars($match['equipe1']) ?></button>
                <button class="btn btn-danger vote-btn" data-match="<?= $match['id'] ?>" data-team="<?= $match['equipe2_id'] ?>">Voter <?= htmlspecialchars($match['equipe2']) ?></button>
            <?php else: ?>
                <p class="text-danger mt-3">Connectez-vous pour voter.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

                <?php else: ?>
                    <p class="text-muted text-center">Aucun match à venir disponible pour le vote.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Bootstrap JS -->
<script src="../bootstrap-5.3.3-dist/js/bootstrap.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $(".vote-btn").click(function(e) {
        e.preventDefault();

        let button = $(this);
        let matchId = button.data("match");
        let equipeVoteeId = button.data("team");

        $.ajax({
            url: "vote_handler.php",
            type: "POST",
            data: {
                match_id: matchId,
                equipe_votee_id: equipeVoteeId
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    button.closest(".match-card").fadeOut(); // Supprime le match de l'affichage
                    $("#message-container").html(
                        `<div class="alert alert-success text-center">${response.message}</div>`
                    );
                } else {
                    $("#message-container").html(
                        `<div class="alert alert-warning text-center">${response.message}</div>`
                    );
                }
            },
            error: function() {
                $("#message-container").html(
                    `<div class="alert alert-danger text-center">❌ Une erreur s'est produite lors du vote.</div>`
                );
            }
        });
    });
});

</script>


</body>
</html>
