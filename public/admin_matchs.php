<?php
session_start();
require_once '../config/database.php';


// Ajouter la fonction de redirection
function redirect($url) {
    echo "<script>window.location.replace('$url');</script>";
    exit();
}

// Vérification du rôle admin_global
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_tournoi') {
    header("Location: index.php");
    exit();
}

// Récupération des équipes pour le formulaire
$equipes = $pdo->query("SELECT id, nom FROM equipes ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);


// Récupération des matchs pour affichage
$matchs = $pdo->query("
    SELECT m.id, m.equipe1_id, m.equipe2_id, m.stade_id, e1.nom AS equipe1, e2.nom AS equipe2, 
           s.nom AS stade, m.date_match, m.heure, m.arbitre_id, a.nom AS arbitre
    FROM matches m
    JOIN equipes e1 ON m.equipe1_id = e1.id
    JOIN equipes e2 ON m.equipe2_id = e2.id
    LEFT JOIN stades s ON m.stade_id = s.id
    LEFT JOIN arbitres a ON m.arbitre_id = a.id
    ORDER BY m.date_match DESC
")->fetchAll(PDO::FETCH_ASSOC);


// Récupération des arbitres pour l'affectation
$arbitres = $pdo->query("SELECT id, nom FROM arbitres ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);


// Ajouter un match
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_match'])) {
    $equipe1_id = $_POST['equipe1'];
    $equipe2_id = $_POST['equipe2'];
    $date_match = $_POST['date_match'];
    $heure_match = $_POST['heure'];
    $stade_id = $_POST['stade'];
    $arbitre_id = !empty($_POST['arbitre_id']) ? $_POST['arbitre_id'] : NULL;
    $tournoi_id = 1; // Tournoi par défaut  

    if ($equipe1_id == $equipe2_id) {
        $error = "Une équipe ne peut pas jouer contre elle-même.";
    } elseif (empty($heure_match) || empty($stade_id)) {
        $error = "L'heure et le stade du match sont obligatoires.";
    } else {
        //  Vérifier si la requête SQL est bien préparée
        $stmt = $pdo->prepare("INSERT INTO matches (equipe1_id, equipe2_id, date_match, heure, stade_id, arbitre_id, tournoi_id) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)");

        if ($stmt) { // Vérification avant d'exécuter
            $stmt->execute([$equipe1_id, $equipe2_id, $date_match, $heure_match, $stade_id, $arbitre_id , 1]);

            //  Insérer les joueurs sélectionnés après l'ajout du match
            $match_id = $pdo->lastInsertId(); // Récupérer l'ID du match inséré

            // Insérer les joueurs sélectionnés avec leur position
   if (!empty($_POST['joueurs'])) {
       $stmt_joueur = $pdo->prepare("INSERT INTO match_joueurs (match_id, joueur_id, position) VALUES (?, ?, ?)");
    foreach ($_POST['joueurs'] as $joueur_id) {
        $position = $_POST['position'][$joueur_id]; // Récupérer la position du joueur
        $stmt_joueur->execute([$match_id, $joueur_id, $position]);
    }
}

            redirect("admin_matchs.php");
        } else {
            $error = "Erreur lors de la préparation de la requête d'ajout de match.";
        }
    }
}

// if ($stmt->execute([$equipe1_id, $equipe2_id, $date_match, $heure_match, $stade_id, $arbitre_id])) {
//     $match_id = $pdo->lastInsertId(); // Récupérer l'ID du match inséré

//     // Vérifier si des joueurs ont été sélectionnés
//     if (!empty($_POST['joueurs'])) {
//         $stmt_joueur = $pdo->prepare("INSERT INTO match_joueurs (match_id, joueur_id) VALUES (?, ?)");
//         foreach ($_POST['joueurs'] as $joueur_id) {
//             $stmt_joueur->execute([$match_id, $joueur_id]);
//         }
//     }

//     redirect("admin_matchs.php");
// } else {
//     $error = "Erreur lors de l'ajout du match.";
// }



// Ajouter un match avec un arbitre
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_match'])) {
  $equipe1_id = $_POST['equipe1'];
  $equipe2_id = $_POST['equipe2'];
  $date_match = $_POST['date_match'];
  $heure_match = $_POST['heure'];
  $stade_id = $_POST['stade'];
  $arbitre_id = !empty($_POST['arbitre_id']) ? $_POST['arbitre_id'] : NULL;


  if ($equipe1_id == $equipe2_id) {
      $error = "Une équipe ne peut pas jouer contre elle-même.";
  } elseif (empty($heure_match) || empty($stade_id)) {
      $error = "L'heure et le stade du match sont obligatoires.";
  } else {
      $stmt = $pdo->prepare("INSERT INTO matches (equipe1_id, equipe2_id, date_match, heure, stade_id, arbitre_id) VALUES (?, ?, ?, ?, ?, ?)");
      if ($stmt->execute([$equipe1_id, $equipe2_id, $date_match, $heure_match, $stade_id, $arbitre_id])) {
        redirect("admin_matchs.php");

      } else {
          $error = "Erreur lors de l'ajout du match.";
      }
  }
}



// Supprimer un match
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['supprimer_match'])) {
    $stmt = $pdo->prepare("DELETE FROM matches WHERE id = ?");
    $stmt->execute([$_POST['match_id']]);
    redirect("admin_matchs.php");

}



