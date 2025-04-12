<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Vérifier que le rôle sélectionné est valide
    $roles_valides = ['user', 'admin_tournoi'];
    $role = in_array($_POST['role'], $roles_valides) ? $_POST['role'] : 'user';

    // Vérifier si l'email existe déjà
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $error = "Cet email est déjà utilisé.";
    } else {
        // Insérer l'utilisateur dans la base de données avec le rôle choisi
        $query = "INSERT INTO users (nom, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        
        if ($stmt->execute([$nom, $email, $password, $role])) {
            header("Location: login.php");
            exit();
        } else {
            $error = "Erreur lors de l'inscription.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">

<div class="card shadow p-4" style="width: 350px;">
    <h2 class="text-center">Inscription</h2>
    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Rôle</label>
            <select name="role" class="form-control">
                <option value="user">Utilisateur</option>
                <!-- <option value="admin_tournoi">Admin Tournoi</option> -->
            </select>
        </div>
        <button type="submit" class="btn btn-success w-100">S'inscrire</button>
    </form>
    
    <p class="text-center mt-3">Déjà un compte ? <a href="login.php">Connectez-vous</a></p>
</div>

</body>
</html>
