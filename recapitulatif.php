<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'supabase.php';
require 'includes/header.php';

$supabase = Supabase::getInstance();

$annee = isset($_GET['annee']) ? intval($_GET['annee']) : date('Y');
$niveau_filter = isset($_GET['niveau']) ? $_GET['niveau'] : '';

$mois_options = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai',
    6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre',
    10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

$niveau_options = ['Petite Section', 'Moyenne Section', 'Grande Section', 'Coranique'];

// Cette requête est complexe, nous allons devoir la traiter côté serveur

// 1. Récupérer tous les élèves
$eleves = $supabase->select('eleves');

// 2. Récupérer tous les paiements pour l'année sélectionnée
$filter = ['annee' => $annee];
if (!empty($niveau_filter)) {
    // Nous filtrerons les élèves par niveau plus tard
}

$paiements = $supabase->select('paiements', [
    'filter' => $filter
]);

// 3. Traiter les données pour obtenir le récapitulatif
$recap = [];
$totalAnnee = 0;

foreach ($paiements as $paiement) {
    $eleveId = $paiement['eleve_id'];
    $mois = $paiement['mois'];
    $montant = $paiement['montant'];
    
    // Trouver le niveau de l'élève
    $niveau = null;
    foreach ($eleves as $eleve) {
        if ($eleve['id'] == $eleveId) {
            $niveau = $eleve['niveau'];
            break;
        }
    }
    
    // Si un filtre de niveau est appliqué, ignorer les autres niveaux
    if (!empty($niveau_filter) && $niveau != $niveau_filter) {
        continue;
    }
    
    if ($niveau) {
        if (!isset($recap[$niveau])) {
            $recap[$niveau] = [];
        }
        
        if (!isset($recap[$niveau][$mois])) {
            $recap[$niveau][$mois] = 0;
        }
        
        $recap[$niveau][$mois] += $montant;
        $totalAnnee += $montant;
    }
}
?>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h3 class="h5 mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Récapitulatif des Paiements - Année <?php echo $annee; ?>
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end mb-4">
                    <div class="col-md-5">
                        <label for="annee" class="form-label">Année :</label>
                        <input type="number" name="annee" id="annee" class="form-control" value="<?php echo $annee; ?>" min="2000" max="2100" required>
                    </div>
                    
                    <div class="col-md-5">
                        <label for="niveau" class="form-label">Niveau :</label>
                        <select name="niveau" id="niveau" class="form-select">
                            <option value="">Tous les niveaux</option>
                            <?php foreach ($niveau_options as $niveau): ?>
                                <option value="<?php echo $niveau; ?>" <?php echo $niveau_filter === $niveau ? 'selected' : ''; ?>>
                                    <?php echo $niveau; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-filter me-1"></i> Filtrer
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <?php if (empty($recap)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Aucune donnée de paiement trouvée pour cette période.
                    </div>
                    <?php else: ?>
                    <?php foreach ($recap as $niveau => $mois_data): ?>
                    <div class="card mb-4">
                        <div class="card-header recap-header">
                            <h4 class="h5 mb-0">Niveau : <?php echo htmlspecialchars($niveau); ?></h4>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-success">
                                    <tr>
                                        <th>Mois</th>
                                        <th class="text-end">Total du Mois (FCFA)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mois_data as $mois => $total_mois): ?>
                                    <tr>
                                        <td><?php echo $mois_options[$mois]; ?></td>
                                        <td class="text-end"><?php echo number_format($total_mois, 0, ',', ' '); ?> FCFA</td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-success">
                                        <td class="fw-bold">Total Niveau</td>
                                        <td class="fw-bold text-end"><?php echo number_format(array_sum($mois_data), 0, ',', ' '); ?> FCFA</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="card bg-success text-white mt-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">Total Année <?php echo $annee; ?></h4>
                                <h4 class="mb-0"><?php echo number_format($totalAnnee, 0, ',', ' '); ?> FCFA</h4>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Bouton d'impression -->
                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-outline-success" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Imprimer le récapitulatif
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
