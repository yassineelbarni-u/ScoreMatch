<?php
session_start();
require_once '../config/database.php';

// Vérification du rôle admin_tournoi
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_tournoi') {
    header("Location: index.php");
    exit();
}

// Récupération des publications
$publications = $pdo->query("SELECT * FROM publications ORDER BY date_publication DESC")->fetchAll(PDO::FETCH_ASSOC);

// Ajouter une publication
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_publication'])) {
    $titre = $_POST['titre'];
    $contenu = $_POST['contenu'];
    $image = $_FILES['image']['name'];
    $upload_dir = "../uploads/";
    
    if ($image) {
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
    }

    $stmt = $pdo->prepare("INSERT INTO publications (titre, contenu, image, auteur_id) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$titre, $contenu, $image, $_SESSION['user_id']])) {
        header("Location: admin_publication.php");
        exit();
    } else {
        $error = "Erreur lors de l'ajout.";
    }
}

// Supprimer une publication
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['supprimer_publication'])) {
    $stmt = $pdo->prepare("DELETE FROM publications WHERE id = ?");
    $stmt->execute([$_POST['publication_id']]);
    header("Location: admin_publication.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Publications</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body class="bg-light">
  
<div class="container mt-5">
    <h2 class="text-center">Gestion des Publications</h2>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Bouton Ajouter -->
    <div class="d-flex justify-content-between mb-3">
        <h4>Liste des Publications</h4>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPublicationModal">+ Ajouter</button>
    </div>

    <!-- Tableau des publications -->
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Image</th>
                <th>Titre</th>
                <th>Contenu</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($publications as $index => $publication) : ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><img src="../uploads/<?= htmlspecialchars($publication['image']) ?>" width="50"></td>
                    <td><?= htmlspecialchars($publication['titre']) ?></td>
                    <td><?= htmlspecialchars(substr($publication['contenu'], 0, 50)) ?>...</td>
                    <td><?= htmlspecialchars($publication['date_publication']) ?></td>
                    <td>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="publication_id" value="<?= $publication['id'] ?>">
                            <button type="submit" name="supprimer_publication" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous supprimer cette publication ?');">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Ajouter -->
<div class="modal fade" id="addPublicationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une Publication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <label>Titre</label>
                    <input type="text" name="titre" class="form-control" required>

                    <label>Contenu</label>
                    <textarea name="contenu" class="form-control" required></textarea>

                    <label>Image</label>
                    <input type="file" name="image" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="submit" name="ajouter_publication" class="btn btn-success">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>
