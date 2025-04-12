<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['receiver_id']) ? $_GET['receiver_id'] : null;

if (!$receiver_id) {
  header("Location: chat_list.php"); 
  exit();
}

// recuperer les message entre les users
$stmt = $pdo->prepare("SELECT m.*, u1.nom AS sender_name, u2.nom AS receiver_name 
                       FROM messages m
                       JOIN users u1 ON m.sender_id = u1.id
                       JOIN users u2 ON m.receiver_id = u2.id
                       WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                       OR (m.sender_id = ? AND m.receiver_id = ?)
                       ORDER BY m.date_sent ASC");
$stmt->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mettre à jour l'état des messages en tant que lus
$stmt_update = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
$stmt_update->execute([$receiver_id, $user_id]);

// Gérer l'envoi de message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];

    // Vérifier que le receiver_id est bien défini
    if ($receiver_id) {

        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, date_sent, is_read) 
                               VALUES (?, ?, ?, NOW(), 0)");
        $stmt->execute([$user_id, $receiver_id, $message]);

        // Rediriger après envoi du message
        header("Location: chat.php?receiver_id=" . $receiver_id);
        exit();
    } else {
        echo "Erreur : L'utilisateur sélectionné pour le chat n'est pas valide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <!-- Affichage des messages -->
        <div class="chat-box" id="chat-box">
            <?php 
                $last_date = null;
                foreach ($messages as $message) : 
                    $message_date = date('d-m-Y', strtotime($message['date_sent']));
                    // Afficher la date uniquement si elle change
                    if ($last_date != $message_date) : 
                        $last_date = $message_date;
            ?>
                <p class="message-time"><strong><?php echo $message_date; ?></strong></p>
            <?php endif; ?>
                
            <div class="message <?php echo ($message['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                <?php if ($message['sender_id'] != $user_id) : ?>
                    <p><strong><?php echo htmlspecialchars($message['sender_name']); ?>:</strong></p>
                <?php endif; ?>
                <p><?php echo htmlspecialchars($message['message']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Formulaire d'envoi de message -->
        <div class="chat-input">
            <form action="chat.php?receiver_id=<?php echo $receiver_id; ?>" method="POST" id="chat-form">
                <textarea name="message" class="form-control" placeholder="Écrivez votre message..." required></textarea>
                <button type="submit">Envoyer</button>
            </form>
        </div>
    </div>

    <script>

        // Scroll to the bottom when the page loads
        window.onload = function() {
            let chatBox = document.getElementById("chat-box");
            chatBox.scrollTop = chatBox.scrollHeight;
        };

        // Handle form submission via AJAX
        document.getElementById("chat-form").onsubmit = function(event) {
            event.preventDefault(); // Prevent the page from reloading
            let formData = new FormData(this);

            // Send message using Fetch API
            fetch('chat.php?receiver_id=<?php echo $receiver_id; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text()) 
            .then(responseText => {

                if (responseText.includes('success')) {
                    let chatBox = document.getElementById("chat-box");
                    let newMessage = document.createElement("div");
                    newMessage.classList.add("message", "sent");
                    newMessage.innerHTML = `
                        <p><strong>Vous:</strong></p>
                        <p>${document.querySelector("textarea").value}</p>
                    `;
                    chatBox.appendChild(newMessage);
                    chatBox.scrollTop = chatBox.scrollHeight; 
                    document.querySelector("textarea").value = ''; 
                }
            })
            .catch(error => {
                console.error("Error while sending message: ", error);
            });
        };
    </script>
</body>
</html>
