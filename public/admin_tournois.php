<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est un admin global
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin_global') {
    header("Location: index.php");
    exit();
}

// Récupérer les tournois existants
$tournois = $pdo->query("
    SELECT t.*, u.nom AS admin_nom, u.email AS admin_email
    FROM tournois t
    JOIN users u ON t.admin_tournoi_id = u.id
    ORDER BY t.date_debut DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les admins de tournoi
$admins_tournoi = $pdo->query("
    SELECT id, nom, email FROM users WHERE role = 'admin_tournoi'
")->fetchAll(PDO::FETCH_ASSOC);

// Gestion AJAX des actions Ajouter, Modifier et Supprimer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == "ajouter") {
        $nom = $_POST['nom'];
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'];
        $admin_tournoi_id = $_POST['admin_tournoi_id'];

        if (!empty($nom) && !empty($date_debut) && !empty($date_fin) && !empty($admin_tournoi_id)) {
            $stmt = $pdo->prepare("INSERT INTO tournois (nom, date_debut, date_fin, admin_tournoi_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $date_debut, $date_fin, $admin_tournoi_id]);
            echo "success";
            exit();
        }
    }

    // Modifier un tournoi
    if ($_POST['action'] == "modifier") {
        $tournoi_id = $_POST['tournoi_id'];
        $nom = $_POST['nom'];
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'];
        $admin_tournoi_id = $_POST['admin_tournoi_id'];

        $stmt = $pdo->prepare("UPDATE tournois SET nom=?, date_debut=?, date_fin=?, admin_tournoi_id=? WHERE id=?");
        $stmt->execute([$nom, $date_debut, $date_fin, $admin_tournoi_id, $tournoi_id]);
        echo "success";
        exit();
    }

    // Supprimer un tournoi
    if ($_POST['action'] == "supprimer") {
        $tournoi_id = $_POST['tournoi_id'];
        $stmt = $pdo->prepare("DELETE FROM tournois WHERE id=?");
        $stmt->execute([$tournoi_id]);
        echo "success";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Tournois</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">🏆 Gestion des Tournois</h2>

    <!-- Modal Ajouter Tournoi -->
<div class="modal fade" id="addTournoiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addTournoiForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="ajouter">
                    
                    <label>Nom</label>
                    <input type="text" name="nom" id="addTournoiNom" class="form-control" required>

                    <label>Date Début</label>
                    <input type="date" name="date_debut" id="addTournoiDebut" class="form-control" required>

                    <label>Date Fin</label>
                    <input type="date" name="date_fin" id="addTournoiFin" class="form-control" required>

                    <label>Admin Tournoi</label>
                    <select name="admin_tournoi_id" id="addTournoiAdmin" class="form-control" required>
                        <?php foreach ($admins_tournoi as $admin) : ?>
                            <option value="<?= $admin['id'] ?>"><?= htmlspecialchars($admin['nom']) ?> (<?= htmlspecialchars($admin['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

     <!-- Bouton Ajouter -->
<button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addTournoiModal">+ Ajouter un Tournoi</button>

    <!-- Tableau des tournois -->
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Date Début</th>
                <th>Date Fin</th>
                <th>Admin</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="tournoiTableBody">
            <?php foreach ($tournois as $index => $tournoi) : ?>
                <tr id="tournoi_<?= $tournoi['id'] ?>">
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($tournoi['nom']) ?></td>
                    <td><?= htmlspecialchars($tournoi['date_debut']) ?></td>
                    <td><?= htmlspecialchars($tournoi['date_fin']) ?></td>
                    <td><?= htmlspecialchars($tournoi['admin_nom']) ?> (<?= htmlspecialchars($tournoi['admin_email']) ?>)</td>
                    <td>
                        <button class="btn btn-warning btn-sm editTournoiBtn" 
                                data-id="<?= $tournoi['id'] ?>"
                                data-nom="<?= htmlspecialchars($tournoi['nom']) ?>" 
                                data-date_debut="<?= $tournoi['date_debut'] ?>" 
                                data-date_fin="<?= $tournoi['date_fin'] ?>"
                                data-admin="<?= $tournoi['admin_tournoi_id'] ?>">
                            Modifier
                        </button>
                        <button class="btn btn-danger btn-sm deleteTournoiBtn" data-id="<?= $tournoi['id'] ?>">Supprimer</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Modifier Tournoi -->
<div class="modal fade" id="editTournoiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editTournoiForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="modifier">
                    <input type="hidden" name="tournoi_id" id="editTournoiId">
                    
                    <label>Nom</label>
                    <input type="text" name="nom" id="editTournoiNom" class="form-control" required>

                    <label>Date Début</label>
                    <input type="date" name="date_debut" id="editTournoiDebut" class="form-control" required>

                    <label>Date Fin</label>
                    <input type="date" name="date_fin" id="editTournoiFin" class="form-control" required>

                    <label>Admin Tournoi</label>
                    <select name="admin_tournoi_id" id="editTournoiAdmin" class="form-control" required>
                        <?php foreach ($admins_tournoi as $admin) : ?>
                            <option value="<?= $admin['id'] ?>"><?= htmlspecialchars($admin['nom']) ?> (<?= htmlspecialchars($admin['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function(){
    
    // Ouvrir le modal Modifier avec les données actuelles
    $(".editTournoiBtn").click(function(){
        $("#editTournoiId").val($(this).data("id"));
        $("#editTournoiNom").val($(this).data("nom"));
        $("#editTournoiDebut").val($(this).data("date_debut"));
        $("#editTournoiFin").val($(this).data("date_fin"));
        $("#editTournoiAdmin").val($(this).data("admin"));

        $("#editTournoiModal").modal("show");
    });

    // Modifier un tournoi via AJAX
    $("#editTournoiForm").submit(function(e){
        e.preventDefault();
        $.post("admin_tournois.php", $(this).serialize(), function(response){
            if(response == "success"){
                alert("Tournoi modifié !");
                location.reload();
            }
        });
    });

    // Supprimer un tournoi
    $(".deleteTournoiBtn").click(function(){
        var tournoiId = $(this).data("id");
        if(confirm("Voulez-vous vraiment supprimer ce tournoi ?")){
            $.post("admin_tournois.php", { action: "supprimer", tournoi_id: tournoiId }, function(response){
                if(response == "success"){
                    alert("Tournoi supprimé !");
                    location.reload();
                }
            });
        }
    });

    // Ajouter un tournoi via AJAX
    $("#addTournoiForm").submit(function(e){
        e.preventDefault();
        $.post("admin_tournois.php", $(this).serialize(), function(response){
            if(response == "success"){
                alert("Tournoi ajouté !");
                location.reload(); // Rafraîchir la page après l'ajout
            }
        });
    });
});

</script>

</body>
</html>
