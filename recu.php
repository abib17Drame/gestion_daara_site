<?php
session_start();
require 'supabase.php';

// Vérifier si l'utilisateur est connecté (pour le portail parent)
$is_parent = isset($_SESSION['parent_telephone']);

$supabase = Supabase::getInstance();

// Vérifier si un ID élève est passé en paramètre
if (!isset($_GET['eleve_id'])) {
    die("
        <div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>
            <h3 style='color: #dc3545;'>Erreur</h3>
            <p>ID élève manquant.</p>
            <a href='javascript:history.back()' style='color: #28a745; text-decoration: none;'>Retour</a>
        </div>
    ");
}

$eleve_id = intval($_GET['eleve_id']);
$mois = isset($_GET['mois']) ? intval($_GET['mois']) : date('n');
$annee = isset($_GET['annee']) ? intval($_GET['annee']) : date('Y');

// Si c'est un parent, vérifier qu'il a accès à cet élève
if ($is_parent) {
    $eleves = $supabase->select('eleves', [
        'filter' => [
            'id' => $eleve_id,
            'telephone_tuteur' => $_SESSION['parent_telephone']
        ]
    ]);
} else {
    $eleves = $supabase->select('eleves', [
        'filter' => ['id' => $eleve_id]
    ]);
}

if (empty($eleves)) {
    die("
        <div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>
            <h3 style='color: #dc3545;'>Erreur</h3>
            <p>Élève non trouvé ou accès non autorisé.</p>
            <a href='javascript:history.back()' style='color: #28a745; text-decoration: none;'>Retour</a>
        </div>
    ");
}

$eleve = $eleves[0];

// Récupérer le paiement correspondant
$paiements = $supabase->select('paiements', [
    'filter' => [
        'eleve_id' => $eleve_id,
        'mois' => $mois,
        'annee' => $annee
    ]
]);

if (empty($paiements)) {
    die("
        <div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>
            <h3 style='color: #dc3545;'>Erreur</h3>
            <p>Aucun paiement trouvé pour cet élève à cette période.</p>
            <a href='javascript:history.back()' style='color: #28a745; text-decoration: none;'>Retour</a>
        </div>
    ");
}

$paiement = $paiements[0];

// Combiner les données
$eleve['montant'] = $paiement['montant'];
$eleve['date_paiement'] = $paiement['date_paiement'];

$mois_options = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai',
    6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre',
    10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

$message = "DAARA MODERNE ELHADJI OUSMANE DRAME\n".
            "Reçu de paiement:\nNom: " . $eleve['nom'] .
           "\nPrénom: " . $eleve['prenom'] .
           "\nNiveau: " . $eleve['niveau'] .
           "\nMontant: " . number_format($eleve['montant'], 0, ',', ' ') . " FCFA" .
           "\nDate: " . date("d/m/Y", strtotime($eleve['date_paiement'])).
           "\t\tResponsable du daara : Aliou DRAME";

$tuteurPhone = trim($eleve['telephone_tuteur']);
if (!empty($tuteurPhone)) {
    $waLink = "https://wa.me/" . urlencode($tuteurPhone) . "?text=" . urlencode($message);
} else {
    $waLink = "#";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de paiement</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="css/custom.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .recu-container {
                border: 1px solid #ddd !important;
            }
            body {
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <!-- Barre d'action -->
                <div class="d-flex justify-content-between mb-4 no-print">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Retour
                    </a>
                    <div>
                        <?php if (!empty($tuteurPhone)): ?>
                            <a href="<?php echo $waLink; ?>" target="_blank" class="btn btn-whatsapp me-2">
                                <i class="fab fa-whatsapp me-1"></i> Envoyer via WhatsApp
                            </a>
                        <?php endif; ?>
                        <button onclick="window.print()" class="btn btn-success">
                            <i class="fas fa-print me-1"></i> Imprimer
                        </button>
                    </div>
                </div>

                <!-- Reçu de paiement -->
                <div class="card shadow-sm recu-container">
                    <div class="card-header bg-success text-white text-center py-3">
                        <h3 class="mb-0">Reçu de Paiement</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="fas fa-mosque text-success fa-3x"></i>
                            <h4 class="mt-2">DAARA MODERNE ELHADJI OUSMANE DRAME</h4>
                        </div>

                        <div class="mb-4">
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Nom :</div>
                                <div class="col-7"><?php echo htmlspecialchars($eleve['nom']); ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Prénom :</div>
                                <div class="col-7"><?php echo htmlspecialchars($eleve['prenom']); ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Niveau :</div>
                                <div class="col-7"><?php echo htmlspecialchars($eleve['niveau']); ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Période :</div>
                                <div class="col-7"><?php echo $mois_options[$mois] . ' ' . $annee; ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Montant :</div>
                                <div class="col-7 fw-bold"><?php echo number_format($eleve['montant'], 0, ',', ' '); ?> FCFA</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Date de paiement :</div>
                                <div class="col-7"><?php echo date("d/m/Y à H:i", strtotime($eleve['date_paiement'])); ?></div>
                            </div>
                        </div>

                        <hr>

                        <div class="row mt-4">
                            <div class="col-6 text-center">
                                <p class="mb-4">Signature Responsable</p>
                                <p class="mt-5">Aliou DRAME</p>
                            </div>
                            <div class="col-6 text-center">
                                <p class="mb-4">Cachet</p>
                                <div class="mt-2 mb-2 fst-italic text-muted small">Cachet du Daara</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center text-muted py-3">
                        <small>Ce reçu est une preuve de paiement officielle</small>
                    </div>
                </div>

                <?php if (empty($tuteurPhone)): ?>
                <div class="alert alert-warning mt-3 no-print" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Le numéro de téléphone du tuteur n'est pas renseigné. Impossible d'envoyer le reçu par WhatsApp.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle avec Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
