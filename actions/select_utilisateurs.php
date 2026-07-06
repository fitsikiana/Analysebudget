<?php
// actions/select_utilisateurs.php
include "../config/db.php";

try {
    $sql = "SELECT id_utilisateur, nom, email FROM utilisateurs";
    $stm = $pdo->prepare($sql);
    $stm->execute();
    $select_utilisateurs = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $select_utilisateurs = [];
}
?>