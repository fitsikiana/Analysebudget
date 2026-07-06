<?php
// actions/se_connecter.php
session_start();

// Ampidirina ny rakitra fifandraisana amin'ny database
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['connexion'])) {
    
    // Fakana sy fanadiovana ny angon-drakitra avy amin'ny formulaire
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mot_de_passe = isset($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';

    // 1. Fanamarinana raha misy champ banga
    if (empty($email) || empty($mot_de_passe)) {
        header("Location: ../pages/acceuil.php?error=banga");
        exit();
    }

    try {
        // 2. Fitadiavana ilay mpampiasa ao amin'ny database amin'ny alalan'ny Email
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 3. Fanamarinana raha nisy ilay email
        if ($user) {
            // Fanamarinana raha mifanaraka ny mot de passe (haché)
            if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
                
                // 4. Tehirizina ao anatin'ny SESSION ny mombamomba azy
                $_SESSION['id_utilisateur']  = $user['id_utilisateur'];
                $_SESSION['email']           = $user['email'];
                $_SESSION['nom_utilisateur'] = $user['nom'];

                // Redirection mankany amin'ny dashboard rehefa tafiditra soa aman-tsara
                header("Location: ../pages/dashboard.php");
                exit();
            } else {
                // Raha diso ny mot de passe
                header("Location: ../pages/acceuil.php?error=password_false&email=" . urlencode($email));
                exit();
            }
        } else {
            // Raha tsy misy ilay email
            header("Location: ../pages/acceuil.php?error=email n'existe pas");
            exit();
        }

    } catch (PDOException $e) {
        // Raha misy olana ara-teknika eo amin'ny Database
        header("Location: ../pages/acceuil.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Raha misy mitady hiditra amin'ity rakitra ity nefa tsy avy amin'ny formulaire POST
    header("Location: ../pages/acceuil.php");
    exit();
}