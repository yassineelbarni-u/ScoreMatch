<?php
$servername = "localhost"; // Adresse du serveur MySQL
$username = "root"; // Nom d'utilisateur MySQL
$password = ""; // Mot de passe MySQL
$dbname = "score_matches"; // Base de données correcte

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Récupérer les IDs des 17 équipes existantes
$equipes = [];
$result = $conn->query("SELECT id FROM equipes");
while ($row = $result->fetch_assoc()) {
    $equipes[] = $row['id'];
}

// Vérifier qu'on a bien 17 équipes
if (count($equipes) < 17) {
    die("Erreur : Moins de 17 équipes trouvées dans la table `equipes`.");
}

// Optionnel : Supprimer les anciens joueurs pour éviter les doublons
$conn->query("DELETE FROM joueurs");

// Liste de base des joueurs (nom et prénom séparés)
$base_joueurs = [
    ["Nouaman", "Aarab"], ["Soufiane", "Abaaziz"], ["Reda", "Abardi"], ["Faouzi", "Abd El Mouttalib"],
    ["Mourad", "Abdelwadie"], ["Mohamed Yassine", "Abouzraa"], ["Badreddine", "Abyir"], ["Salmane", "Achiban"],
    ["Marouane", "Afallah"], ["Hamza", "Afsal"], ["Youssef", "Aguerdoum El Idrissi"], ["Hamid", "Ahadad"],
    ["Soufyan", "Ahannach"], ["Mohamed Aymane", "Ahaytaf"], ["Haytam", "Aina"], ["Hamza", "Ait Allal"],
    ["Bilal", "Ait Allal"], ["Abdelali", "Ait Brayim"], ["Soulaymane", "Ait Dani"], ["Anas", "Ait Jilal"],
    ["Saad", "Ait Khorsa"], ["Redouane", "Ait Lemkadem"], ["Karim", "Ait Mohamed"], ["Khalid", "Aït Ouarkhane"],
    ["Ayoub", "Ait Wahmane"], ["James", "Ajako"], ["Zakaria", "Ajoughlal"], ["Younes", "Akharraz"],
    ["Karim", "Al Achkar"], ["Oussama", "Al Aiz"], ["Ismail", "Al Alami"], ["Mohamed", "Al Cheikhi"],
    ["Hadi Omar", "Ahmed Al Hourani"], ["Adam", "Al Khalfi"], ["Akram", "Al Nakach"], ["Oualid", "Alaoui"],
    ["Ayoub", "Aloum"], ["Heni", "Amamou"], ["Houcine", "Amantag"], ["Sidi Bouna", "Amar"]
];

// Générer 187 joueurs en dupliquant et modifiant les noms
$joueurs = [];
while (count($joueurs) < 187) {
    foreach ($base_joueurs as $joueur) {
        if (count($joueurs) >= 187) break;
        // Ajouter un numéro aléatoire pour éviter les doublons exacts
        $joueurs[] = [$joueur[0] . " " . rand(1, 99), $joueur[1]];
    }
}

// Mélanger la liste des joueurs
shuffle($joueurs);

// Liste des positions possibles
$positions = [
    "Gardien", "Défenseur central", "Défenseur gauche", "Défenseur droit",
    "Milieu défensif", "Milieu central", "Milieu offensif",
    "Ailier gauche", "Ailier droit", "Attaquant"
];

// Insérer les joueurs dans la base en les répartissant sur les équipes
foreach ($joueurs as $index => $joueur) {
    $nom = $joueur[0];
    $prenom = $joueur[1];
    $age = rand(18, 35); // Génération d'un âge aléatoire
    $position = $positions[array_rand($positions)]; // Position aléatoire
    $equipe_id = $equipes[$index % count($equipes)]; // Distribution équilibrée

    // Insérer dans la base de données
    $sql = "INSERT INTO joueurs (nom, prenom, age, position, equipe_id) VALUES ('$nom', '$prenom', '$age', '$position', '$equipe_id')";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Joueur $nom $prenom ajouté à l'équipe ID $equipe_id en tant que $position.<br>";
    } else {
        echo "❌ Erreur : " . $conn->error . "<br>";
    }
}

// Fermer la connexion
$conn->close();
?>
