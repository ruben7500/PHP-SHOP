<?php
// Démarrer la session et inclure les fichiers nécessaires avant tout output HTML
session_start();
// Détecter si nous sommes dans un sous-dossier
$config_path = file_exists('config/database.php') ? 'config/database.php' : '../config/database.php';
require_once $config_path;
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
require_login();

// Vérifier si une commande vient d'être passée
if (!isset($_SESSION['invoice_id'])) {
    header("Location: index.php");
    exit;
}

$invoice_id = $_SESSION['invoice_id'];
unset($_SESSION['invoice_id']); // Nettoyer la session

// Récupérer les informations de la facture
try {
    $stmt = $conn->prepare("SELECT * FROM invoice WHERE id = ? AND id_user = ?");
    $stmt->execute([$invoice_id, $_SESSION['user_id']]);
    $invoice = $stmt->fetch();
    
    if (!$invoice) {
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['flash_message'] = "Erreur lors de la récupération de la commande: " . $e->getMessage();
    header("Location: index.php");
    exit;
}

// Inclure le header après le traitement
include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex justify-center">
        <div class="w-full md:w-2/3">
            <div class="bg-white border border-green-500 rounded-lg shadow-md overflow-hidden">
                <div class="bg-green-500 text-white px-6 py-4">
                    <h4 class="text-xl font-semibold">Commande confirmée</h4>
                </div>
                <div class="p-6">
                    <div class="text-center mb-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-green-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h2 class="text-2xl font-bold mb-2">Merci pour votre commande !</h2>
                        <p class="text-lg text-gray-600">Votre commande a été enregistrée avec succès.</p>
                    </div>
                    
                    <div class="bg-gray-50 border border-gray-200 rounded-lg mb-6">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h5 class="font-medium text-gray-700">Détails de la commande</h5>
                        </div>
                        <div class="p-4">
                            <p class="mb-2"><span class="font-semibold">Numéro de commande :</span> #<?= $invoice_id ?></p>
                            <p class="mb-2"><span class="font-semibold">Date :</span> <?= date('d/m/Y H:i', strtotime($invoice['date_transaction'])) ?></p>
                            <p class="mb-2"><span class="font-semibold">Montant total :</span> <?= number_format($invoice['montant'], 2, ',', ' ') ?> €</p>
                            <p class="mb-2">
                                <span class="font-semibold">Adresse de livraison :</span><br>
                                <?= $invoice['adresse_facturation'] ?><br>
                                <?= $invoice['code_postal'] ?> <?= $invoice['ville'] ?>
                            </p>
                        </div>
                    </div>
                    
                    <p class="text-gray-600 mb-6">Un email de confirmation a été envoyé à votre adresse email. Vous pouvez suivre l'état de votre commande dans votre espace client.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white text-center font-medium py-3 px-4 rounded-md transition duration-200">Retour à l'accueil</a>
                        <a href="catalog.php" class="border border-gray-300 hover:bg-gray-50 text-gray-700 text-center font-medium py-3 px-4 rounded-md transition duration-200">Continuer vos achats</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>