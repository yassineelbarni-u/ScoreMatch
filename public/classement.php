<?php
session_start();
require_once '../config/database.php';

// Récupérer les statistiques des équipes
$query = "SELECT 
            e.id, e.nom, e.logo,
            COUNT(m.id) AS matches_joues,
            COALESCE(SUM(
                CASE 
                    WHEN m.score_equipe1 > m.score_equipe2 AND m.equipe1_id = e.id THEN 3 
                    WHEN m.score_equipe2 > m.score_equipe1 AND m.equipe2_id = e.id THEN 3 
                    WHEN m.score_equipe1 = m.score_equipe2 AND (m.equipe1_id = e.id OR m.equipe2_id = e.id) THEN 1 
                    ELSE 0 
                END
            ), 0) AS points,
            COALESCE(SUM(
                CASE 
                    WHEN m.score_equipe1 > m.score_equipe2 AND m.equipe1_id = e.id THEN 1
                    WHEN m.score_equipe2 > m.score_equipe1 AND m.equipe2_id = e.id THEN 1
                    ELSE 0
                END
            ), 0) AS victoires,
            COALESCE(SUM(
                CASE 
                    WHEN m.score_equipe1 = m.score_equipe2 AND (m.equipe1_id = e.id OR m.equipe2_id = e.id) THEN 1
                    ELSE 0
                END
            ), 0) AS nuls,
            COALESCE(SUM(
                CASE 
                    WHEN m.score_equipe1 < m.score_equipe2 AND m.equipe1_id = e.id THEN 1
                    WHEN m.score_equipe2 < m.score_equipe1 AND m.equipe2_id = e.id THEN 1
                    ELSE 0
                END
            ), 0) AS défaites
        FROM equipes e
        LEFT JOIN matches m ON (e.id = m.equipe1_id OR e.id = m.equipe2_id)
        GROUP BY e.id, e.nom, e.logo
        ORDER BY points DESC, victoires DESC, nuls DESC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$classement = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Classement</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">

    <style>
        body {
            background-color: #121212;
            color: white;
        }

        .container {
            margin-top: 30px;
        }

        .classement-table {
            width: 100%;
            max-width: 800px;
            margin: auto;
            background-color: #1c1c1c;
            border-radius: 8px;
            padding: 15px;
        }

        .table thead {
            background-color: #FF5722;
            color: white;
        }

        .table tbody tr {
            background-color: #2a2a2a;
            transition: 0.3s;
        }

        .table tbody tr:hover {
            background-color: #3a3a3a;
        }

        .team-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .team-logo {
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }
        

    </style>
</head>
<body>

<!-- Inclure la barre de navigation -->
<?php include 'navbar.php'; ?>

<div class="container">
    <h2 class="text-center">Classement des Équipes</h2>

    <table class="table classement-table text-center">
        <thead>
            <tr>
                <th>Équipe</th>
                <th>J</th> <!-- Nombre de matchs joués -->
                <th>Pts</th>
                <th>V</th>
                <th>N</th>
                <th>D</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($classement as $equipe): ?>
                <tr>
                    <td class="team-info">
                        <img src="<?= htmlspecialchars($equipe['logo']) ?>" alt="Logo" class="team-logo">
                        <?= htmlspecialchars($equipe['nom']) ?>
                    </td>
                    <td><?= htmlspecialchars($equipe['matches_joues']) ?></td> <!-- Ajout des matchs joués -->
                    <td><?= htmlspecialchars($equipe['points']) ?></td>
                    <td><?= htmlspecialchars($equipe['victoires']) ?></td>
                    <td><?= htmlspecialchars($equipe['nuls']) ?></td>
                    <td><?= htmlspecialchars($equipe['défaites']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
