<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin_global') {
    header("Location: index.php");
    exit();
}

require_once '../config/database.php';

// Récupérer les stades
$stades = $pdo->query("SELECT * FROM stades ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Ajouter un stade
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_stade'])) {
    $nom = trim($_POST['nom']);
    $ville = trim($_POST['ville']);
    $capacité = trim($_POST['capacite']);
    $image = $_FILES['image']['name'];

    // Gestion du fichier image
    if (!empty($image)) {
        $dossier = "assets/images/";
        $fichier = basename($_FILES['image']['name']);
        $chemin_image = $dossier . $fichier;
        move_uploaded_file($_FILES['image']['tmp_name'], $chemin_image);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO stades (nom, ville, capacite, image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $ville, $capacité, $chemin_image]);
        header("Location: admin_stades.php");
        exit();
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}

// Modifier un stade
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_stade'])) {
    $id = intval($_POST['stade_id']);
    $nom = trim($_POST['nom']);
    $ville = trim($_POST['ville']);
    $capacité = trim($_POST['capacite']);
    $image = $_FILES['image']['name'];

    // Si une nouvelle image est téléchargée
    if (!empty($image)) {
        $dossier = "assets/images/";
        $fichier = basename($_FILES['image']['name']);
        $chemin_image = $dossier . $fichier;
        move_uploaded_file($_FILES['image']['tmp_name'], $chemin_image);
        $stmt = $pdo->prepare("UPDATE stades SET nom = ?, ville = ?, capacite = ?, image = ? WHERE id = ?");
        $stmt->execute([$nom, $ville, $capacité, $chemin_image, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE stades SET nom = ?, ville = ?, capacite = ? WHERE id = ?");
        $stmt->execute([$nom, $ville, $capacité, $id]);
    }

    header("Location: admin_stades.php");
    exit();
}

// Supprimer un stade
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['supprimer_stade'])) {
    $stmt = $pdo->prepare("DELETE FROM stades WHERE id = ?");
    $stmt->execute([$_POST['stade_id']]);
    header("Location: admin_stades.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Stades</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Gestion des Stades</h2>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addStadeModal">+ Ajouter un Stade</button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Ville</th>
                <th>Capacité</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stades as $index => $stade) : ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($stade['nom']) ?></td>
                    <td><?= htmlspecialchars($stade['ville']) ?></td>
                    <td><?= htmlspecialchars($stade['capacite']) ?></td>
                    <td><img src="<?= htmlspecialchars($stade['image']) ?>" width="50" height="50" alt="Image du Stade"></td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editStadeModal<?= $stade['id'] ?>">Modifier</button>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="stade_id" value="<?= $stade['id'] ?>">
                            <button type="submit" name="supprimer_stade" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce stade ?');">Supprimer</button>
                        </form>
                    </td>
                </tr>

                <!-- Modal Modifier Stade -->
                <div class="modal fade" id="editStadeModal<?= $stade['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="post" enctype="multipart/form-data">
                                <div class="modal-header">
                                    <h5 class="modal-title">Modifier le Stade</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="stade_id" value="<?= $stade['id'] ?>">
                                    <label>Nom</label>
                                    <input type="text" name="nom" value="<?= htmlspecialchars($stade['nom']) ?>" class="form-control" required>
                                    <label>Ville</label>
                                    <input type="text" name="ville" value="<?= htmlspecialchars($stade['ville']) ?>" class="form-control" required>
                                    <label>Capacité</label>
                                    <input type="number" name="capacité" value="<?= htmlspecialchars($stade['capacite']) ?>" class="form-control" required>
                                    <label>Image</label>
                                    <input type="file" name="image" class="form-control">
                                    <p><small>L'image actuelle : <?= htmlspecialchars($stade['image']) ?></small></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="modifier_stade" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Ajouter un Stade -->
<div class="modal fade" id="addStadeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Stade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <label>Nom</label>
                    <input type="text" name="nom" class="form-control" required>
                    <label>Ville</label>
                    <input type="text" name="ville" class="form-control" required>
                    <label>Capacité</label>
                    <input type="number" name="capacite" class="form-control" required>
                    <label>Image</label>
                    <input type="file" name="image" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="ajouter_stade" class="btn btn-success">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
