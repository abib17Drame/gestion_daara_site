<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'supabase.php';
require 'includes/header.php';

$supabase = Supabase::getInstance();

// Configuration des mois
$mois_options = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai',
    6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre',
    10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

// Étape 1: Sélection du niveau
if (!isset($_GET['niveau'])) {
    displayLevelSelection();
    include 'includes/footer.php';
    exit();
}

// Étape 2: Traitement principal
$niveau = htmlspecialchars($_GET['niveau']);
$mois = isset($_GET['mois']) ? (int)$_GET['mois'] : date('n');
$annee = isset($_GET['annee']) ? (int)$_GET['annee'] : date('Y');

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    processPayments($supabase, $niveau, $mois, $annee);
}

// Affichage principal
displayPaymentInterface($supabase, $niveau, $mois, $annee, $mois_options);
include 'includes/footer.php';

// Fonctions
function displayLevelSelection() {
    global $mois_options;
    ?>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center p-5">
                    <h2 class="text-success mb-4"><i class="fas fa-history me-2"></i>Sélectionnez le Niveau</h2>
                    <div class="row g-4 mt-2">
                        <div class="col-md-6 col-lg-3">
                            <a href="paiements.php?niveau=Petite+Section" class="text-decoration-none">
                                <div class="card h-100 niveau-card border-success">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-child text-success fa-3x mb-3"></i>
                                        <h4>Petite Section</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="paiements.php?niveau=Moyenne+Section" class="text-decoration-none">
                                <div class="card h-100 niveau-card border-success">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-child text-success fa-3x mb-3"></i>
                                        <h4>Moyenne Section</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="paiements.php?niveau=Grande+Section" class="text-decoration-none">
                                <div class="card h-100 niveau-card border-success">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-child text-success fa-3x mb-3"></i>
                                        <h4>Grande Section</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="paiements.php?niveau=Coranique" class="text-decoration-none">
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
}

