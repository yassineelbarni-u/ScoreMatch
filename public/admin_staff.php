<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin_global') {
    header("Location: index.php");
    exit();
}

require_once '../config/database.php';

// **Initialisation des variables**
$nom = $prenom = $role = $equipe_id = "";
$edit_mode = false;

// **Récupérer les équipes pour la sélection**
$equipes = $pdo->query("SELECT id, nom FROM equipes")->fetchAll(PDO::FETCH_ASSOC);
$roles = ["Entraîneur", "Assistant coach", "Préparateur physique", "Médecin", "Analyste vidéo", "Responsable équipement"];

// **Vérifier si un staff doit être modifié**
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
    $stmt->execute([$id]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($staff) {
        $nom = $staff['nom'];
        $prenom = $staff['prenom'];
        $role = $staff['role'];
        $equipe_id = $staff['equipe_id'];
    }
}

// **Ajouter ou modifier un staff**
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $role = $_POST['role'];
    $equipe_id = $_POST['equipe_id'];

    if (isset($_POST['update'])) {  // **Modifier un staff**
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE staff SET nom = ?, prenom = ?, role = ?, equipe_id = ? WHERE id = ?");
        $stmt->execute([$nom, $prenom, $role, $equipe_id, $id]);
    } else {  // **Ajouter un staff**
        $stmt = $pdo->prepare("INSERT INTO staff (nom, prenom, role, equipe_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $role, $equipe_id]);
    }

    header("Location: admin_staff.php");
    exit();
}

// **Supprimer un staff**
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM staff WHERE id = ?")->execute([$id]);
    header("Location: admin_staff.php");
    exit();
}

// **Récupérer tous les membres du staff**
$query = "
    SELECT s.id, s.nom, s.prenom, s.role, e.nom AS equipe
    FROM staff s
    JOIN equipes e ON s.equipe_id = e.id
    ORDER BY e.nom, s.role
";
$stmt = $pdo->query($query);
$staffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion du Staff</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Gestion du Staff</h2>

    <!-- Bouton Ajouter -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addStaffModal">+ Ajouter un Membre</button>

    <!-- Tableau des membres du staff -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Rôle</th>
                <th>Équipe</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($staffs as $index => $staff) : ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($staff['nom']) ?></td>
                    <td><?= htmlspecialchars($staff['prenom']) ?></td>
                    <td><?= htmlspecialchars($staff['role']) ?></td>
                    <td><?= htmlspecialchars($staff['equipe']) ?></td>
                    <td>
                        <a href="admin_staff.php?edit=<?= $staff['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                        <a href="admin_staff.php?delete=<?= $staff['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce membre du staff ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Ajouter un Membre -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Membre du Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <label>Nom</label>
                    <input type="text" name="nom" class="form-control" required>

                    <label>Prénom</label>
                    <input type="text" name="prenom" class="form-control" required>

                    <label>Rôle</label>
                    <select name="role" class="form-control" required>
                        <?php foreach ($roles as $r) : ?>
                            <option value="<?= $r ?>"><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Équipe</label>
                    <select name="equipe_id" class="form-control" required>
                        <?php foreach ($equipes as $equipe) : ?>
                            <option value="<?= $equipe['id'] ?>"><?= htmlspecialchars($equipe['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add" class="btn btn-success">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
