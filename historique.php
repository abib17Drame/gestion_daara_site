<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'supabase.php';
require 'includes/header.php';

$supabase = Supabase::getInstance();

$niveau = isset($_GET['niveau']) ? $_GET['niveau'] : '';
$mois = isset($_GET['mois']) ? (int)$_GET['mois'] : date('n');
$annee = isset($_GET['annee']) ? (int)$_GET['annee'] : date('Y');

$mois_options = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai',
    6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre',
    10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

// Étape 1: Sélection du niveau si aucun n'est choisi
if (empty($niveau)) {
    ?>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4 fade-in">
                <div class="card-body text-center p-5">
                    <h2 class="text-success mb-4"><i class="fas fa-history me-2"></i>Historique des Paiements</h2>
                    <div class="row g-4 mt-2">
                        <div class="col-md-6 col-lg-3">
                            <a href="historique.php?niveau=Petite+Section" class="text-decoration-none">
                                <div class="card h-100 niveau-card border-success">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-child text-success fa-3x mb-3"></i>
                                        <h4>Petite Section</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="historique.php?niveau=Moyenne+Section" class="text-decoration-none">
                                <div class="card h-100 niveau-card border-success">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-child text-success fa-3x mb-3"></i>
                                        <h4>Moyenne Section</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="historique.php?niveau=Grande+Section" class="text-decoration-none">
                                <div class="card h-100 niveau-card border-success">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-child text-success fa-3x mb-3"></i>
                                        <h4>Grande Section</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="historique.php?niveau=Coranique" class="text-decoration-none">
                                <div class="card h-100 niveau-card border-success">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-book-quran text-success fa-3x mb-3"></i>
                                        <h4>Coranique</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    include 'includes/footer.php';
    exit();
}

// Récupérer les paiements pour le niveau, mois et année sélectionnés
// Cette requête est complexe et nécessite un traitement côté serveur

// 1. Récupérer tous les élèves du niveau
$eleves = $supabase->select('eleves', [
    'filter' => ['niveau' => $niveau],
    'order' => 'nom.asc'
]);

// 2. Récupérer tous les paiements pour ce mois/année
$paiements = $supabase->select('paiements', [
    'filter' => [
        'mois' => $mois,
        'annee' => $annee
    ]
]);

// 3. Joindre les données
$paiementsAvecEleves = [];
foreach ($paiements as $paiement) {
    // Trouver l'élève correspondant
    $eleveAssocie = null;
    foreach ($eleves as $eleve) {
        if ($eleve['id'] == $paiement['eleve_id']) {
            $eleveAssocie = $eleve;
            break;
        }
    }
    
    if ($eleveAssocie) {
        $paiement['nom'] = $eleveAssocie['nom'];
        $paiement['prenom'] = $eleveAssocie['prenom'];
        $paiement['niveau'] = $eleveAssocie['niveau'];
        $paiementsAvecEleves[] = $paiement;
    }
}

// Trier par date de paiement
usort($paiementsAvecEleves, function($a, $b) {
    return strtotime($b['date_paiement']) - strtotime($a['date_paiement']);
});
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="h5 mb-0">
                        <i class="fas fa-history me-2"></i>Historique des Paiements - <?php echo htmlspecialchars($niveau); ?>
                    </h3>
                    <a href="historique.php" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-exchange-alt me-1"></i> Changer de Niveau
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end mb-4">
                    <input type="hidden" name="niveau" value="<?php echo htmlspecialchars($niveau); ?>">
                    <div class="col-md-4">
                        <label for="mois" class="form-label">Mois :</label>
                        <select name="mois" id="mois" class="form-select">
                            <?php foreach ($mois_options as $num => $nom): ?>
                                <option value="<?php echo $num; ?>" <?php echo ($num == $mois) ? 'selected' : ''; ?>><?php echo $nom; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="annee" class="form-label">Année :</label>
                        <input type="number" name="annee" id="annee" class="form-control" value="<?php echo $annee; ?>" min="2000" max="2100" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-filter me-2"></i>Filtrer
                        </button>
                    </div>
                </form>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <div class="table-responsive">
                    <?php if (empty($paiementsAvecEleves)): ?>
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>Aucun paiement n'a été enregistré pour <?php echo $mois_options[$mois].' '.$annee; ?>.
                        </div>
                    <?php else: ?>
                        <table class="table table-hover table-green">
                            <thead>
                                <tr>
                                    <th>Élève</th>
                                    <th>Montant</th>
                                    <th>Date de paiement</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paiementsAvecEleves as $paiement): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($paiement['prenom'].' '.$paiement['nom']); ?></td>
                                        <td><?php echo number_format($paiement['montant'], 0, ',', ' '); ?> FCFA</td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($paiement['date_paiement'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="recu.php?eleve_id=<?php echo $paiement['eleve_id']; ?>&mois=<?php echo $mois; ?>&annee=<?php echo $annee; ?>" class="btn btn-outline-success">
                                                    <i class="fas fa-receipt"></i> Reçu
                                                </a>
                                                <form action="revert_paiement.php" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce paiement?');">
                                                    <input type="hidden" name="paiement_id" value="<?php echo $paiement['id']; ?>">
                                                    <input type="hidden" name="niveau" value="<?php echo htmlspecialchars($niveau); ?>">
                                                    <button type="submit" class="btn btn-outline-danger">
                                                        <i class="fas fa-times"></i> Annuler
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
