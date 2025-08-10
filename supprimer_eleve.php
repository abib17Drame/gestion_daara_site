<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'supabase.php';

$supabase = Supabase::getInstance();

if (!isset($_GET['id'])) {
    header("Location: eleves.php");
    exit();
}

$id = $_GET['id'];
$rediriger_vers = "eleves.php"; // Défaut

// Vérifier si l'élève existe et récupérer son niveau
$eleves = $supabase->select('eleves', [
    'filter' => ['id' => $id]
]);

if (empty($eleves)) {
    $_SESSION['error_message'] = "L'élève n'existe pas.";
    header("Location: " . $rediriger_vers);
    exit();
}

// Récupérer le niveau avant suppression
$eleve = $eleves[0];
$rediriger_vers = "eleves.php?niveau=" . urlencode($eleve['niveau']);

// Supprimer d'abord les paiements liés à cet élève
$paiementsSupprimes = $supabase->delete('paiements', ['eleve_id' => $id]);

// Ensuite, supprimer l'élève
$elevesSupprime = $supabase->delete('eleves', ['id' => $id]);

if ($elevesSupprime) {
    $_SESSION['success_message'] = "L'élève a été supprimé avec succès!";
} else {
    $_SESSION['error_message'] = "Une erreur est survenue lors de la suppression de l'élève.";
}

header("Location: " . $rediriger_vers);
exit();
?>
