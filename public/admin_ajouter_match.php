<?php
session_start();
require_once '../config/database.php';

// Vérification de l'accès Admin Global
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin_global') {
    header("Location: ../index.php");
    exit();
}

// Récupération des équipes de Premier League
$equipes = $pdo->query("SELECT id, nom FROM equipes ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $equipe1_id = $_POST['equipe1'];
    $equipe2_id = $_POST['equipe2'];
    $date_match = $_POST['date_match'];

    if ($equipe1_id == $equipe2_id) {
        $error = "Une équipe ne peut pas jouer contre elle-même.";
    } else {
        $query = "INSERT INTO matchs (equipe1_id, equipe2_id, date_match) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($query);
        if ($stmt->execute([$equipe1_id, $equipe2_id, $date_match])) {
            $success = "Le match a été ajouté avec succès !";
        } else {
            $error = "Erreur lors de l'ajout du match.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ajouter un Match</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="text-center">Ajouter un Match</h2>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (isset($success)) : ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="mt-4">
        <div class="mb-3">
            <label class="form-label">Équipe 1</label>
            <select name="equipe1" class="form-control" required>
                <option value="">-- Sélectionner une équipe --</option>
                <?php foreach ($equipes as $equipe) : ?>
                    <option value="<?= $equipe['id'] ?>"><?= htmlspecialchars($equipe['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Équipe 2</label>
            <select name="equipe2" class="form-control" required>
                <option value="">-- Sélectionner une équipe --</option>
                <?php foreach ($equipes as $equipe) : ?>
                    <option value="<?= $equipe['id'] ?>"><?= htmlspecialchars($equipe['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Date et Heure du Match</label>
            <input type="datetime-local" name="date_match" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Ajouter le Match</button>
    </form>
</div>
</body>
</html>
