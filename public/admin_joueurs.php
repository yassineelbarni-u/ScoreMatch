<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin_global') {
    header("Location: index.php");
    exit();
}

require_once '../config/database.php';

// **Initialiser les variables**
$nom = $prenom = $age = $position = $equipe_id = "";
$edit_mode = false;

// **Récupérer les équipes pour la sélection**
$equipes = $pdo->query("SELECT id, nom FROM equipes")->fetchAll(PDO::FETCH_ASSOC);

// **Vérifier si un joueur doit être modifié**
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM joueurs WHERE id = ?");
    $stmt->execute([$id]);
    $joueur = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($joueur) {
        $nom = $joueur['nom'];
        $prenom = $joueur['prenom'];
        $age = $joueur['age'];
        $position = $joueur['position'];
        $equipe_id = $joueur['equipe_id'];
    }
}


// **Ajouter ou modifier un joueur**
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['update']) || isset($_POST['add']))) {
  $nom = isset($_POST['nom']) ? trim($_POST['nom']) : "";
  $prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : "";
  $age = isset($_POST['age']) ? intval($_POST['age']) : null;
  $position = isset($_POST['position']) ? trim($_POST['position']) : "";
  $equipe_id = isset($_POST['equipe_id']) ? intval($_POST['equipe_id']) : null;

  if (empty($nom) || empty($prenom) || empty($position) || !$equipe_id) {
      $_SESSION['message'] = " Tous les champs sont obligatoires.";
      header("Location: admin_joueurs.php");
      exit();
  }

  if (isset($_POST['update'])) {  // **Modifier un joueur**
      $id = intval($_POST['id']);
      $stmt = $pdo->prepare("UPDATE joueurs SET nom = ?, prenom = ?, age = ?, position = ?, equipe_id = ? WHERE id = ?");
      $stmt->execute([$nom, $prenom, $age, $position, $equipe_id, $id]);
  } else {  // **Ajouter un joueur**
      $stmt = $pdo->prepare("INSERT INTO joueurs (nom, prenom, age, position, equipe_id) VALUES (?, ?, ?, ?, ?)");
      $stmt->execute([$nom, $prenom, $age, $position, $equipe_id]);
  }

  header("Location: admin_joueurs.php");
  exit();
}


// **Supprimer un joueur**
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM joueurs WHERE id = ?")->execute([$id]);
    header("Location: admin_joueurs.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  echo "<pre>";
  print_r($_POST); // Afficher les données envoyées
  echo "</pre>";
}

// Transférer un joueur à une autre équipe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['transferer'])) {
  if (!isset($_POST['joueur_id']) || !isset($_POST['nouvelle_equipe'])) {
      $_SESSION['message'] = " Erreur : Données manquantes pour le transfert.";
      header("Location: admin_joueurs.php");
      exit();
  }

  $joueur_id = intval($_POST['joueur_id']);
  $nouvelle_equipe = intval($_POST['nouvelle_equipe']);

  // Vérifier si le joueur existe
  $stmt = $pdo->prepare("SELECT id FROM joueurs WHERE id = ?");
  $stmt->execute([$joueur_id]);
  if ($stmt->rowCount() === 0) {
      $_SESSION['message'] = " Erreur : Joueur introuvable.";
      header("Location: admin_joueurs.php");
      exit();
  }

  // Vérifier si la nouvelle équipe existe
  $stmt = $pdo->prepare("SELECT id FROM equipes WHERE id = ?");
  $stmt->execute([$nouvelle_equipe]);
  if ($stmt->rowCount() === 0) {
      $_SESSION['message'] = " Erreur : Équipe invalide.";
      header("Location: admin_joueurs.php");
      exit();
  }

  // Mise à jour du joueur
  $stmt = $pdo->prepare("UPDATE joueurs SET equipe_id = ? WHERE id = ?");
  $stmt->execute([$nouvelle_equipe, $joueur_id]);

  if ($stmt->rowCount() > 0) {
      $_SESSION['message'] = " Joueur transféré avec succès.";
  } else {
      $_SESSION['message'] = " Aucun changement effectué.";
  }

  header("Location: admin_joueurs.php");
  exit();
}



