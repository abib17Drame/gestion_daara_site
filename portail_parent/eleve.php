<?php
session_start();
require_once '../supabase.php';

// Vérifier si le parent est connecté
if (!isset($_SESSION['parent_telephone'])) {
    header('Location: index.php');
    exit;
}

// Vérifier si l'ID de l'élève est fourni
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$supabase = Supabase::getInstance();

// Récupérer les informations de l'élève
$eleve = null;
$eleves = $supabase->select('eleves', [
    'filter' => [
        'telephone_tuteur' => $_SESSION['parent_telephone']
    ]
]);

foreach ($eleves as $e) {
    if ($e['id'] == $_GET['id']) {
        $eleve = $e;
        break;
    }
}

if (!$eleve) {
    header('Location: dashboard.php');
    exit;
}

// Récupérer les paiements avec filtre par mois si spécifié
$mois = isset($_GET['mois']) ? $_GET['mois'] : '';

$filter = [
    'eleve_id' => $eleve['id']
];

if ($mois) {
    $filter['mois'] = $mois;
}

$paiements = $supabase->select('paiements', [
    'filter' => $filter,
    'order' => 'date_paiement.desc'
]);

// Calculer le total des paiements
$total_paye = 0;
foreach ($paiements as $paiement) {
    $total_paye += $paiement['montant'];
}

$mois_francais = [
    '1' => 'Janvier', '2' => 'Février', '3' => 'Mars',
    '4' => 'Avril', '5' => 'Mai', '6' => 'Juin',
    '7' => 'Juillet', '8' => 'Août', '9' => 'Septembre',
    '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
];

// Définir l'année scolaire
$mois_courant = date('n');
$annee_courante = date('Y');

// Si nous sommes entre novembre et décembre, l'année scolaire commence cette année
// Si nous sommes entre janvier et juin, l'année scolaire a commencé l'année précédente
$annee_scolaire_debut = ($mois_courant >= 11) ? $annee_courante : $annee_courante - 1;
$annee_scolaire_fin = $annee_scolaire_debut + 1;

// Créer un tableau de tous les mois avec leur statut
$statut_paiements = [];

// Initialiser le tableau des mois de l'année scolaire (novembre à juin)
$mois_scolaire = [
    11 => 'Novembre',
    12 => 'Décembre',
    1 => 'Janvier',
    2 => 'Février',
    3 => 'Mars',
    4 => 'Avril',
    5 => 'Mai',
    6 => 'Juin'
];

// Initialiser le tableau des statuts pour l'année scolaire
foreach ($mois_scolaire as $num => $nom) {
    $annee = ($num >= 11) ? $annee_scolaire_debut : $annee_scolaire_fin;
    $statut_paiements[$num] = [
        'status' => 'non_paye',
        'montant' => 0,
        'date_paiement' => null,
        'annee' => $annee
    ];
}

// Mettre à jour le statut des mois payés
foreach ($paiements as $paiement) {
    if (($paiement['annee'] == $annee_scolaire_debut && $paiement['mois'] >= 11) || 
        ($paiement['annee'] == $annee_scolaire_fin && $paiement['mois'] <= 6)) {
        $statut_paiements[$paiement['mois']] = [
            'status' => 'paye',
            'montant' => $paiement['montant'],
            'date_paiement' => $paiement['date_paiement'],
            'annee' => $paiement['annee']
        ];
    }
}

// Initialiser le tableau des mois en retard
$mois_retard = [];

// Marquer les mois en retard (mois passés non payés)
foreach ($mois_scolaire as $num => $nom) {
    $annee = ($num >= 11) ? $annee_scolaire_debut : $annee_scolaire_fin;
    $est_passe = ($annee < $annee_courante) || 
                 ($annee == $annee_courante && $num < $mois_courant);
    
    if ($est_passe && $statut_paiements[$num]['status'] === 'non_paye') {
        $statut_paiements[$num]['status'] = 'retard';
        $mois_retard[$num] = $statut_paiements[$num];
    }
}

// Calculer le montant total des paiements en retard
$montant_mensuel = 2500; // Montant mensuel fixe de 2500 FCFA
$total_retard = count($mois_retard) * $montant_mensuel;

// Récupérer toutes les années disponibles
$annees_disponibles = $supabase->customSelect('paiements', "eleve_id=eq.{$eleve['id']}&select=distinct(annee)&order=annee.desc");
$annees = array_column($annees_disponibles, 'annee');

