<?php
session_start();
require_once '../config/database.php'; // Vérifie que ce fichier contient bien $pdo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 🔎 Vérifier si l'utilisateur existe
    $query = "SELECT id, nom, email, password, role, tournoi_id FROM users WHERE email = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    // ✅ Vérifier le mot de passe
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['nom'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['tournoi_id'] = $user['tournoi_id'];

        // 🔀 Redirection selon le rôle
        if ($user['role'] == 'admin_global') {
            header("Location: admin_dashboard.php");
        } elseif ($user['role'] == 'admin_tournoi') {
            if ($user['tournoi_id'] == 1) {
                header("Location: tournoi_dashboard.php"); //  Admin Botola
            } elseif ($user['tournoi_id'] == 2) {
                header("Location: admin_kass_l3arch.php"); //  Admin Kass L3arch
            } else {
                header("Location: index.php"); //  Rediriger si pas de tournoi associé
            }
        } else {
            header("Location: index.php"); //  Rediriger les utilisateurs normaux
        }
        exit();
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">

<div class="card shadow p-4" style="width: 350px;">
    <h2 class="text-center">Connexion</h2>
    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Se connecter</button>
    </form>
    <p class="text-center mt-3">Pas de compte ? <a href="register.php">Inscrivez-vous</a></p>
</div>

</body>
</html>
