<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Daara Moderne Elhadji Ousmane DRAME</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="css/custom.css">
</head>
<body>
    <!-- Navbar Bootstrap -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-mosque me-2"></i>DMEOD
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="eleves.php"><i class="fas fa-user-graduate me-1"></i> Élèves</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="paiements.php"><i class="fas fa-money-bill me-1"></i> Paiements</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="historique.php"><i class="fas fa-history me-1"></i> Historique</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="recapitulatif.php"><i class="fas fa-chart-bar me-1"></i> Récapitulatif</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="liste_eleves.php"><i class="fas fa-list me-1"></i> Liste Élèves</a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Déconnexion</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Le contenu principal sera inséré ici -->
