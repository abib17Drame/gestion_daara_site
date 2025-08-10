<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'supabase.php';

$supabase = Supabase::getInstance();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['paiement_id'])) {
    $paiementId = $_POST['paiement_id'];
    $niveau = isset($_POST['niveau']) ? $_POST['niveau'] : '';
    
    // Supprimer complètement le paiement
    $result = $supabase->delete('paiements', ['id' => $paiementId]);
    
    if ($result) {
        $_SESSION['success_message'] = "Le paiement a été annulé avec succès.";
    } else {
        $_SESSION['error_message'] = "Erreur lors de l'annulation du paiement.";
    }
    
    // Redirection vers la page d'historique
    if (!empty($niveau)) {
        header("Location: historique.php?niveau=" . urlencode($niveau));
    } else {
        header("Location: historique.php");
    }
    exit;
} else {
    // Cas où aucun ID de paiement n'est spécifié
    $_SESSION['error_message'] = "Aucun paiement à annuler n'a été spécifié.";
    header("Location: historique.php");
    exit;
}
?>
