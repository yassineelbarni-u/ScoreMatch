<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est un admin_global
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_global') {
    header("Location: index.php");
    exit();
}

// Récupérer les comptes utilisateurs (role = user)
$users = $pdo->query("SELECT id, nom, email, date_inscription FROM users WHERE role = 'user'")->fetchAll(PDO::FETCH_ASSOC);

// Ajouter un utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_user'])) {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (nom, email, password, role, date_inscription) VALUES (?, ?, ?, 'user', NOW())");
    if ($stmt->execute([$nom, $email, $password])) {
        header("Location: admin_user.php");
        exit();
    } else {
        $error = "Erreur lors de l'ajout de l'utilisateur.";
    }
}

// Modifier un utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_user'])) {
    $id = $_POST['user_id'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    
    $stmt = $pdo->prepare("UPDATE users SET nom = ?, email = ? WHERE id = ?");
    if ($stmt->execute([$nom, $email, $id])) {
        header("Location: admin_user.php");
        exit();
    } else {
        $error = "Erreur lors de la modification de l'utilisateur.";
    }
}

// Supprimer un utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['supprimer_user'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_POST['user_id']]);
    header("Location: admin_user.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Utilisateurs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center">Gestion des Utilisateurs</h2>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Bouton Ajouter -->
    <div class="d-flex justify-content-between mb-3">
        <h4>Liste des Utilisateurs</h4>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">+ Ajouter un Utilisateur</button>
    </div>

    <!-- Tableau des utilisateurs -->
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Date d'inscription</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $index => $user) : ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($user['nom']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['date_inscription']) ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $user['id'] ?>">Modifier</button>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <button type="submit" name="supprimer_user" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');">Supprimer</button>
                    </form>
                </td>
            </tr>

            <!-- Modal Modifier Utilisateur -->
            <div class="modal fade" id="editUserModal<?= $user['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Modifier l'Utilisateur</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="post">
                            <div class="modal-body">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <label>Nom</label>
                                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="modifier_user" class="btn btn-success">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Ajouter un Utilisateur -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Utilisateur</h5>
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
                </div>
                <div class="modal-footer">
                    <button type="submit" name="ajouter_user" class="btn btn-success">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
