<?php
session_start();

date_default_timezone_set('Africa/Casablanca'); // Ajoutez cette ligne


require_once '../config/database.php';

// Récupérer la date du jour
$today = date('Y-m-d');

$queryBotola = "SELECT m.date_match, m.heure, m.id, 
e1.nom AS equipe1, e2.nom AS equipe2, 
e1.logo AS logo1, e2.logo AS logo2, 
m.score_equipe1, m.score_equipe2
FROM matches m
JOIN equipes e1 ON m.equipe1_id = e1.id
JOIN equipes e2 ON m.equipe2_id = e2.id
WHERE DATE(m.date_match) = ? 
AND m.tournoi_id = 1";

$stmtBotola = $pdo->prepare($queryBotola);
$stmtBotola->execute([$today]);
$matchs_botola = $stmtBotola->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si les matchs sont bien récupérés



//  Récupérer les matchs du jour pour Kass L3arch (tournoi_id = 2)
$queryKassL3arch = "SELECT m.date_match, m.heure, m.id, e1.logo AS logo1, e2.logo AS logo2 
                    FROM matches m
                    JOIN equipes e1 ON m.equipe1_id = e1.id
                    JOIN equipes e2 ON m.equipe2_id = e2.id
                    WHERE DATE(m.date_match) = ? 
                    AND m.tournoi_id = 2 
                    AND (m.score_equipe1 IS NULL OR m.score_equipe2 IS NULL OR m.score_equipe1 = 0 OR m.score_equipe2 = 0)";

$stmtKassL3arch = $pdo->prepare($queryKassL3arch);
$stmtKassL3arch->execute([$today]);
$matchs_kass_l3arch = $stmtKassL3arch->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scores Matches</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="../public/assets/css/index.css">
    
    <!-- Custom Styles -->
</head>
<body>
<?php include 'navbar.php'; ?>



<!-- Section Hero (Doit être après la Navbar) -->
<section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1>Bienvenue sur Scores Matches </h1>
        <p>Suivez les scores et résultats en temps réel !</p>
        <a href="calendrier.php" class="hero-button">Voir le Calendrier</a>
    </div>
</section>



<!--  Section des Matchs du Jour -->
<section class="py-5">
    <div class="container">
        <h2 class="mb-4 text-center"> Matchs du Jour</h2>

        <!--  Section des matchs de Botola -->
        <h3 class="mb-3 text-primary">🏆 Botola Pro</h3>
        <div class="match-list">
            <?php if (count($matchs_botola) > 0) : ?>
                <?php foreach ($matchs_botola as $match) : ?>
                    <div class="match-item">
                        <div class="match-info">
                            <?php
                            $date_formatee = date('d/m/Y', strtotime($match['date_match']));
                            $heure_formatee = (!empty($match['heure']) && $match['heure'] !== "00:00:00") ? date('H:i', strtotime($match['heure'])) : '';
                            ?>
                            <span class="match-date"><?= $date_formatee . (!empty($heure_formatee) ? " " . $heure_formatee : '') ?></span>
                        </div>
                        <div class="match-content">
                            <div class="team">
                                <img src="<?= htmlspecialchars($match['logo1']) ?>" alt="Équipe 1">
                            </div>
                            <div class="match-score">VS</div>
                            <div class="team">
                                <img src="<?= htmlspecialchars($match['logo2']) ?>" alt="Équipe 2">
                            </div>
                        </div>
                        <a href="match_details.php?id=<?= $match['id'] ?>" class="match-details-btn">Détails</a>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p class="text-muted text-center">Aucun match aujourd'hui.</p>
            <?php endif; ?>
        </div>

        <!-- Section des matchs de Kass L3arch -->
        <h3 class="mt-5 mb-3 text-success">🏆 Kass L3arch</h3>
        <div class="match-list">
            <?php if (count($matchs_kass_l3arch) > 0) : ?>
                <?php foreach ($matchs_kass_l3arch as $match) : ?>
                    <div class="match-item">
                        <div class="match-info">
                            <?php
                            $date_formatee = date('d/m/Y', strtotime($match['date_match']));
                            $heure_formatee = (!empty($match['heure']) && $match['heure'] !== "00:00:00") ? date('H:i', strtotime($match['heure'])) : '';
                            ?>
                            <span class="match-date"><?= $date_formatee . (!empty($heure_formatee) ? " " . $heure_formatee : '') ?></span>
                        </div>
                        <div class="match-content">
                            <div class="team">
                                <img src="<?= htmlspecialchars($match['logo1']) ?>" alt="Équipe 1">
                            </div>
                            <div class="match-score">VS</div>
                            <div class="team">
                                <img src="<?= htmlspecialchars($match['logo2']) ?>" alt="Équipe 2">
                            </div>
                        </div>
                        <a href="match_details.php?id=<?= $match['id'] ?>" class="match-details-btn">Détails</a>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p class="text-muted text-center">Aucun match aujourd'hui.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

 <!-- Section Publications -->
 <?php
// Récupérer les publications depuis la base de données
$query = "SELECT * FROM publications ORDER BY date_publication DESC "; // Limite à 6 publications
$publications = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>




