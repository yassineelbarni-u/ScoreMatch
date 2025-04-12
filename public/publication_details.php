<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'ID de la publication est présent dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Aucune publication spécifiée.");
}

$id = intval($_GET['id']); // Sécuriser l'ID

try {
    // Récupérer les détails de la publication
    $stmt = $pdo->prepare("SELECT * FROM publications WHERE id = ?");
    $stmt->execute([$id]);
    $publication = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$publication) {
        die("Publication introuvable.");
    }
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Vérifier si une image est disponible
$imagePath = !empty($publication['image']) ? '../public/assets/images/' . htmlspecialchars($publication['image']) : '../public/assets/images/default.png';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($publication['titre']) ?> - Détails</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
    <style>
       /* 🌟 Styles Généraux */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f8f9fa;
    color: #212529;
    transition: 0.3s;
}

/* 📰 Conteneur de la publication */
.publication-container {
    max-width: 800px;
    margin: 50px auto;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: 0.3s;
}

/* 📝 Titre et Métadonnées */
.publication-title {
    font-size: 2.5rem;
    font-weight: bold;
    text-align: center;
    margin-bottom: 10px;
}

.publication-meta {
    text-align: center;
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 20px;
}

/* 🖼️ Image de la publication */
.publication-image {
    display: block;
    width: 100%;
    max-height: 400px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 20px;
}

/* 📖 Contenu */
.publication-content {
    font-size: 1.2rem;
    line-height: 1.6;
    text-align: justify;
}

/* 🔙 Bouton de retour */
.btn-back {
    display: block;
    width: 200px;
    margin: 20px auto;
    font-size: 18px;
    text-align: center;
}

/* 🌙 Mode Sombre */
.dark-mode {
    background-color: #121212;
    color: white;
}

.dark-mode .publication-container {
    background-color: #1e1e1e;
    color: white;
    border: 1px solid #444;
}

.dark-mode .publication-meta {
    color: #bbb !important;
}

.dark-mode .btn-primary {
    background-color: #ff5722;
    border-color: #ff5722;
}

.dark-mode .btn-primary:hover {
    background-color: #e64a19;
    border-color: #e64a19;
}

.dark-mode img {
    filter: brightness(0.8);
}

    </style>
</head>
<body class="<?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-mode' : '' ?>">
  <!-- Inclure la barre de navigation -->
<?php include 'navbar.php'; ?>

<div class="container">
    <div class="publication-container">
        <h1 class="publication-title"><?= htmlspecialchars($publication['titre']) ?></h1>
        <p class="publication-meta">📅 Publié le <?= date('d M Y à H:i', strtotime($publication['date_publication'])) ?></p>
        
        <!-- Affichage de l'image -->
        <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($publication['titre']) ?>" class="publication-image">
        
        <div class="publication-content">
            <?= nl2br(htmlspecialchars($publication['contenu'])) ?>
        </div>
        
        <a href="index.php" class="btn btn-primary btn-back">🏠 Retour à l'accueil</a>
    </div>
</div>

<script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