// **Récupérer tous les joueurs**
$query = "
    SELECT j.id, j.nom, j.prenom, j.age, j.position, e.nom AS equipe
    FROM joueurs j
    JOIN equipes e ON j.equipe_id = e.id
    ORDER BY e.nom, j.nom
";
$stmt = $pdo->query($query);
$joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion des Joueurs</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body>
<?php if (isset($_SESSION['message'])) : ?>
    <div class="alert alert-info text-center">
        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>


<div class="container mt-5">
    <h2 class="text-center"><?= $edit_mode ? "Modifier un Joueur" : "Ajouter un Joueur" ?></h2>

    <!-- Formulaire d'Ajout et de Modification -->
    <form method="POST">
        <?php if ($edit_mode) : ?>
            <input type="hidden" name="id" value="<?= $id ?>">
        <?php endif; ?>
        
        <div class="mb-3">
            <label class="form-label">Nom :</label>
            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($nom) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Prénom :</label>
            <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($prenom) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Âge :</label>
            <input type="number" name="age" class="form-control" value="<?= htmlspecialchars($age) ?>" min="16" max="40" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Position :</label>
            <select name="position" class="form-control" required>
                <option value="Gardien" <?= ($position == "Gardien") ? "selected" : "" ?>>Gardien</option>
                <option value="Défenseur" <?= ($position == "Défenseur") ? "selected" : "" ?>>Défenseur</option>
                <option value="Milieu" <?= ($position == "Milieu") ? "selected" : "" ?>>Milieu</option>
                <option value="Attaquant" <?= ($position == "Attaquant") ? "selected" : "" ?>>Attaquant</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Équipe :</label>
            <select name="equipe_id" class="form-control" required>
                <?php foreach ($equipes as $equipe) : ?>
                    <option value="<?= $equipe['id'] ?>" <?= ($equipe_id == $equipe['id']) ? "selected" : "" ?>>
                        <?= htmlspecialchars($equipe['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" name="<?= $edit_mode ? "update" : "add" ?>" class="btn btn-success">
            <?= $edit_mode ? "Mettre à jour" : "Ajouter" ?>
        </button>
        <a href="admin_joueurs.php" class="btn btn-secondary">Annuler</a>
    </form>
    <?php foreach ($joueurs as $joueur) : ?>
    <!-- Modal de transfert -->
    <div class="modal fade" id="transferModal<?= $joueur['id'] ?>" tabindex="-1" aria-labelledby="transferModalLabel<?= $joueur['id'] ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="transferModalLabel<?= $joueur['id'] ?>">Transférer <?= htmlspecialchars($joueur['nom']) ?> <?= htmlspecialchars($joueur['prenom']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="joueur_id" value="<?= $joueur['id'] ?>">
                        <label class="form-label">Nouvelle équipe :</label>
                        <select name="nouvelle_equipe" class="form-control" required>
                            <?php foreach ($equipes as $equipe) : ?>
                                <?php if ($equipe['id'] != $joueur['equipe_id']) : ?>
                                    <option value="<?= $equipe['id'] ?>"><?= htmlspecialchars($equipe['nom']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="transferer" class="btn btn-success">Confirmer le transfert</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>


    <hr>

    <!-- Liste des joueurs -->
    <h2 class="text-center mt-4">Liste des Joueurs</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Âge</th>
                <th>Position</th>
                <th>Équipe</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($joueurs as $joueur) : ?>
                <tr>
                    <td><?= htmlspecialchars($joueur['nom']) ?></td>
                    <td><?= htmlspecialchars($joueur['prenom']) ?></td>
                    <td><?= htmlspecialchars($joueur['age']) ?></td>
                    <td><?= htmlspecialchars($joueur['position']) ?></td>
                    <td><?= htmlspecialchars($joueur['equipe']) ?></td>
                    <td>
                        <a href="admin_joueurs.php?edit=<?= $joueur['id'] ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <a href="admin_joueurs.php?delete=<?= $joueur['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment supprimer ce joueur ?');">Supprimer</a>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#transferModal<?= $joueur['id'] ?>">Transférer</button>
                    </td>
                    
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
