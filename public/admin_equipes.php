<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';

// Vérification du rôle admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_global') {
    header("Location: index.php");
    exit();
}

// Récupérer les équipes avec les stades associés
$equipes = $pdo->query("
    SELECT equipes.*, GROUP_CONCAT(tournois.nom SEPARATOR ', ') AS tournois
    FROM equipes
    LEFT JOIN equipes_tournois ON equipes.id = equipes_tournois.equipe_id
    LEFT JOIN tournois ON equipes_tournois.tournoi_id = tournois.id
    GROUP BY equipes.id
    ORDER BY equipes.nom
")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des stades pour le formulaire
$stades = $pdo->query("SELECT id, nom FROM stades ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
// Récupérer la liste des tournois
$tournois = $pdo->query("SELECT id, nom FROM tournois")->fetchAll(PDO::FETCH_ASSOC);


//traitement pour associer une équipe à un ou plusieurs tournois
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_equipe'])) {
    $nom = trim($_POST['nom']);
    $entraineur = trim($_POST['entraineur']);
    $description = trim($_POST['description']);
    $tournoi_ids = $_POST['tournoi_ids'] ?? []; // Liste des tournois cochés

    // Gestion du fichier image (logo)
    if (!empty($_FILES['logo']['name'])) {
        $dossier = "assets/images/";
        $fichier = basename($_FILES['logo']['name']);
        $chemin_logo = $dossier . $fichier;
        move_uploaded_file($_FILES['logo']['tmp_name'], $chemin_logo);
    } else {
        $chemin_logo = "assets/images/default.png";
    }

    try {
        // 🔹 Étape 1 : Ajouter l'équipe
        $stmt = $pdo->prepare("INSERT INTO equipes (nom, entraineur, description, logo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $entraineur, $description, $chemin_logo]);
        $equipe_id = $pdo->lastInsertId();

        // 🔹 Étape 2 : Associer aux tournois sélectionnés
        foreach ($tournoi_ids as $tournoi_id) {
            $stmt = $pdo->prepare("INSERT INTO equipes_tournois (equipe_id, tournoi_id) VALUES (?, ?)");
            $stmt->execute([$equipe_id, $tournoi_id]);
        }

        header("Location: admin_equipes.php");
        exit();
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}


   



// Ajouter une équipe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_equipe'])) {
    $nom = trim($_POST['nom']);
    $entraineur = trim($_POST['entraineur']);
    $description = trim($_POST['description']);
    $stade_id = !empty($_POST['stade_id']) ? intval($_POST['stade_id']) : NULL;
    $tournoi_id = !empty($_POST['tournoi_id']) ? intval($_POST['tournoi_id']) : NULL;

    if (!empty($_FILES['logo']['name'])) {
        $dossier = "assets/images/";
        $fichier = basename($_FILES['logo']['name']);
        $chemin_logo = $dossier . $fichier;
        move_uploaded_file($_FILES['logo']['tmp_name'], $chemin_logo);
    } else {
        $chemin_logo = "assets/images/default.png";
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO equipes (nom, entraineur, description, logo, stade_id, tournoi_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $entraineur, $description, $chemin_logo, $stade_id, $tournoi_id]);
        header("Location: admin_equipes.php");
        exit();
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}


// Modifier une équipe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_equipe'])) {
    $id = intval($_POST['equipe_id']);
    $nom = trim($_POST['nom']);
    $entraineur = trim($_POST['entraineur']);
    $description = trim($_POST['description']);
    $stade_id = !empty($_POST['stade_id']) ? intval($_POST['stade_id']) : NULL;
    $tournoi_id = !empty($_POST['tournoi_id']) ? intval($_POST['tournoi_id']) : NULL;

    if (!empty($_FILES['logo']['name'])) {
        $dossier = "assets/images/";
        $fichier = basename($_FILES['logo']['name']);
        $chemin_logo = $dossier . $fichier;
        move_uploaded_file($_FILES['logo']['tmp_name'], $chemin_logo);
        $sql = "UPDATE equipes SET nom=?, entraineur=?, description=?, logo=?, stade_id=?, tournoi_id=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom, $entraineur, $description, $chemin_logo, $stade_id, $tournoi_id, $id]);
    } else {
        $sql = "UPDATE equipes SET nom=?, entraineur=?, description=?, stade_id=?, tournoi_id=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom, $entraineur, $description, $stade_id, $tournoi_id, $id]);
    }

    header("Location: admin_equipes.php");
    exit();
}


// Supprimer une équipe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['supprimer_equipe'])) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_global') {
        die("Accès refusé !");
    }

    $stmt = $pdo->prepare("DELETE FROM equipes WHERE id = ?");
    $stmt->execute([$_POST['equipe_id']]);
    header("Location: admin_equipes.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Équipes</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body>



<div class="container mt-5">
    <h2 class="text-center">Gestion des Équipes</h2>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addEquipeModal">+ Ajouter une Équipe</button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Logo</th>
                <th>Nom</th>
                <th>Entraîneur</th>
                <th>Description</th>
                <th>Stade</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($equipes as $index => $equipe) : ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><img src="<?= htmlspecialchars($equipe['logo']) ?>" width="50" height="50" alt="Logo"></td>
                    <td><?= htmlspecialchars($equipe['nom']) ?></td>
                    <td><?= htmlspecialchars($equipe['entraineur']) ?></td>
                    <td><?= htmlspecialchars($equipe['description']) ?></td>
                    <td><?= htmlspecialchars($equipe['stade_nom'] ?? 'Non attribué') ?></td>
                    <td>
                        <?php if ($_SESSION['role'] === 'admin_global') : ?>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editEquipeModal<?= $equipe['id'] ?>">Modifier</button>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="equipe_id" value="<?= $equipe['id'] ?>">
                                <button type="submit" name="supprimer_equipe" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette équipe ?');">Supprimer</button>
                            </form>
                        <?php else : ?>
                            <span class="text-muted">Accès restreint</span>
                        <?php endif; ?>
                    </td>
                </tr>

                <div class="modal fade" id="editEquipeModal<?= $equipe['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="post" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" name="equipe_id" value="<?= $equipe['id'] ?>">
                                    <label>Nom</label>
                                    <input type="text" name="nom" value="<?= htmlspecialchars($equipe['nom']) ?>" class="form-control" required>
                                    <label>Entraîneur</label>
                                    <input type="text" name="entraineur" value="<?= htmlspecialchars($equipe['entraineur']) ?>" class="form-control" required>
                                    <label>Description</label>
                                    <textarea name="description" class="form-control"><?= htmlspecialchars($equipe['description']) ?></textarea>
                                    <label>Logo</label>

                                    <input type="file" name="logo" class="form-control">

                                    <?php
$equipe_id = $_GET['equipe_id'] ?? null;
$selected_tournois = [];

if ($equipe_id) {
    $stmt = $pdo->prepare("SELECT tournoi_id FROM equipes_tournois WHERE equipe_id = ?");
    $stmt->execute([$equipe_id]);
    $selected_tournois = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>


<label>Associer aux tournois :</label>
<div class="form-check">
    <?php foreach ($tournois as $tournoi) : ?>
        <input class="form-check-input" type="checkbox" name="tournoi_ids[]" value="<?= $tournoi['id'] ?>" id="tournoi_<?= $tournoi['id'] ?>">
        <label class="form-check-label" for="tournoi_<?= $tournoi['id'] ?>">
            <?= htmlspecialchars($tournoi['nom']) ?>
        </label>
        <br>
    <?php endforeach; ?>
</div>


                                    <button type="submit" name="modifier_equipe" class="btn btn-primary mt-3">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<!-- Modal Ajouter une Équipe -->
<div class="modal fade" id="addEquipeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une Équipe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <label>Nom</label>
                    <input type="text" name="nom" class="form-control" required>

                    <label>Entraîneur</label>
                    <input type="text" name="entraineur" class="form-control" required>

                    <label>Description</label>
                    <textarea name="description" class="form-control"></textarea>

                    <label>Stade</label>
                    <select name="stade_id" class="form-control">
                        <option value="">Sélectionner un stade</option>
                        <?php foreach ($stades as $stade) : ?>
                            <option value="<?= $stade['id'] ?>"><?= htmlspecialchars($stade['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label>Associer aux tournois :</label>
<div class="form-check">
    <?php foreach ($tournois as $tournoi) : ?>
        <input class="form-check-input" type="checkbox" name="tournoi_ids[]" value="<?= $tournoi['id'] ?>" id="tournoi_<?= $tournoi['id'] ?>">
        <label class="form-check-label" for="tournoi_<?= $tournoi['id'] ?>">
            <?= htmlspecialchars($tournoi['nom']) ?>
        </label>
        <br>
    <?php endforeach; ?>
</div>



                    

                    <label>Logo</label>
                    <input type="file" name="logo" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="submit" name="ajouter_equipe" class="btn btn-success">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
