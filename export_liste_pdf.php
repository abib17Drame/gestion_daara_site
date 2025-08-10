<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Vérification du niveau
if (!isset($_GET['niveau']) || empty($_GET['niveau'])) {
    header("Location: liste_eleves.php");
    exit();
}

require 'supabase.php';

// Définition du chemin absolu vers dompdf
$dompdfPath = __DIR__ . '/dompdf/autoload.inc.php';
if (!file_exists($dompdfPath)) {
    die("Erreur : Le fichier dompdf n'est pas trouvé. Chemin recherché : " . $dompdfPath);
}
require_once $dompdfPath;

use Dompdf\Dompdf;
use Dompdf\Options;

// Récupération du niveau
$niveau = $_GET['niveau'];

// Récupération des élèves
$supabase = Supabase::getInstance();
$eleves = $supabase->select('eleves', [
    'filter' => ['niveau' => $niveau],
    'order' => 'nom.asc'
]);

// Création du contenu HTML
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
            color: #000;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #003399;
            color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #000;
        }
        td {
            padding: 8px;
            border: 1px solid #000;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>LISTE DES ÉLÈVES</h1>
    
    <div class="info">
        <p><strong>Niveau:</strong> ' . htmlspecialchars($niveau) . '</p>
        <p><strong>Date:</strong> ' . date('d/m/Y') . '</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Âge</th>
                <th>Niveau</th>
                <th>Tuteur</th>
                <th>Téléphone</th>
            </tr>
        </thead>
        <tbody>';

$i = 1;
foreach ($eleves as $eleve) {
    $html .= '<tr>
        <td>' . $i++ . '</td>
        <td>' . htmlspecialchars($eleve['nom']) . '</td>
        <td>' . htmlspecialchars($eleve['prenom']) . '</td>
        <td>' . ($eleve['age'] ?? '-') . '</td>
        <td>' . htmlspecialchars($eleve['niveau']) . '</td>
        <td>' . htmlspecialchars($eleve['nom_complet_tuteur'] ?? '-') . '</td>
        <td>' . htmlspecialchars($eleve['telephone_tuteur'] ?? '-') . '</td>
    </tr>';
}

$html .= '
        </tbody>
    </table>
</body>
</html>';

// Configuration de DOMPDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('defaultFont', 'Arial');

// Création du PDF
$dompdf = new Dompdf($options);
$dompdf->setPaper('A4', 'landscape');
$dompdf->loadHtml($html);
$dompdf->render();

// Envoi du PDF au navigateur
$dompdf->stream('liste_eleves_' . $niveau . '_' . date('Y-m-d') . '.pdf', array('Attachment' => false)); 