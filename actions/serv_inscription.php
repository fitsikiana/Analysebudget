<?php
// actions/serv_inscription.php
session_start();

include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_utilisateur'])) {
    
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mot_de_passe = isset($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';

    if (empty($nom) || empty($email) || empty($mot_de_passe)) {
        header("Location: ../pages/dashboard.php?tab=gestion-utilisateurs&error=" . urlencode("Veuillez remplir tous les champs obligatoires."));
        exit();
    }

    try {
        $stmtCheck = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = ? OR nom = ?");
        $stmtCheck->execute([$email, $nom]);
        
        if ($stmtCheck->fetch()) {
            header("Location: ../pages/dashboard.php?tab=gestion-utilisateurs&error=" . urlencode("Le nom d'utilisateur ou l'adresse email existe deja."));
            exit();
        }

        $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        $stmtInsert = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)");
        $stmtInsert->execute([$nom, $email, $mot_de_passe_hache]);

        $id_nouvel_utilisateur = $pdo->lastInsertId();
        $_SESSION['id_utilisateur'] = $id_nouvel_utilisateur;
        $_SESSION['email'] = $email;
        $_SESSION['nom_utilisateur'] = $nom;

        header("Location: ../pages/dashboard.php?tab=gestion-utilisateurs&success=" . urlencode("Utilisateur cree avec succes !"));
        exit();

    } catch (PDOException $e) {
        header("Location: ../pages/dashboard.php?tab=gestion-utilisateurs&error=" . urlencode("Erreur : " . $e->getMessage()));
        exit();
    }

} else {
    header("Location: ../pages/dashboard.php?tab=gestion-utilisateurs");
    exit();
}