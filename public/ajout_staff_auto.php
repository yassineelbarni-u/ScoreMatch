<?php
require_once '../config/database.php';

// Liste de noms et prénoms réalistes
$noms = ["Dupont", "Martin", "Durand", "Bernard", "Morel", "Girard", "Lambert", "Rousseau", "Garnier", "Leclerc"];
$prenoms = ["Jean", "Paul", "Luc", "Michel", "Pierre", "Nicolas", "Antoine", "Mathieu", "Julien", "Olivier"];
$roles = ["Entraîneur", "Assistant coach", "Préparateur physique", "Médecin", "Analyste vidéo", "Responsable équipement"];

// Récupérer les équipes
$equipes = $pdo->query("SELECT id FROM equipes")->fetchAll(PDO::FETCH_ASSOC);

foreach ($equipes as $equipe) {
    $equipe_id = $equipe['id'];

    // Générer 4 membres de staff différents pour chaque équipe
    $staff_roles = array_rand(array_flip($roles), 4); // Sélectionne 4 rôles uniques
    foreach ($staff_roles as $role) {
        $nom = $noms[array_rand($noms)];
        $prenom = $prenoms[array_rand($prenoms)];

        // Ajouter le membre du staff à la base de données
        $stmt = $pdo->prepare("INSERT INTO staff (nom, prenom, role, equipe_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $role, $equipe_id]);
    }
}

echo "✅ 4 membres de staff générés pour chaque équipe avec des noms et rôles réalistes !";
?>