function processPayments($supabase, $niveau, $mois, $annee) {
    if (!isset($_POST['eleve_ids']) || !is_array($_POST['eleve_ids'])) {
        $_SESSION['error_message'] = "Aucun élève sélectionné";
        return;
    }

    $errors = [];
    $successCount = 0;

    foreach ($_POST['eleve_ids'] as $eleveId) {
        $eleveId = (int)$eleveId;
        
        // Récupération et validation du montant
        $montant = 2500; // Valeur par défaut
        if (isset($_POST['montants'][$eleveId])) {
            $montant = (int)$_POST['montants'][$eleveId];
            if ($montant <= 0) {
                $errors[] = "Montant invalide pour l'élève ID $eleveId";
                continue;
            }
        }

        // Préparation des données
        $paymentData = [
            'eleve_id' => $eleveId,
            'mois' => $mois,
            'annee' => $annee,
            'montant' => $montant,
            'date_paiement' => date('Y-m-d H:i:s')
        ];

        // Vérification existence paiement
        $existing = $supabase->select('paiements', [
            'filter' => [
                'eleve_id' => $eleveId,
                'mois' => $mois,
                'annee' => $annee
            ],
            'limit' => 1
        ]);

        try {
            if (empty($existing)) {
                $result = $supabase->insert('paiements', $paymentData);
            } else {
                $result = $supabase->update('paiements', $paymentData, [
                    'eleve_id' => $eleveId,
                    'mois' => $mois,
                    'annee' => $annee
                ]);
            }
            
            if ($result) {
                $successCount++;
            } else {
                $errors[] = "Erreur lors de l'enregistrement pour l'élève ID $eleveId";
            }
        } catch (Exception $e) {
            $errors[] = "Erreur base de données pour l'élève ID $eleveId: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
    
    if ($successCount > 0) {
        $_SESSION['success_message'] = "$successCount paiement(s) enregistré(s) avec succès";
    }

    header("Location: paiements.php?niveau=".urlencode($niveau)."&mois=$mois&annee=$annee");
    exit();
}

function displayPaymentInterface($supabase, $niveau, $mois, $annee, $mois_options) {
    // Récupération des élèves impayés
    $eleves = $supabase->select('eleves', [
        'filter' => ['niveau' => $niveau],
        'order' => 'nom.asc'
    ]);

    $paiements = $supabase->select('paiements', [
        'filter' => [
            'mois' => $mois,
            'annee' => $annee
        ]
    ]);

    // Filtrage des impayés
    $impayes = [];
    foreach ($eleves as $eleve) {
        $aPaye = false;
        foreach ($paiements as $p) {
            if ($p['eleve_id'] == $eleve['id']) {
                $aPaye = true;
                break;
            }
        }
        
        if (!$aPaye) {
            $eleve['montant'] = 2500; // Valeur par défaut
            $impayes[] = $eleve;
        }
    }
    ?>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h3 class="h5 mb-0">
                            <i class="fas fa-history me-2"></i>Gestion des Paiements - <?php echo htmlspecialchars($niveau); ?>
                        </h3>
                        <a href="paiements.php" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-exchange-alt me-1"></i> Changer de niveau
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Messages -->
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error_message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    
                    <!-- Filtres -->
                    <form method="GET" class="row g-3 mb-4">
                        <input type="hidden" name="niveau" value="<?= $niveau ?>">
                        
                        <div class="col-md-4">
                            <label class="form-label">Mois</label>
                            <select name="mois" class="form-select">
                                <?php foreach ($mois_options as $num => $nom): ?>
                                    <option value="<?= $num ?>" <?= $num == $mois ? 'selected' : '' ?>>
                                        <?= $nom ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Année</label>
                            <input type="number" name="annee" class="form-control" 
                                   value="<?= $annee ?>" min="2000" max="2100">
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-filter me-1"></i> Filtrer
                            </button>
                        </div>
                    </form>
                    
                    <!-- Formulaire principal -->
                    <?php if (!empty($impayes)): ?>
                        <form method="POST" id="paymentForm">
                            <div class="table-responsive">
                                <table class="table table-hover table-green">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="selectAll">
                                            </th>
                                            <th>ID</th>
                                            <th>Nom</th>
                                            <th>Prénom</th>
                                            <th>Montant (FCFA)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($impayes as $index => $eleve): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="eleve_ids[]" 
                                                       value="<?= $eleve['id'] ?>" class="student-check">
                                            </td>
                                            <td><?= $eleve['id'] ?></td>
                                            <td><?= htmlspecialchars($eleve['nom']) ?></td>
                                            <td><?= htmlspecialchars($eleve['prenom']) ?></td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="montants[<?= $eleve['id'] ?>]" 
                                                           value="<?= $eleve['montant'] ?>" 
                                                           class="form-control amount-input"
                                                           min="0" step="100" required
                                                           data-default="<?= $eleve['montant'] ?>">
                                                    <span class="input-group-text">FCFA</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Bouton d'action flottant toujours visible -->
                            <div style="position: fixed; right: 20px; bottom: 20px; z-index: 1050;">
                                <button type="submit" class="btn btn-success btn-lg shadow rounded-pill">
                                    <i class="fas fa-save me-2"></i> Enregistrer
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <h4>Tous les élèves ont payé pour <?= $mois_options[$mois] ?> <?= $annee ?></h4>
                            <a href="historique.php?niveau=<?= urlencode($niveau) ?>&mois=<?= $mois ?>&annee=<?= $annee ?>" 
                               class="btn btn-outline-success mt-2">
                                <i class="fas fa-history me-1"></i> Voir l'historique
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion des sélections
        const selectAll = document.getElementById('selectAll');
        const studentChecks = document.querySelectorAll('.student-check');
        
        selectAll.addEventListener('change', function() {
            studentChecks.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Gestion des montants
        const form = document.getElementById('paymentForm');
        const amountInputs = document.querySelectorAll('.amount-input');
        
        // Sauvegarde des modifications dans localStorage
        amountInputs.forEach(input => {
            // Restaurer la valeur si elle existe
            const savedValue = localStorage.getItem(`amount_${input.name}`);
            if (savedValue && savedValue !== input.dataset.default) {
                input.value = savedValue;
            }
            
            // Sauvegarder les modifications
            input.addEventListener('change', function() {
                localStorage.setItem(`amount_${this.name}`, this.value);
            });
        });
        
        // Forcer la soumission des valeurs modifiées
        form.addEventListener('submit', function() {
            amountInputs.forEach(input => {
                if (input.value !== input.dataset.default) {
                    input.setAttribute('value', input.value);
                }
            });
        });
    });
    </script>
    <?php
}