<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est un admin_global
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_global') {
    header("Location: index.php");
    exit();
}

// Récupérer la liste des arbitres
$arbitres = $pdo->query("SELECT id, nom, email, telephone, date_inscription FROM arbitres")->fetchAll(PDO::FETCH_ASSOC);

// Ajouter un arbitre
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_arbitre'])) {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];

    $stmt = $pdo->prepare("INSERT INTO arbitres (nom, email, telephone) VALUES (?, ?, ?)");
    if ($stmt->execute([$nom, $email, $telephone])) {
        header("Location: admin_arbitres.php");
        exit();
    } else {
        $error = "Erreur lors de l'ajout de l'arbitre.";
    }
}

// Modifier un arbitre
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_arbitre'])) {
    $id = $_POST['arbitre_id'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    
    $stmt = $pdo->prepare("UPDATE arbitres SET nom = ?, email = ?, telephone = ? WHERE id = ?");
    if ($stmt->execute([$nom, $email, $telephone, $id])) {
        header("Location: admin_arbitres.php");
        exit();
    } else {
        $error = "Erreur lors de la modification de l'arbitre.";
    }
}

// Supprimer un arbitre
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['supprimer_arbitre'])) {
    $stmt = $pdo->prepare("DELETE FROM arbitres WHERE id = ?");
    $stmt->execute([$_POST['arbitre_id']]);
    header("Location: admin_arbitres.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Arbitres</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center">Gestion des Arbitres</h2>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Bouton Ajouter -->
    <div class="d-flex justify-content-between mb-3">
        <h4>Liste des Arbitres</h4>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addArbitreModal">+ Ajouter un Arbitre</button>
    </div>

    <!-- Tableau des arbitres -->
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Date d'inscription</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($arbitres as $index => $arbitre) : ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($arbitre['nom']) ?></td>
                <td><?= htmlspecialchars($arbitre['email']) ?></td>
                <td><?= htmlspecialchars($arbitre['telephone']) ?></td>
                <td><?= htmlspecialchars($arbitre['date_inscription']) ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editArbitreModal<?= $arbitre['id'] ?>">Modifier</button>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="arbitre_id" value="<?= $arbitre['id'] ?>">
                        <button type="submit" name="supprimer_arbitre" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment supprimer cet arbitre ?');">Supprimer</button>
                    </form>
                </td>
            </tr>

            <!-- Modal Modifier Arbitre -->
            <div class="modal fade" id="editArbitreModal<?= $arbitre['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Modifier l'Arbitre</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="post">
                            <div class="modal-body">
                                <input type="hidden" name="arbitre_id" value="<?= $arbitre['id'] ?>">
                                <label>Nom</label>
                                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($arbitre['nom']) ?>" required>
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($arbitre['email']) ?>" required>
                                <label>Téléphone</label>
                                <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($arbitre['telephone']) ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="modifier_arbitre" class="btn btn-success">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Ajouter un Arbitre -->
<div class="modal fade" id="addArbitreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Arbitre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <label>Nom</label>
                    <input type="text" name="nom" class="form-control" required>
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                    <label>Téléphone</label>
                    <input type="text" name="telephone" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="submit" name="ajouter_arbitre" class="btn btn-success">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
