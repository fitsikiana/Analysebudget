<?php
session_start();
include "../config/db.php";

if (isset($_POST['modifier_utilisateur'])) {
    $id_utilisateur = $_POST['id_utilisateur'];
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);

    if (!empty($id_utilisateur) && !empty($nom) && !empty($email)) {
        try {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, email = ? WHERE id_utilisateur = ?");
            $stmt->execute([$nom, $email, $id_utilisateur]);
            
            header("Location: ../pages/dashboard.php?tab=gestion-utilisateurs&user_success=" . urlencode("L'utilisateur a ete modifie avec succes !"));
            exit();
        } catch (Exception $e) {
            header("Location: ../pages/dashboard.php?tab=gestion-utilisateurs&user_error=" . urlencode("Erreur : " . $e->getMessage()));
        }
    } else {
        header("Location: ../pages/dashboard.php?tab=gestion-utilisateurs&user_error=" . urlencode("Veuillez remplir tous les champs."));
        exit();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id_utilisateur = $_GET['id'];

    if (!empty($id_utilisateur)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id_utilisateur = ?");
            $stmt->execute([$id_utilisateur]);

            header("Location: ../pages/dashboard.php?tab=gestion-utilisateurs&user_success=" . urlencode("L'utilisateur a ete supprime avec succes !"));
            exit();
        } catch (Exception $e) {
            header("Location: ../pages/dashboard.php?tab=gestion-utilisateurs&user_error=" . urlencode("Erreur : " . $e->getMessage()));
            exit();
        }
    }
}

header("Location: ../pages/dashboard.php");
exit();