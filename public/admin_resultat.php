<?php
session_start();
require_once '../config/database.php';

// Vérification du rôle admin_tournoi
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_tournoi') {
    header("Location: index.php");
    exit();
}

// Récupérer les matchs avec les équipes
$query = "SELECT m.*, e1.nom AS equipe1, e2.nom AS equipe2, e1.id AS equipe1_id, e2.id AS equipe2_id
          FROM matches m
          JOIN equipes e1 ON m.equipe1_id = e1.id
          JOIN equipes e2 ON m.equipe2_id = e2.id
          WHERE m.statut = 'en cours' AND m.tournoi_id = 1
          ORDER BY m.date_match ASC";

$matchs = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Fonction pour récupérer les joueurs d'une équipe spécifique
function getJoueursParEquipe($pdo, $equipe_id) {
    $stmt = $pdo->prepare("SELECT id, nom FROM joueurs WHERE equipe_id = ?");
    $stmt->execute([$equipe_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



// Ajouter un score et les statistiques du match
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_score'])) {
    $match_id = $_POST['match_id'];
    $score_equipe1 = $_POST['score_equipe1'];
    $score_equipe2 = $_POST['score_equipe2'];

    // Préparer la requête de mise à jour
    $stmt = $pdo->prepare("UPDATE matches SET 
        score_equipe1 = ?, score_equipe2 = ?,
        possession_equipe1 = ?, possession_equipe2 = ?, 
        tirs_equipe1 = ?, tirs_equipe2 = ?, 
        tirs_cadres_equipe1 = ?, tirs_cadres_equipe2 = ?, 
        corners_equipe1 = ?, corners_equipe2 = ?, 
        fautes_equipe1 = ?, fautes_equipe2 = ?, 
        passes_equipe1 = ?, passes_equipe2 = ?, 
        interventions_gardien_equipe1 = ?, interventions_gardien_equipe2 = ?, 
        cartons_jaunes_equipe1 = ?, cartons_jaunes_equipe2 = ?, 
        cartons_rouges_equipe1 = ?, cartons_rouges_equipe2 = ?, 
        touches_equipe1 = ?, touches_equipe2 = ?, 
        tirs_bloques_equipe1 = ?, tirs_bloques_equipe2 = ?, 
        penaltys_concedes_equipe1 = ?, penaltys_concedes_equipe2 = ?, 
        penaltys_reussis_equipe1 = ?, penaltys_reussis_equipe2 = ?, 
        hors_jeu_equipe1 = ?, hors_jeu_equipe2 = ? 
        WHERE id = ?");

    // Exécuter la requête avec les valeurs du formulaire
    $stmt->execute([
        $score_equipe1, $score_equipe2, 
        $_POST['possession_equipe1'], $_POST['possession_equipe2'],
        $_POST['tirs_equipe1'], $_POST['tirs_equipe2'],
        $_POST['tirs_cadres_equipe1'], $_POST['tirs_cadres_equipe2'],
        $_POST['corners_equipe1'], $_POST['corners_equipe2'],
        $_POST['fautes_equipe1'], $_POST['fautes_equipe2'],
        $_POST['passes_equipe1'], $_POST['passes_equipe2'],
        $_POST['interventions_gardien_equipe1'], $_POST['interventions_gardien_equipe2'],
        $_POST['cartons_jaunes_equipe1'], $_POST['cartons_jaunes_equipe2'],
        $_POST['cartons_rouges_equipe1'], $_POST['cartons_rouges_equipe2'],
        $_POST['touches_equipe1'], $_POST['touches_equipe2'],
        $_POST['tirs_bloques_equipe1'], $_POST['tirs_bloques_equipe2'],
        $_POST['penaltys_concedes_equipe1'], $_POST['penaltys_concedes_equipe2'],
        $_POST['penaltys_reussis_equipe1'], $_POST['penaltys_reussis_equipe2'],
        $_POST['hors_jeu_equipe1'], $_POST['hors_jeu_equipe2'],
        $match_id
    ]);

    // Redirection après l'enregistrement
    echo "<script>setTimeout(() => { window.location.href = 'admin_resultat.php'; }, 500);</script>";
    exit();
}



// Ajouter un événement (but ou carton)

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_evenement'])) {
    $match_id = $_POST['match_id'];
    $joueur_id = $_POST['joueur_id'] ?? NULL;
    $minute = $_POST['minute'] ?? NULL;
    $type_event = $_POST['type_event'] ?? NULL;
    $carton = $_POST['carton'] ?? NULL;
    $minute_carton = !empty($_POST['minute_carton']) ? $_POST['minute_carton'] : NULL;

    // Vérifier que le joueur est bien sélectionné
    if (!$joueur_id) {
        echo "<script>alert('Veuillez sélectionner un joueur');</script>";
        exit();
    }

    // Récupérer l'équipe du joueur
    $stmt = $pdo->prepare("SELECT equipe_id FROM joueurs WHERE id = ?");
    $stmt->execute([$joueur_id]);
    $equipe_id = $stmt->fetchColumn() ?: NULL;

    if ($joueur_id && $minute && $equipe_id && $type_event) {
        // Si aucun carton n'est sélectionné, ne pas insérer de minute_carton
        if ($carton == "Aucun") {
            $carton = NULL;
            $minute_carton = NULL;
        }

        $stmt = $pdo->prepare("INSERT INTO match_events (match_id, joueur_id, equipe_id, type_event, minute_but, carton, minute_carton) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");

        if ($stmt->execute([$match_id, $joueur_id, $equipe_id, $type_event, $minute, $carton, $minute_carton])) {
            echo "<script>alert('Événement ajouté avec succès !'); window.location.href = 'admin_resultat.php';</script>";
        } else {
            echo "<script>alert('Erreur SQL : " . json_encode($stmt->errorInfo()) . "');</script>";
        }

        exit();
    } else {
        echo "<script>alert('Données manquantes !');</script>";
        exit();
    }
}



?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Résultats botola</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f4f4;
        }
        .container {
            max-width: 900px;
        }
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            font-size: 18px;
            font-weight: bold;
        }
        .btn {
            width: 100%;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h2 class="text-center mb-4">🏆 Gestion des Résultats botola ⚽</h2>

    <?php foreach ($matchs as $match) : ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <?= htmlspecialchars($match['equipe1']) ?> 🆚 <?= htmlspecialchars($match['equipe2']) ?>
                <br><small>📅 <?= $match['date_match'] ?></small>
            </div>
            <div class="card-body">
                
                <!-- Ajouter un Score -->
                <form method="post" class="row g-2 mb-3">
                    <input type="hidden" name="match_id" value="<?= $match['id'] ?>">
                    <div class="col">
                        <input type="number" class="form-control" name="score_equipe1" placeholder="Score <?= $match['equipe1'] ?>" required>
                    </div>
                    <div class="col">
                        <input type="number" class="form-control" name="score_equipe2" placeholder="Score <?= $match['equipe2'] ?>" required>
                    </div>

                <!-- Possession de balle -->
<label>Possession de balle (%)</label>
<input type="number" class="form-control" name="possession_equipe1" placeholder="Possession de <?= htmlspecialchars($match['equipe1']) ?>">
<input type="number" class="form-control" name="possession_equipe2" placeholder="Possession de <?= htmlspecialchars($match['equipe2']) ?>">

<!-- Tirs et tirs cadrés -->
<label>Tirs</label>
<input type="number" class="form-control" name="tirs_equipe1" placeholder="Tirs de <?= htmlspecialchars($match['equipe1']) ?>">
<input type="number" class="form-control" name="tirs_equipe2" placeholder="Tirs de <?= htmlspecialchars($match['equipe2']) ?>">

<label>Tirs cadrés</label>
<input type="number" class="form-control" name="tirs_cadres_equipe1" placeholder="Tirs cadrés de <?= htmlspecialchars($match['equipe1']) ?>">
<input type="number" class="form-control" name="tirs_cadres_equipe2" placeholder="Tirs cadrés de <?= htmlspecialchars($match['equipe2']) ?>">

<!-- Corners -->
<label>Corners</label>
<input type="number" class="form-control" name="corners_equipe1" placeholder="Corners de <?= htmlspecialchars($match['equipe1']) ?>">
<input type="number" class="form-control" name="corners_equipe2" placeholder="Corners de <?= htmlspecialchars($match['equipe2']) ?>">

<!-- Interventions gardien -->
<label>Interventions du gardien</label>
<input type="number" class="form-control" name="interventions_gardien_equipe1" placeholder="Sauvegardes de <?= htmlspecialchars($match['equipe1']) ?>">
<input type="number" class="form-control" name="interventions_gardien_equipe2" placeholder="Sauvegardes de <?= htmlspecialchars($match['equipe2']) ?>">

<!-- Fautes -->
<label>Fautes</label>
<input type="number" class="form-control" name="fautes_equipe1" placeholder="Fautes de <?= htmlspecialchars($match['equipe1']) ?>">
<input type="number" class="form-control" name="fautes_equipe2" placeholder="Fautes de <?= htmlspecialchars($match['equipe2']) ?>">

<!-- Cartons jaunes et rouges -->
<label>Cartons jaunes</label>
<input type="number" class="form-control" name="cartons_jaunes_equipe1" placeholder="Cartons jaunes de <?= htmlspecialchars($match['equipe1']) ?>">
<input type="number" class="form-control" name="cartons_jaunes_equipe2" placeholder="Cartons jaunes de <?= htmlspecialchars($match['equipe2']) ?>">

<label>Cartons rouges</label>
<input type="number" class="form-control" name="cartons_rouges_equipe1" placeholder="Cartons rouges de <?= htmlspecialchars($match['equipe1']) ?>">
<input type="number" class="form-control" name="cartons_rouges_equipe2" placeholder="Cartons rouges de <?= htmlspecialchars($match['equipe2']) ?>">

<!-- Passes complet -->
<label>Passes complétées</label>
<input type="number" class="form-control" name="passes_equipe1" placeholder="Passes de <?= htmlspecialchars($match['equipe1']) ?>">
<input type="number" class="form-control" name="passes_equipe2" placeholder="Passes de <?= htmlspecialchars($match['equipe2']) ?>">
<!-- Tirs bloqués -->
<label>Tirs bloqués</label>
<div>


<input type="number" class="form-control" name="tirs_bloques_equipe1" placeholder="Tirs bloqués de <?= htmlspecialchars($match['equipe1']) ?>">
<input type="number" class="form-control" name="tirs_bloques_equipe2" placeholder="Tirs bloqués de <?= htmlspecialchars($match['equipe2']) ?>">

<!-- Pénaltys -->
<label>Pénaltys concédés</label>
<div class="row">
    <div class="col">
        <input type="number" name="penaltys_concedes_equipe1" class="form-control" placeholder="Équipe 1" required>
    </div>
    <div class="col">
        <input type="number" name="penaltys_concedes_equipe2" class="form-control" placeholder="Équipe 2" required>
    </div>



<label>Pénaltys réussis</label>
<div class="row">
    <div class="col">
        <input type="number" name="penaltys_reussis_equipe1" class="form-control" placeholder="Équipe 1" required>
    </div>
    <div class="col">
        <input type="number" name="penaltys_reussis_equipe2" class="form-control" placeholder="Équipe 2" required>
    </div>
</div>

<label>Hors-jeu</label>
<div class="row">
    <div class="col">
        <input type="number" name="hors_jeu_equipe1" class="form-control" placeholder="Équipe 1" required>
    </div>
    <div class="col">
        <input type="number" name="hors_jeu_equipe2" class="form-control" placeholder="Équipe 2" required>
    </div>
</div>


<!-- Touches -->
<label>Touches</label>
<input type="number" class="form-control" name="touches_equipe1" placeholder="Touches de <?= htmlspecialchars($match['equipe1']) ?>">
<input type="number" class="form-control" name="touches_equipe2" placeholder="Touches de <?= htmlspecialchars($match['equipe2']) ?>">

                    <div class="col">
                        <button type="submit" name="ajouter_score" class="btn btn-success">✔ Ajouter Score</button>
                    </div>
                </form>

                <!-- Ajouter un Événement -->
                <h5 class="text-info">📋 Ajouter un Événement</h5>
                <form method="post" class="ajouter_evenement">

                    <input type="hidden" name="match_id" value="<?= $match['id'] ?>">

                    <label class="fw-bold">Joueur :</label>
                    <select class="form-select mb-2" name="joueur_id">
                        <optgroup label="<?= htmlspecialchars($match['equipe1']) ?>">
                            <?php foreach (getJoueursParEquipe($pdo, $match['equipe1_id']) as $joueur) : ?>
                                <option value="<?= $joueur['id'] ?>"><?= $joueur['nom'] ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="<?= htmlspecialchars($match['equipe2']) ?>">
                            <?php foreach (getJoueursParEquipe($pdo, $match['equipe2_id']) as $joueur) : ?>
                                <option value="<?= $joueur['id'] ?>"><?= $joueur['nom'] ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>

                    <label class="fw-bold">Type d'événement :</label>
                    <select class="form-select mb-2" name="type_event">
                        <option value="but">⚽ But</option>
                        <option value="carton">🚨 Carton</option>
                    </select>

                    <label class="fw-bold">Minute :</label>
                    <input type="number" class="form-control mb-2" name="minute" placeholder="Minute" min="1" max="120" required>

                    <label class="fw-bold">Type de Carton :</label>
                    <select class="form-select mb-2" name="carton">
                        <option value="">Aucun</option>
                        <option value="jaune">🟨 Jaune</option>
                        <option value="rouge">🟥 Rouge</option>
                    </select>

                    <label class="fw-bold">Minute :</label>
                    <input type="number" class="form-control mb-2" name="minute" placeholder="Minute" min="1" max="120" >

                    

                    <button type="submit" name="ajouter_evenement" class="btn btn-warning">Ajouter</button>
                </form>

                <form method="post" class="form_enregistrer">
               <input type="hidden" name="match_id" value="<?= $match['id'] ?>">
               <button type="submit" name="enregistrer_match" class="btn btn-danger mt-3">📌 Enregistrer</button>
           </form>


            </div>
        </div>
    <?php endforeach; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $("form.ajouter_evenement").submit(function(event) {
        event.preventDefault(); 

        var formData = $(this).serialize(); 

        $.ajax({
            type: "POST",
            url: "ajouter_evenement.php",
            data: formData,
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    alert(response.message);
                    location.reload(); 
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert("Erreur de connexion au serveur.");
            }
        });
    });
});

//script 2 pour marque rune marche comme terminé


$(document).ready(function() {
    $(".form_enregistrer").submit(function(event) {
        event.preventDefault(); // Empêcher le rechargement de la page

        var formData = $(this).serialize(); // Récupérer les données du formulaire
        var matchCard = $(this).closest('.card'); // Sélectionner la carte du match

        $.ajax({
            type: "POST",
            url: "enregistrer_match.php",
            data: formData,
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    alert(response.message);
                    matchCard.fadeOut(); // Masquer le match enregistré
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert("Erreur de connexion au serveur.");
            }
        });
    });
});

</script>

</body>
</html>
