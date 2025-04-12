<?php
session_start();
require_once '../config/database.php';

// Vérifier que l'utilisateur est bien admin_global
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_global') {
    header("Location: index.php");
    exit();
}

// Récupérer les comptes admin_tournoi avec leur tournoi associé
$admins = $pdo->query("
    SELECT users.id, users.nom, users.email, users.date_inscription, tournois.nom AS tournoi_nom, users.tournoi_id 
    FROM users 
    LEFT JOIN tournois ON users.tournoi_id = tournois.id
    WHERE users.role = 'admin_tournoi'
")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les tournois disponibles
$tournois = $pdo->query("SELECT id, nom FROM tournois")->fetchAll(PDO::FETCH_ASSOC);

// Ajouter un admin tournoi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_admin'])) {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $tournoi_id = $_POST['tournoi_id']; // ID du tournoi sélectionné

    $stmt = $pdo->prepare("INSERT INTO users (nom, email, password, role, tournoi_id, date_inscription) 
                           VALUES (?, ?, ?, 'admin_tournoi', ?, NOW())");
    if ($stmt->execute([$nom, $email, $password, $tournoi_id])) {
        header("Location: admin_compte.php");
        exit();
    } else {
        $error = "Erreur lors de l'ajout de l'admin tournoi.";
    }
}

// Modifier un admin tournoi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_admin'])) {
    $id = $_POST['admin_id'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $tournoi_id = $_POST['tournoi_id']; // ID du tournoi sélectionné

    $stmt = $pdo->prepare("UPDATE users SET nom = ?, email = ?, tournoi_id = ? WHERE id = ?");
    if ($stmt->execute([$nom, $email, $tournoi_id, $id])) {
        header("Location: admin_compte.php");
        exit();
    } else {
        $error = "Erreur lors de la modification de l'admin tournoi.";
    }
}

// Supprimer un admin tournoi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['supprimer_admin'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_POST['admin_id']]);
    header("Location: admin_compte.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Admins Tournoi</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body class="bg-light">
  
<div class="container mt-5">
    <h2 class="text-center">Gestion des Admins Tournoi</h2>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mb-3">
        <h4>Liste des Admins Tournoi</h4>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAdminModal">+ Ajouter un Admin</button>
    </div>

    <!-- Tableau des admins tournoi -->
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                
                <th>#</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Date d'inscription</th>
                <th>Tournoi Associé</th>
                <th>Actions</th>

            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $index => $admin) : ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($admin['nom']) ?></td>
                    <td><?= htmlspecialchars($admin['email']) ?></td>
                    <td><?= htmlspecialchars($admin['date_inscription']) ?></td>
                    <td><?= htmlspecialchars($admin['tournoi_nom'] ?? 'Non Assigné') ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editAdminModal<?= $admin['id'] ?>">Modifier</button>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                            <button type="submit" name="supprimer_admin" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment supprimer cet admin ?');">Supprimer</button>
                        </form>
                    </td>
                </tr>

                <!-- Modal Modifier Admin -->
                <div class="modal fade" id="editAdminModal<?= $admin['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Modifier l'Admin</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post">
                                <div class="modal-body">
                                    <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                    <label>Nom</label>
                                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($admin['nom']) ?>" required>
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
                                    <label>Tournoi</label>
                                    <select name="tournoi_id" class="form-control" required>
                                        <option value="">-- Sélectionner --</option>
                                        <?php foreach ($tournois as $tournoi) : ?>
                                            <option value="<?= $tournoi['id'] ?>" <?= $admin['tournoi_id'] == $tournoi['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tournoi['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="modifier_admin" class="btn btn-success">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Ajouter un Admin -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Admin Tournoi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <label>Nom</label>
                    <input type="text" name="nom" class="form-control" required>
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                    <label>Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                    <label>Tournoi</label>
                    <label>Tournoi</label>
                <select name="tournoi_id" class="form-control" required>

                <option value="">-- Sélectionner --</option>
                <?php foreach ($tournois as $tournoi) : ?>
                <option value="<?= $tournoi['id'] ?>"><?= htmlspecialchars($tournoi['nom']) ?></option>
                <?php endforeach; ?>
                </select>

                </div>
                <div class="modal-footer">
                    <button type="submit" name="ajouter_admin" class="btn btn-success">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
