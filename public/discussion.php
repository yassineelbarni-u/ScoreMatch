<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}
 //Définir la variable user_id

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT m.id, m.date_match, m.heure, 
           e1.nom AS equipe1_nom, e1.logo AS equipe1_logo, 
           e2.nom AS equipe2_nom, e2.logo AS equipe2_logo
    FROM matches m
    JOIN equipes e1 ON m.equipe1_id = e1.id
    JOIN equipes e2 ON m.equipe2_id = e2.id
    WHERE m.date_match > CURDATE() OR (m.date_match = CURDATE() AND m.heure > CURTIME())
    ORDER BY m.date_match ASC, m.heure ASC
");

$stmt->execute();
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si un match a été sélectionné par l'utilisateur
$match_id = isset($_GET['match_id']) ? intval($_GET['match_id']) : null;

$selected_match = null;
$comments = [];

if ($match_id) {
  // Vérifier si le match sélectionné existe et n'a pas commencé
  $stmt = $pdo->prepare("
      SELECT m.id, m.date_match, m.heure, 
             e1.nom AS equipe1_nom, e1.logo AS equipe1_logo, 
             e2.nom AS equipe2_nom, e2.logo AS equipe2_logo
      FROM matches m
      JOIN equipes e1 ON m.equipe1_id = e1.id
      JOIN equipes e2 ON m.equipe2_id = e2.id
      WHERE m.id = ? 
      AND (m.date_match > CURDATE() OR (m.date_match = CURDATE() AND m.heure > CURTIME()))
  ");
  $stmt->execute([$match_id]);
  $selected_match = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($selected_match) {
      // Récupérer les commentaires pour le match sélectionné
      $stmt = $pdo->prepare("
          SELECT c.*, u.nom FROM commentaires c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.match_id = ? 
          ORDER BY c.date_commentaire DESC
      ");
      $stmt->execute([$match_id]);
      $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
      $match_id = null; // Réinitialiser si le match est invalide
  }
}


// Ajouter un commentaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment']) && $match_id) {
    $comment = trim($_POST['comment']);

    // Insérer le commentaire dans la base de données
    $stmt = $pdo->prepare("INSERT INTO commentaires (user_id, match_id, contenu) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $match_id, $comment]);

    // Rafraîchir la page pour voir immédiatement le commentaire
    header("Location: discussion.php?match_id=" . $match_id);
    exit();

}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Discussion sur les Matchs</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
</head>
<body>
  <!-- Inclure la barre de navigation -->
<?php include 'navbar.php'; ?>
<div class="container mt-5">
    <h2 class="text-center">Discussions sur les Matchs</h2>

    <!-- Sélection du match -->
    <div class="card mb-4 shadow">
        <div class="card-body">
            <h5>Sélectionnez un match :</h5>
            <form method="GET">
                  <select name="match_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Choisissez un match --</option>
                    <?php foreach ($matches as $match): ?>
                    <option value="<?= $match['id'] ?>" <?= ($match['id'] == $match_id) ? 'selected' : '' ?>>
                     <?= htmlspecialchars($match['equipe1_nom']) ?> 🆚 <?= htmlspecialchars($match['equipe2_nom']) ?> |
                    <?= htmlspecialchars($match['date_match']) ?> à <?= htmlspecialchars($match['heure']) ?>
              </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Affichage du match sélectionné -->
    <?php if ($selected_match): ?>
      <div class="text-center">
    <h4>
        <img src="<?= htmlspecialchars($selected_match['equipe1_logo']) ?>" 
             alt="Logo <?= htmlspecialchars($selected_match['equipe1_nom']) ?>" width="50">
        <?= htmlspecialchars($selected_match['equipe1_nom']) ?>
        🆚
        <?= htmlspecialchars($selected_match['equipe2_nom']) ?>
        <img src="<?= htmlspecialchars($selected_match['equipe2_logo']) ?>" 
             alt="Logo <?= htmlspecialchars($selected_match['equipe2_nom']) ?>" width="50">
    </h4>
    <p><strong>Date :</strong> <?= htmlspecialchars($selected_match['date_match']) ?> | 
       <strong>Heure :</strong> <?= htmlspecialchars($selected_match['heure']) ?></p>
</div>


        <!-- Formulaire d'ajout de commentaire -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>Ajoutez un commentaire :</h5>
                <form method="POST">
                    <input type="hidden" name="match_id" value="<?= $selected_match['id'] ?>">
                    <textarea name="comment" class="form-control" placeholder="Écrivez votre commentaire..." required></textarea>
                    <button type="submit" class="btn btn-primary mt-2">Publier</button>
                </form>
            </div>
        </div>

        <!-- Affichage des commentaires -->
        <h3 class="mt-4">Commentaires</h3>
        <ul class="list-group">
            <?php if (empty($comments)) : ?>
                <li class="list-group-item text-muted text-center">Aucun commentaire pour ce match.</li>
            <?php else : ?>
                <?php foreach ($comments as $comment): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($comment['nom']) ?> :</strong>
                        <?= htmlspecialchars($comment['contenu']) ?>
                        <small class="text-muted float-end"><?= $comment['date_commentaire'] ?></small>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="../bootstrap-5.3.3-dist/js/bootstrap.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const commentForm = document.querySelector("form");
    const commentInput = document.querySelector("textarea[name='comment']");
    const commentList = document.querySelector(".list-group");

    commentForm.addEventListener("submit", function (e) {
        e.preventDefault(); // Empêche le rechargement de la page

        let commentText = commentInput.value.trim();
        if (commentText === "") return;

        let newComment = document.createElement("li");
        newComment.classList.add("list-group-item");
        newComment.innerHTML = `
            <strong>Vous :</strong> ${commentText} 
            <small class="text-muted float-end">Maintenant</small>
        `;

        commentList.prepend(newComment); // Ajoute le commentaire en haut de la liste
        commentInput.value = ""; // Vide le champ de texte

        // Envoie le commentaire au serveur pour l'enregistrer
        fetch("save_comment.php", {
            method: "POST",
            body: new FormData(commentForm)
        });
    });
});
</script>

</body>
</html>
