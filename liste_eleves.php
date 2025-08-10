<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'supabase.php';
require 'includes/header.php';

$supabase = Supabase::getInstance();
$niveauSelectionne = isset($_GET['niveau']) ? $_GET['niveau'] : '';

// Récupération des niveaux distincts
$listeNiveaux = $supabase->distinct('eleves', 'niveau');

// Tri des niveaux selon l'ordre défini
$niveauOrdre = ['Petite Section', 'Moyenne Section', 'Grande Section', 'Coranique'];
usort($listeNiveaux, function($a, $b) use ($niveauOrdre) {
    $posA = array_search($a, $niveauOrdre);
    $posB = array_search($b, $niveauOrdre);
    
    // Si un des niveaux n'est pas dans l'ordre prédéfini, le placer à la fin
    if ($posA === false) return 1;
    if ($posB === false) return -1;
    
    return $posA - $posB;
});

// Construction de la requête selon le filtre de niveau
if (!empty($niveauSelectionne)) {
    $result = $supabase->select('eleves', [
        'filter' => ['niveau' => $niveauSelectionne],
        'order' => 'nom.asc'
    ]);
} else {
    $result = $supabase->select('eleves', [
        'order' => 'niveau.asc,nom.asc'
    ]);
    
    // Tri manuel supplémentaire pour respecter l'ordre des niveaux
    usort($result, function($a, $b) use ($niveauOrdre) {
        $posA = array_search($a['niveau'], $niveauOrdre);
        $posB = array_search($b['niveau'], $niveauOrdre);
        
        if ($posA === $posB) {
            return strcmp($a['nom'], $b['nom']);
        }
        
        // Si un des niveaux n'est pas dans l'ordre prédéfini, le placer à la fin
        if ($posA === false) return 1;
        if ($posB === false) return -1;
        
        return $posA - $posB;
    });
}

$index = 1;
?>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h3 class="h5 mb-0 text-center">
                    <i class="fas fa-list me-2"></i>Liste Complète des Élèves
                </h3>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <form method="GET" class="d-flex">
                            <select name="niveau" id="niveau" class="form-select me-2" onchange="this.form.submit()">
                                <option value="">Tous les niveaux</option>
                                <?php foreach ($listeNiveaux as $niveau): ?>
                                    <option value="<?php echo htmlspecialchars($niveau); ?>" 
                                        <?php echo ($niveau == $niveauSelectionne) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($niveau); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-success me-2">
                                <i class="fas fa-filter me-1"></i> Filtrer
                            </button>
                            <?php if (!empty($niveauSelectionne)): ?>
                                <a href="export_liste_pdf.php?niveau=<?php echo urlencode($niveauSelectionne); ?>" class="btn btn-outline-success" target="_blank">
                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un élève...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <!-- Table for desktop -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover table-green">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Prénom</th>
                                    <th>Nom</th>
                                    <th>Âge</th>
                                    <th>Niveau</th>
                                    <th>Tuteur</th>
                                    <th>N° Tuteur</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($result)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-3">Aucun élève trouvé.</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                $index = 1;
                                foreach ($result as $eleve): 
                                ?>
                                <tr>
                                    <td><?php echo $index; ?></td>
                                    <td><?php echo htmlspecialchars($eleve['prenom']); ?></td>
                                    <td><?php echo htmlspecialchars($eleve['nom']); ?></td>
                                    <td><?php echo $eleve['age'] ?? '-'; ?></td>
                                    <td>
                                        <span class="badge bg-success"><?php echo htmlspecialchars($eleve['niveau']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($eleve['nom_complet_tuteur'] ?? '-'); ?></td>
                                    <td>
                                        <?php if (!empty($eleve['telephone_tuteur'])): ?>
                                            <a href="tel:<?php echo htmlspecialchars($eleve['telephone_tuteur']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($eleve['telephone_tuteur']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Non renseigné</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php $index++; ?>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Cards for mobile -->
                    <div class="d-md-none">
                        <?php if (empty($result)): ?>
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle me-2"></i>Aucun élève trouvé.
                            </div>
                        <?php else: ?>
                            <?php 
                            $index = 1;
                            foreach ($result as $eleve): 
                            ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <?php echo htmlspecialchars($eleve['prenom'] . ' ' . $eleve['nom']); ?>
                                        </h5>
                                        <span class="badge bg-success">
                                            <?php echo htmlspecialchars($eleve['niveau']); ?>
                                        </span>
                                    </div>

                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">Informations de l'élève :</small>
                                        <p class="mb-1">
                                            <i class="fas fa-hashtag me-2"></i>
                                            N°: <?php echo $index++; ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-birthday-cake me-2"></i>
                                            Âge: <?php echo $eleve['age'] ?? '-'; ?> ans
                                        </p>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted d-block mb-1">Informations du tuteur :</small>
                                        <p class="mb-1">
                                            <i class="fas fa-user-tie me-2"></i>
                                            <?php echo htmlspecialchars($eleve['nom_complet_tuteur'] ?? 'Non renseigné'); ?>
                                        </p>
                                        <?php if (!empty($eleve['telephone_tuteur'])): ?>
                                            <a href="tel:<?php echo htmlspecialchars($eleve['telephone_tuteur']); ?>" 
                                               class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-phone-alt me-2"></i>
                                                <?php echo htmlspecialchars($eleve['telephone_tuteur']); ?>
                                            </a>
                                        <?php else: ?>
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-phone-alt me-2"></i>
                                                Téléphone non renseigné
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
<script src="js/search.js"></script>
