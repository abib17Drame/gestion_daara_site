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
$message = '';

// Afficher les niveaux si aucun n'est sélectionné
if (empty($niveauSelectionne)) {
?>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center p-5">
                    <h2 class="text-success mb-4"><i class="fas fa-user-graduate me-2"></i>Sélectionnez le Niveau</h2>
                    <div class="row g-4 mt-2">
                        <div class="col-md-6 col-lg-3">
                            <a href="eleves.php?niveau=Petite+Section" class="text-decoration-none">
                                <div class="card h-100 niveau-card border-success">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-child text-success fa-3x mb-3"></i>
                                        <h4>Petite Section</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="eleves.php?niveau=Moyenne+Section" class="text-decoration-none">
                                <div class="card h-100 niveau-card border-success">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-child text-success fa-3x mb-3"></i>
                                        <h4>Moyenne Section</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="eleves.php?niveau=Grande+Section" class="text-decoration-none">
                                <div class="card h-100 niveau-card border-success">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-child text-success fa-3x mb-3"></i>
                                        <h4>Grande Section</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="eleves.php?niveau=Coranique" class="text-decoration-none">
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

// Si un niveau est sélectionné, récupérer et afficher les élèves de ce niveau
$eleves = $supabase->select('eleves', [
    'filter' => ['niveau' => $niveauSelectionne],
    'order' => 'nom.asc,prenom.asc'
]);
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="h5 mb-0">
                        <i class="fas fa-user-graduate me-2"></i>Gestion des Élèves - <?php echo htmlspecialchars($niveauSelectionne); ?>
                    </h3>
                    <a href="eleves.php" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-exchange-alt me-1"></i> Changer de niveau
                    </a>
                </div>
            </div>
            <div class="card-body">
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

                <div class="d-flex justify-content-between mb-4">
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un élève...">
                    </div>
                    <a href="ajouter_eleve.php?niveau=<?php echo urlencode($niveauSelectionne); ?>" class="btn btn-success">
                        <i class="fas fa-plus-circle me-1"></i> Ajouter un élève
                    </a>
                </div>

                <?php if (empty($eleves)): ?>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>Aucun élève n'est inscrit dans ce niveau. Ajoutez-en un nouveau !
                    </div>
                <?php else: ?>
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
                                    <th>Téléphone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $index = 1; ?>
                                <?php foreach ($eleves as $eleve): ?>
                                <tr>
                                    <td><?php echo $index++; ?></td>
                                    <td data-prenom="<?php echo htmlspecialchars(strtolower($eleve['prenom'])); ?>">
                                        <?php echo htmlspecialchars($eleve['prenom']); ?>
                                    </td>
                                    <td data-nom="<?php echo htmlspecialchars(strtolower($eleve['nom'])); ?>">
                                        <?php echo htmlspecialchars($eleve['nom']); ?>
                                    </td>
                                    <td><?php echo $eleve['age'] ?? '-'; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($eleve['niveau']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($eleve['nom_complet_tuteur'] ?? '-'); ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($eleve['telephone_tuteur'])): ?>
                                            <a href="tel:<?php echo htmlspecialchars($eleve['telephone_tuteur']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($eleve['telephone_tuteur']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Non renseigné</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="modifier_eleve.php?id=<?php echo $eleve['id']; ?>" class="btn btn-outline-success">
                                                <i class="fas fa-edit"></i> Modifier
                                            </a>
                                            <a href="supprimer_eleve.php?id=<?php echo $eleve['id']; ?>" class="btn btn-outline-danger" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élève ? Cette action est irréversible.')">
                                                <i class="fas fa-trash-alt"></i> Supprimer
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Cards for mobile -->
                    <div class="d-md-none">
                        <?php 
                        $index = 1;
                        foreach ($eleves as $eleve): 
                        ?>
                        <div class="card mb-3 student-item">
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
                                        <i class="fas fa-user-graduate me-2"></i>
                                        N°: <?php echo $index++; ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-birthday-cake me-2"></i>
                                        Âge: <?php echo $eleve['age'] ?? '-'; ?> ans
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">Informations du tuteur :</small>
                                    <p class="mb-1">
                                        <i class="fas fa-user-tie me-2"></i>
                                        <?php echo htmlspecialchars($eleve['nom_complet_tuteur'] ?? 'Non renseigné'); ?>
                                    </p>
                                    <?php if (!empty($eleve['telephone_tuteur'])): ?>
                                        <a href="tel:<?php echo htmlspecialchars($eleve['telephone_tuteur']); ?>" 
                                           class="btn btn-sm btn-outline-success mb-2">
                                            <i class="fas fa-phone-alt me-2"></i>
                                            <?php echo htmlspecialchars($eleve['telephone_tuteur']); ?>
                                        </a>
                                    <?php else: ?>
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-phone-alt me-2"></i>
                                            Téléphone non renseigné
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="modifier_eleve.php?id=<?php echo $eleve['id']; ?>" 
                                       class="btn btn-outline-success flex-grow-1">
                                        <i class="fas fa-edit me-1"></i> Modifier
                                    </a>
                                    <a href="supprimer_eleve.php?id=<?php echo $eleve['id']; ?>" 
                                       class="btn btn-outline-danger flex-grow-1"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élève ? Cette action est irréversible.')">
                                        <i class="fas fa-trash-alt me-1"></i> Supprimer
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="js/search.js"></script>
