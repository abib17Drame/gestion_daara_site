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

if (empty($niveau)) {
    header("Location: eleves.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $niveau = $_POST['niveau'];
    $telephone_tuteur = trim($_POST['telephone_tuteur']);
    $nom_complet_tuteur = trim($_POST['nom_complet_tuteur']);
    $age = !empty($_POST['age']) ? intval($_POST['age']) : null;
    
    // Validation des données
    $errors = [];
    
    if (strlen($nom) < 2) {
        $errors[] = "Le nom doit contenir au moins 2 caractères";
    }
    
    if (strlen($prenom) < 2) {
        $errors[] = "Le prénom doit contenir au moins 2 caractères";
    }
    
    if (!empty($telephone_tuteur) && !preg_match("/^\+?[0-9]{8,15}$/", $telephone_tuteur)) {
        $errors[] = "Le numéro de téléphone n'est pas valide";
    }

    if (!empty($age) && ($age < 0 || $age > 120)) {
        $errors[] = "L'âge doit être compris entre 0 et 120 ans";
    }
    
    if (empty($errors)) {
        $data = [
            'nom' => $nom,
            'prenom' => $prenom,
            'niveau' => $niveau,
            'telephone_tuteur' => $telephone_tuteur,
            'nom_complet_tuteur' => $nom_complet_tuteur,
            'age' => $age
        ];
        
        $result = $supabase->insert('eleves', $data);
        
        if ($result) {
            $_SESSION['success_message'] = "L'élève a été ajouté avec succès!";
            header("Location: eleves.php?niveau=" . urlencode($niveau));
            exit();
        } else {
            $_SESSION['error_message'] = "Une erreur est survenue lors de l'ajout de l'élève.";
        }
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h3 class="h5 mb-0"><i class="fas fa-user-plus me-2"></i>Ajouter un Nouvel Élève</h3>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <!-- Section Informations de l'élève -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h4 class="h6 mb-0"><i class="fas fa-user-graduate me-2"></i>Informations de l'élève</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="prenom" class="form-label">Prénom :</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" required>
                                <div class="invalid-feedback">Le prénom est requis.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom :</label>
                                <input type="text" class="form-control" id="nom" name="nom" required>
                                <div class="invalid-feedback">Le nom est requis.</div>
                            </div>

                            <div class="mb-3">
                                <label for="age" class="form-label">Âge :</label>
                                <input type="number" class="form-control" id="age" name="age" min="0" max="120">
                                <div class="form-text">Optionnel</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="niveau" class="form-label">Niveau :</label>
                                <select class="form-select" id="niveau" name="niveau" required>
                                    <option value="Petite Section" <?php echo $niveau == 'Petite Section' ? 'selected' : ''; ?>>Petite Section</option>
                                    <option value="Moyenne Section" <?php echo $niveau == 'Moyenne Section' ? 'selected' : ''; ?>>Moyenne Section</option>
                                    <option value="Grande Section" <?php echo $niveau == 'Grande Section' ? 'selected' : ''; ?>>Grande Section</option>
                                    <option value="Coranique" <?php echo $niveau == 'Coranique' ? 'selected' : ''; ?>>Coranique</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section Informations du tuteur -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h4 class="h6 mb-0"><i class="fas fa-user-tie me-2"></i>Informations du tuteur</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="nom_complet_tuteur" class="form-label">Nom complet :</label>
                                <input type="text" class="form-control" id="nom_complet_tuteur" name="nom_complet_tuteur">
                                <div class="form-text">Optionnel</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="telephone_tuteur" class="form-label">Numéro de téléphone :</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control" id="telephone_tuteur" name="telephone_tuteur" placeholder="+221...">
                                </div>
                                <div class="form-text">Format: 7xxxxxxxx</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                        <a href="eleves.php?niveau=<?php echo urlencode($niveau); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>

<script>
// Validation du formulaire Bootstrap
(function() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>