<!-- Section Publications -->
<section class="py-5 publications">
    <div class="container">
        <h2 class="mb-4 text-center">Publications</h2>
        <div class="row">
            <?php foreach ($publications as $publication) : ?>
                <div class="col-md-12 mb-3">
                    <div class="publication-card d-flex align-items-center p-3 shadow">
                        <img src="<?= !empty($publication['image']) ? '../public/assets/images/' . htmlspecialchars($publication['image']) : '../public/assets/images/default.png'; ?>" class="publication-img" alt="Image">
                        <div class="publication-content">
                            <a href="publication_details.php?id=<?= $publication['id'] ?>" class="publication-title"><?= htmlspecialchars($publication['titre']) ?></a>
                            <p class="publication-meta">Publié le <?= date('d.m.Y H:i', strtotime($publication['date_publication'])) ?></p>
                            <p class="text-muted"><?= htmlspecialchars(substr($publication['contenu'], 0, 100)) ?>...</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
    




<!-- Pied de page -->
<footer class="bg-dark text-white pt-5 pb-3">
    <div class="container">
        <div class="row">
            <!-- Section Botola Pro -->
            <div class="col-md-3">
                <h5 class="text-uppercase">Botola Pro</h5>
                <ul class="list-unstyled">
                    <li><a href="https://frmf.ma/fr/competitions-2/botola-d1/" class="text-white text-decoration-none">Actualités</a></li>
                    <li><a href="https://frmf.ma/fr/competitions-2/botola-d1/" class="text-white text-decoration-none">Classement</a></li>
                    <li><a href="https://frmf.ma/fr/competitions-2/botola-d1/" class="text-white text-decoration-none">Calendrier</a></li>
                    <li><a href="https://frmf.ma/fr/competitions-2/botola-d1/" class="text-white text-decoration-none">Résultats</a></li>
                </ul>
            </div>

            <!-- Section Équipes -->
            <div class="col-md-3">
                <h5 class="text-uppercase">Équipes</h5>
                <ul class="list-unstyled">
                    <li><a href="https://frmf.ma/fr/clubs/wydad-ac/" class="text-white text-decoration-none">Wydad AC</a></li>
                    <li><a href="https://frmf.ma/fr/clubs/raja-ca/" class="text-white text-decoration-none">Raja CA</a></li>
                    <li><a href="https://frmf.ma/fr/clubs/as-far/" class="text-white text-decoration-none">AS FAR</a></li>
                    <li><a href="https://frmf.ma/fr/clubs/rs-berkane/" class="text-white text-decoration-none">RS Berkane</a></li>
                </ul>
            </div>

            <!-- Section Compétitions -->
            <div class="col-md-3">
                <h5 class="text-uppercase">Compétitions</h5>
                <ul class="list-unstyled">
                    <li><a href="https://frmf.ma/fr/competitions-2/coupe-du-trone/" class="text-white text-decoration-none">Coupe du Trône</a></li>
                    <li><a href="https://frmf.ma/fr/competitions-2/lnfp/" class="text-white text-decoration-none">LNFP</a></li>
                    <li><a href="https://frmf.ma/fr/competitions-2/football-feminin/" class="text-white text-decoration-none">Football Féminin</a></li>
                    <li><a href="https://frmf.ma/fr/competitions-2/futsal/" class="text-white text-decoration-none">Futsal</a></li>
                </ul>
            </div>

            <!-- Section Contact & Réseaux sociaux -->
            <div class="col-md-3">
                <h5 class="text-uppercase">Contact</h5>
                <ul class="list-unstyled">
                    <li><a href="https://frmf.ma/fr/contact/" class="text-white text-decoration-none">À propos</a></li>
                    <li><a href="https://frmf.ma/fr/contact/" class="text-white text-decoration-none">Politique de confidentialité</a></li>
                    <li><a href="https://frmf.ma/fr/contact/" class="text-white text-decoration-none">Conditions d'utilisation</a></li>
                    <li><a href="https://frmf.ma/fr/contact/" class="text-white text-decoration-none">Contactez-nous</a></li>
                </ul>
                <div class="mt-3">
                    <a href="https://www.facebook.com/FRMFOFFICIEL/" class="text-white me-2"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="https://twitter.com/FRMFOFFICIEL" class="text-white me-2"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="https://www.instagram.com/frmfofficiel/" class="text-white me-2"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="https://www.youtube.com/channel/UCy0uvytQz4T5ZxJ4rMBUuVg" class="text-white me-2"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="text-center mt-4">
            <p class="mb-0">&copy; 2025 Botola Pro Maroc - Tous droits réservés.</p>
        </div>
    </div>
</footer>


<!-- Font Awesome pour les icônes -->
<script src="https://kit.fontawesome.com/yourkitid.js" crossorigin="anonymous"></script>


<!-- JavaScript pour le Mode Sombre -->
<script>
  function toggleTheme() {
    document.body.classList.toggle("dark-mode");
    document.body.classList.toggle("light-mode");

    let isDarkMode = document.body.classList.contains("dark-mode");
    localStorage.setItem("theme", isDarkMode ? "dark" : "light");
  }

  document.addEventListener("DOMContentLoaded", function () {
    if (localStorage.getItem("theme") === "dark") {
      document.body.classList.add("dark-mode");
    } else {
      document.body.classList.add("light-mode");
    }
  });
</script>


<!-- Bootstrap JS -->
<script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>