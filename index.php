<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Daara Moderne</title>
    <link rel="stylesheet" href="css/custom.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="row justify-content-center">
        <div class="col-lg-10 text-center">
            <div class="card shadow-sm mb-5 border-0 fade-in">
                <div class="card-body p-5">
                    <i class="fas fa-mosque text-success fa-4x mb-3"></i>
                    <h1 class="display-5 fw-bold text-success mb-3">Bienvenue sur la plateforme de gestion</h1>
                    <h2 class="h3 mb-4">Daara Moderne Elhadji Ousmane DRAME</h2>
                    <p class="lead">Gérez les élèves, les inscriptions et les paiements en toute simplicité.</p>
                    <hr class="my-4">
                    <div class="row g-4 py-3">
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm niveau-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-graduate text-success fa-3x mb-3"></i>
                                    <h3>Gestion des Élèves</h3>
                                    <p>Ajoutez, modifiez ou supprimez des élèves par niveau.</p>
                                    <a href="eleves.php" class="btn btn-outline-success">Accéder</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm niveau-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-money-bill text-success fa-3x mb-3"></i>
                                    <h3>Paiements</h3>
                                    <p>Enregistrez les paiements mensuels et générez des reçus.</p>
                                    <a href="paiements.php" class="btn btn-outline-success">Accéder</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm niveau-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-bar text-success fa-3x mb-3"></i>
                                    <h3>Récapitulatif</h3>
                                    <p>Consultez les statistiques et récapitulatifs des paiements.</p>
                                    <a href="recapitulatif.php" class="btn btn-outline-success">Accéder</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
