<?php
session_start();
require_once '../supabase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telephone = $_POST['telephone'];
    
    // Vérifier si le numéro de téléphone existe dans la table eleves
    $supabase = Supabase::getInstance();
    $eleves = $supabase->select('eleves', [
        'filter' => [
            'telephone_tuteur' => $telephone
        ]
    ]);
    
    if (!empty($eleves)) {
        // Stocker les informations dans la session
        $_SESSION['parent_telephone'] = $telephone;
        $_SESSION['parent_nom'] = $eleves[0]['nom_complet_tuteur'];
        
        // Rediriger vers la page d'accueil du portail parent
        header('Location: dashboard.php');
        exit;
    } else {
        // Rediriger vers la page de connexion avec un message d'erreur
        header('Location: index.php?error=1');
        exit;
    }
} else {
    // Si quelqu'un essaie d'accéder directement à auth.php
    header('Location: index.php');
    exit;
} 