// Modifier un match avec l'arbitre
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_match'])) {
  $match_id = $_POST['match_id'];
  $equipe1_id = $_POST['equipe1'];
  $equipe2_id = $_POST['equipe2'];
  $date_match = $_POST['date_match'];
  $heure_match = $_POST['heure'];
  $stade_id = $_POST['stade'];
  $arbitre_id = !empty($_POST['arbitre_id']) ? $_POST['arbitre_id'] : NULL;

  if ($equipe1_id == $equipe2_id) {
      $error = "Une équipe ne peut pas jouer contre elle-même.";
  } elseif (empty($heure_match) || empty($stade_id)) {
      $error = "L'heure et le stade du match sont obligatoires.";
  } else {
      $stmt = $pdo->prepare("
          UPDATE matches 
          SET equipe1_id = ?, equipe2_id = ?, date_match = ?, heure = ?, stade_id = ?, arbitre_id = ?
          WHERE id = ?
      ");
      if ($stmt->execute([$equipe1_id, $equipe2_id, $date_match, $heure_match, $stade_id, $arbitre_id, $match_id])) {
        redirect("admin_matchs.php");


        
      } else {
          $error = "Erreur lors de la modification du match.";
      }
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Matchs</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
  
<div class="container mt-5">
    <h2 class="text-center">Gestion des Matchs</h2>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Bouton Ajouter -->
    <div class="d-flex justify-content-between mb-3">
        <h4>Liste des Matchs</h4>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMatchModal">+ Ajouter un Match</button>
    </div>

    <!-- Tableau des matchs -->
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Équipe 1</th>
                <th>Équipe 2</th>
                <th>Date & Heure</th>
                <th>Arbitre</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($matchs as $index => $match) : ?>
              <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($match['equipe1']) ?></td>
                  <td><?= htmlspecialchars($match['equipe2']) ?></td>
                  <td><?= htmlspecialchars($match['date_match'] . ' ' . $match['heure']) ?></td>
                  <td><?= !empty($match['arbitre']) ? htmlspecialchars($match['arbitre']) : "Non assigné" ?></td>

                  <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editMatchModal<?= $match['id'] ?>">Modifier</button>
                    <form method="post" class="d-inline">
                      <input type="hidden" name="match_id" value="<?= $match['id'] ?>">
                      <button type="submit" name="supprimer_match" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment supprimer ce match ?');">Supprimer</button>
                    </form>
                 </td>
              </tr>
             


         <!-- Modal Modifier Match -->
    <div class="modal fade" id="editMatchModal<?= $match['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier le Match</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="match_id" value="<?= $match['id'] ?>">

                        <label>Équipe 1</label>
                    <select name="equipe1" class="form-control equipe1" data-match-id="<?= $match['id'] ?>" required>
                                <option value="">-- Sélectionner --</option>
                   <?php foreach ($equipes as $equipe) : ?>
                               <option value="<?= $equipe['id'] ?>" <?= ($equipe['id'] == $match['equipe1_id']) ? 'selected' : '' ?>>
                               <?= htmlspecialchars($equipe['nom']) ?>
                               </option>
                   <?php endforeach; ?>
                   </select>

                <label>Équipe 2</label>
                 <select name="equipe2" class="form-control equipe2" data-match-id="<?= $match['id'] ?>" required>
                      <option value="">-- Sélectionner --</option>
                     <?php foreach ($equipes as $equipe) : ?>
                        <option value="<?= $equipe['id'] ?>" <?= ($equipe['id'] == $match['equipe2_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($equipe['nom']) ?>
                        </option>
                     <?php endforeach; ?>
                </select>


              <label>Date du Match</label>
                <input type="date" name="date_match" class="form-control" value="<?= $match['date_match'] ?>" required>

              <label>Heure du Match</label>
                 <input type="time" name="heure" class="form-control" value="<?= $match['heure'] ?>" required>

             <label>Stade</label>
                 <select name="stade" class="form-control" required>
                     <option value="">-- Sélectionner --</option>
                 <?php
                   $stades = $pdo->query("SELECT id, nom FROM stades ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
               foreach ($stades as $stade) : ?>
                 <option value="<?= $stade['id'] ?>"><?= htmlspecialchars($stade['nom']) ?></option>
               <?php endforeach; ?>
               </select>

               <label>Arbitre</label>

           <select name="arbitre_id" class="form-control">
             <option value="">-- Sélectionner --</option>
             <?php foreach ($arbitres as $arbitre) : ?>
              <option value="<?= $arbitre['id'] ?>" <?= (isset($match['arbitre_id']) && $arbitre['id'] == $match['arbitre_id']) ? 'selected' : '' ?>>
             <?= htmlspecialchars($arbitre['nom']) ?>
            </option>
            <?php endforeach; ?>
          </select>   
          </div>


                     <div class="modal-footer">
                         <button type="submit" name="modifier_match" class="btn btn-success">Enregistrer</button>
                    </div>
                </form>
             </div>
         </div>
      </div>
    <?php endforeach; ?>

        </tbody>
    </table>
</div>



         <!-- Modal Ajouter un match -->
<div class="modal fade" id="addMatchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Match</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <label>Équipe 1</label>
                    <select name="equipe1" id="equipe1" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($equipes as $equipe) : ?>
                            <option value="<?= $equipe['id'] ?>"><?= htmlspecialchars($equipe['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Équipe 2</label>
                    <select name="equipe2" id="equipe2" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($equipes as $equipe) : ?>
                            <option value="<?= $equipe['id'] ?>"><?= htmlspecialchars($equipe['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>

            

                    

                <label>Date du Match</label>
                <input type="date" name="date_match" class="form-control" required>

                <label>Heure du Match</label>
                <input type="time" name="heure" class="form-control" required>

                <label>Stade</label>
                <select name="stade" id="stade" class="form-control" required>
                  <option value="">-- Sélectionner --</option>
                </select>            

        <label>Arbitre</label>
           <select name="arbitre_id" class="form-control">
             <option value="">-- Sélectionner --</option>
                 <?php foreach ($arbitres as $arbitre) : ?>
            <option value="<?= $arbitre['id'] ?>"><?= htmlspecialchars($arbitre['nom']) ?></option>
                 <?php endforeach; ?>
         </select>
         </div>

    <div id="joueursSelection" style="display:none;">
        <h5 class="mt-3">Sélectionner les joueurs et leurs positions</h5>
    
        <label>Joueurs de l'Équipe 1</label>
         <div id="joueursEquipe1" class="border p-2 mb-3"></div>

           <label>Joueurs de l'Équipe 2</label>
      <div id="joueursEquipe2" class="border p-2 mb-3"></div>
    </div>

     <div class="modal-footer">
                     <button type="submit" name="ajouter_match" class="btn btn-success">Ajouter</button>
                </div>
            </form>
         </div>
     </div>
    </div>





<!-- Script pour charger les stades et les joueurs et leur position -->
<script>
function chargerInfosMatch() {
    let equipe1 = document.getElementById("equipe1").value;
    let equipe2 = document.getElementById("equipe2").value;
    
    chargerStades(equipe1, equipe2);
    chargerJoueurs(equipe1, equipe2);
}

//  Charger les stades des équipes sélectionnées
function chargerStades(equipe1, equipe2) {
    let stadeSelect = document.getElementById("stade");

    if (equipe1 && equipe2) {
        fetch(`get_stades.php?equipe1=${equipe1}&equipe2=${equipe2}`)
            .then(response => response.json())
            .then(data => {
                stadeSelect.innerHTML = '<option value="">-- Sélectionner --</option>';
                data.forEach(stade => {
                    stadeSelect.innerHTML += `<option value="${stade.id}">${stade.nom}</option>`;
                });
            })
            .catch(error => console.error("Erreur lors du chargement des stades :", error));
    } else {
        stadeSelect.innerHTML = '<option value="">-- Sélectionner --</option>';
    }
}

//  Charger les joueurs des équipes sélectionnées
function chargerJoueurs(equipe1, equipe2) {
    let joueursEquipe1Div = document.getElementById("joueursEquipe1");
    let joueursEquipe2Div = document.getElementById("joueursEquipe2");
    let joueursSelectionDiv = document.getElementById("joueursSelection");
    

    if (equipe1 && equipe2) {
        joueursSelectionDiv.style.display = "block"; // Afficher la section joueurs

        fetch(`get_joueurs.php?equipe1=${equipe1}&equipe2=${equipe2}`)
            .then(response => response.json())
            .then(data => {
                joueursEquipe1Div.innerHTML = "<p><strong>Joueurs Équipe 1</strong></p>";
                data.equipe1.forEach(joueur => {
                    joueursEquipe1Div.innerHTML += `
                        <input type="checkbox" name="joueurs[]" value="${joueur.id}"> 
                        ${joueur.nom} ${joueur.prenom} 
                        <select name="position[${joueur.id}]" class="form-select d-inline-block w-auto">
                            <option value="Attaquant">Attaquant</option>
                            <option value="Ailier droit">Ailier droit</option>
                            <option value="Ailier gauche">Ailier gauche</option>
                            <option value="Milieu défensif">Milieu défensif</option>
                            <option value="Milieu central">Milieu central</option>
                            <option value="Milieu offensif">Milieu offensif</option>
                            <option value="Défenseur central">Défenseur central</option>
                            <option value="Défenseur droit">Défenseur droit</option>
                            <option value="Défenseur gauche">Défenseur gauche</option>
                            <option value="Gardien">Gardien</option>
                        </select>
                        <br>`;
                });

                joueursEquipe2Div.innerHTML = "<p><strong>Joueurs Équipe 2</strong></p>";
                data.equipe2.forEach(joueur => {
                    joueursEquipe2Div.innerHTML += `
                        <input type="checkbox" name="joueurs[]" value="${joueur.id}"> 
                        ${joueur.nom} ${joueur.prenom} 
                        <select name="position[${joueur.id}]" class="form-select d-inline-block w-auto">
                           <option value="Attaquant">Attaquant</option>
                            <option value="Ailier droit">Ailier droit</option>
                            <option value="Ailier gauche">Ailier gauche</option>
                            <option value="Milieu défensif">Milieu défensif</option>
                            <option value="Milieu central">Milieu central</option>
                            <option value="Milieu offensif">Milieu offensif</option>
                            <option value="Défenseur central">Défenseur central</option>
                            <option value="Défenseur droit">Défenseur droit</option>
                            <option value="Défenseur gauche">Défenseur gauche</option>
                            <option value="Gardien">Gardien</option>
                        </select>
                        <br>`;
                });
            })
            .catch(error => console.error("Erreur lors du chargement des joueurs :", error));
    } else {
        joueursSelectionDiv.style.display = "none";
    }
}

//  Ajouter les écouteurs d'événements
document.getElementById("equipe1").addEventListener("change", chargerInfosMatch);
document.getElementById("equipe2").addEventListener("change", chargerInfosMatch);
</script>


</body>
</html>
