<?php
session_start();
require_once '../supabase.php';

// Vérifier si le parent est connecté
if (!isset($_SESSION['parent_telephone'])) {
    header('Location: index.php');
    exit;
}

$supabase = Supabase::getInstance();

// Récupérer tous les élèves du parent
$eleves = $supabase->select('eleves', [
    'filter' => [
        'telephone_tuteur' => $_SESSION['parent_telephone']
    ]
]);

// Si un élève spécifique est sélectionné
$eleve_selectionne = null;
$paiements = [];
if (isset($_GET['eleve_id'])) {
    foreach ($eleves as $eleve) {
        if ($eleve['id'] == $_GET['eleve_id']) {
            $eleve_selectionne = $eleve;
            // Récupérer les paiements de l'élève sélectionné
            $paiements = $supabase->select('paiements', [
                'filter' => [
                    'eleve_id' => $eleve['id']
                ],
                'order' => 'date_paiement.desc'
            ]);
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portail Parent - Tableau de bord</title>
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
        .navbar-brand {
            color: var(--secondary-color) !important;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            height: 100%;
        }
        .card-header {
            background-color: var(--primary-color);
            color: var(--secondary-color);
        }
        .logout-btn {
            color: var(--secondary-color);
            text-decoration: none;
        }
        .logout-btn:hover {
            color: #f8f9fa;
        }
        .eleve-card {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .eleve-card:hover {
            transform: translateY(-5px);
        }
        .btn-details {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        .btn-details:hover {
            background-color: #218838;
            border-color: #218838;
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">Portail Parent</a>
            <div class="ms-auto">
                <span class="text-white me-3">
                    <?php echo htmlspecialchars($_SESSION['parent_nom']); ?>
                </span>
                <a href="logout.php" class="logout-btn">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">Mes Élèves</h2>
        <div class="row">
            <?php foreach ($eleves as $eleve): ?>
            <div class="col-md-4 mb-4">
                <div class="card eleve-card">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo htmlspecialchars($eleve['prenom'] . ' ' . $eleve['nom']); ?></h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <i class="bi bi-book me-2"></i>
                            <strong>Niveau:</strong> <?php echo htmlspecialchars($eleve['niveau']); ?>
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-calendar-date me-2"></i>
                            <strong>Inscription:</strong> <?php echo date('d/m/Y', strtotime($eleve['date_inscription'])); ?>
                        </p>
                        <?php if (!empty($eleve['age'])): ?>
                        <p class="mb-2">
                            <i class="bi bi-person me-2"></i>
                            <strong>Âge:</strong> <?php echo htmlspecialchars($eleve['age']); ?> ans
                        </p>
                        <?php endif; ?>
                        <div class="text-center mt-3">
                            <a href="eleve.php?id=<?php echo $eleve['id']; ?>" class="btn btn-details w-100">
                                <i class="bi bi-eye me-2"></i>Voir les détails
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($eleve_selectionne): ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Détails de l'élève</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Nom:</strong> <?php echo htmlspecialchars($eleve_selectionne['nom']); ?></p>
                        <p><strong>Prénom:</strong> <?php echo htmlspecialchars($eleve_selectionne['prenom']); ?></p>
                        <p><strong>Niveau:</strong> <?php echo htmlspecialchars($eleve_selectionne['niveau']); ?></p>
                        <p><strong>Date d'inscription:</strong> <?php echo date('d/m/Y', strtotime($eleve_selectionne['date_inscription'])); ?></p>
                        <?php if (!empty($eleve_selectionne['age'])): ?>
                        <p><strong>Âge:</strong> <?php echo htmlspecialchars($eleve_selectionne['age']); ?> ans</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Résumé des paiements</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $total_paye = 0;
                        foreach ($paiements as $paiement) {
                            $total_paye += $paiement['montant'];
                        }
                        ?>
                        <h3 class="text-success"><?php echo number_format($total_paye, 0, ',', ' '); ?> FCFA</h3>
                        <p class="text-muted">Total des paiements effectués</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Historique des paiements</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Mois</th>
                                <th>Année</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paiements as $paiement): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($paiement['date_paiement'])); ?></td>
                                <td><?php echo htmlspecialchars($paiement['mois']); ?></td>
                                <td><?php echo htmlspecialchars($paiement['annee']); ?></td>
                                <td><?php echo number_format($paiement['montant'], 0, ',', ' '); ?> FCFA</td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($paiements)): ?>
                            <tr>
                                <td colspan="4" class="text-center">Aucun paiement enregistré</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 