// Si aucune année n'est disponible, utiliser l'année courante
if (empty($annees)) {
    $annees = [$annee_courante];
}

// Récupérer l'année sélectionnée
$annee_selectionnee = isset($_GET['annee']) ? intval($_GET['annee']) : $annee_courante;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'élève - <?php echo htmlspecialchars($eleve['prenom'] . ' ' . $eleve['nom']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #28a745;
            --secondary-color: #ffffff;
        }
        .navbar {
            background-color: var(--primary-color);
        }
        .navbar-brand, .nav-link {
            color: var(--secondary-color) !important;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: var(--primary-color);
            color: var(--secondary-color);
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #218838;
            border-color: #218838;
        }
        .paiement-row:hover {
            background-color: #f8f9fa;
        }
        .filters {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .status-card {
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .status-card:hover {
            transform: translateY(-5px);
        }
        .notification-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            padding: 5px 10px;
            border-radius: 50%;
            font-size: 12px;
        }
        .tab-content {
            padding: 20px 0;
        }
        /* Styles spécifiques pour les onglets */
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
        }
        .nav-tabs .nav-link {
            color: #000000 !important; /* Texte noir */
            border: none;
            padding: 10px 20px;
            margin-right: 10px;
            font-weight: 500;
        }
        .nav-tabs .nav-link:hover {
            border: none;
            color: var(--primary-color) !important;
            background-color: rgba(40, 167, 69, 0.1);
        }
        .nav-tabs .nav-link.active {
            color: var(--primary-color) !important;
            background-color: transparent;
            border: none;
            border-bottom: 2px solid var(--primary-color);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-arrow-left me-2"></i>Retour au tableau de bord
            </a>
            <div class="ms-auto">
                <span class="text-white me-3"><?php echo htmlspecialchars($_SESSION['parent_nom']); ?></span>
                <a href="logout.php" class="nav-link d-inline">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- En-tête avec informations principales -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h2><?php echo htmlspecialchars($eleve['prenom'] . ' ' . $eleve['nom']); ?></h2>
                <p class="text-muted">
                    Niveau: <?php echo htmlspecialchars($eleve['niveau']); ?> |
                    Inscription: <?php echo date('d/m/Y', strtotime($eleve['date_inscription'])); ?>
                    <?php if (!empty($eleve['age'])): ?> | Âge: <?php echo htmlspecialchars($eleve['age']); ?> ans<?php endif; ?>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <?php if (!empty($mois_retard)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Retard total: <?php echo number_format($total_retard, 0, ',', ' '); ?> FCFA
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Navigation par onglets -->
        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="apercu-tab" data-bs-toggle="tab" data-bs-target="#apercu" type="button" role="tab">
                    <i class="bi bi-calendar-check me-1"></i>Aperçu
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="historique-tab" data-bs-toggle="tab" data-bs-target="#historique" type="button" role="tab">
                    <i class="bi bi-clock-history me-1"></i>Historique
                </button>
            </li>
        </ul>

        <!-- Contenu des onglets -->
        <div class="tab-content" id="myTabContent">
            <!-- Onglet Aperçu -->
            <div class="tab-pane fade show active" id="apercu">
                <div class="row">
                    <!-- Statut du mois en cours -->
                    <div class="col-md-4 mb-4">
                        <div class="card status-card">
                            <div class="card-header">
                                <h5 class="mb-0">Mois en cours</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $status_mois_courant = $statut_paiements[$mois_courant]['status'];
                                $classe_status = [
                                    'paye' => 'success',
                                    'non_paye' => 'warning',
                                    'retard' => 'danger'
                                ];
                                $texte_status = [
                                    'paye' => 'Payé',
                                    'non_paye' => 'Non payé',
                                    'retard' => 'En retard'
                                ];
                                ?>
                                <div class="alert alert-<?php echo $classe_status[$status_mois_courant]; ?> mb-0">
                                    <h4 class="alert-heading">
                                        <?php echo $mois_francais[$mois_courant] . ' ' . $annee_courante; ?>
                                    </h4>
                                    <p class="mb-0">
                                        Statut : <strong><?php echo $texte_status[$status_mois_courant]; ?></strong>
                                        <?php if ($status_mois_courant === 'paye'): ?>
                                            <br>Montant : <?php echo number_format($statut_paiements[$mois_courant]['montant'], 0, ',', ' '); ?> FCFA
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prochaines échéances -->
                    <div class="col-md-4 mb-4">
                        <div class="card status-card">
                            <div class="card-header">
                                <h5 class="mb-0">Prochaines échéances</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $prochains_mois = [];
                                for ($i = $mois_courant; $i <= min($mois_courant + 2, 12); $i++) {
                                    if ($statut_paiements[$i]['status'] === 'non_paye') {
                                        $prochains_mois[] = $i;
                                    }
                                }
                                ?>
                                <?php if (!empty($prochains_mois)): ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($prochains_mois as $m): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $mois_francais[$m]; ?>
                                        <span class="badge bg-warning rounded-pill">À venir</span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php else: ?>
                                <p class="text-muted mb-0">Aucune échéance proche</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Paiements en retard -->
                    <?php if (!empty($mois_retard)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card status-card">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Retards
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($mois_retard as $mois_num => $info): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center text-danger">
                                        <?php echo $mois_francais[$mois_num]; ?>
                                        <span><?php echo number_format($montant_mensuel, 0, ',', ' '); ?> FCFA</span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="alert alert-danger mt-3 mb-0">
                                    <strong>Total dû : <?php echo number_format($total_retard, 0, ',', ' '); ?> FCFA</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Aperçu annuel -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Aperçu  Annee Scolaire <?php echo $annee_selectionnee; ?></h5>
                                <select class="form-select form-select-sm w-auto" onchange="window.location.href='?id=<?php echo $eleve['id']; ?>&annee=' + this.value">
                                    <?php foreach ($annees as $a): ?>
                                    <option value="<?php echo $a; ?>" <?php echo $a == $annee_selectionnee ? 'selected' : ''; ?>>
                                        <?php echo $a; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="card-body">
                                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                                    <?php foreach ($mois_scolaire as $num => $nom): 
                                        $status = $statut_paiements[$num]['status'];
                                        $bg_class = [
                                            'paye' => 'bg-success',
                                            'non_paye' => 'bg-warning',
                                            'retard' => 'bg-danger'
                                        ][$status];
                                        $icon = [
                                            'paye' => 'bi-check-circle-fill',
                                            'non_paye' => 'bi-clock',
                                            'retard' => 'bi-exclamation-circle-fill'
                                        ][$status];
                                        $annee = ($num >= 11) ? $annee_scolaire_debut : $annee_scolaire_fin;
                                    ?>
                                    <div class="col">
                                        <div class="card h-100 status-card">
                                            <div class="card-body text-center <?php echo $bg_class; ?> text-white">
                                                <h6 class="card-title mb-2"><?php echo $nom . ' ' . $annee; ?></h6>
                                                <i class="bi <?php echo $icon; ?> fs-4"></i>
                                                <?php if ($status === 'paye'): ?>
                                                <div class="mt-2 small">
                                                    <?php echo number_format($statut_paiements[$num]['montant'], 0, ',', ' '); ?> FCFA
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onglet Historique -->
            <div class="tab-pane fade" id="historique">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Historique des paiements</h5>
                        <form method="GET" class="d-flex gap-2">
                            <input type="hidden" name="id" value="<?php echo $eleve['id']; ?>">
                            <select name="mois" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Tous les mois</option>
                                <?php foreach ($mois_francais as $num => $nom): ?>
                                <option value="<?php echo $num; ?>" <?php echo $num == $mois ? 'selected' : ''; ?>>
                                    <?php echo $nom; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Mois</th>
                                        <th>Année</th>
                                        <th>Montant</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paiements as $paiement): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($paiement['date_paiement'])); ?></td>
                                        <td><?php echo $mois_francais[$paiement['mois']]; ?></td>
                                        <td><?php echo $paiement['annee']; ?></td>
                                        <td><?php echo number_format($paiement['montant'], 0, ',', ' '); ?> FCFA</td>
                                        <td>
                                            <a href="../recu.php?eleve_id=<?php echo $eleve['id']; ?>&mois=<?php echo $paiement['mois']; ?>&annee=<?php echo $paiement['annee']; ?>" 
                                               class="btn btn-sm btn-primary" target="_blank">
                                                <i class="bi bi-download me-1"></i>Reçu
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($paiements)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun paiement trouvé</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialiser les onglets
        document.addEventListener('DOMContentLoaded', function() {
            var tabEl = document.querySelectorAll('button[data-bs-toggle="tab"]');
            tabEl.forEach(function(tab) {
                new bootstrap.Tab(tab);
            });
        });
    </script>
</body>
</html> 