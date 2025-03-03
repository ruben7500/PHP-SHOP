<?php
// Démarrer la session et inclure les fichiers nécessaires avant tout output HTML
session_start();
// Détecter si nous sommes dans un sous-dossier
$config_path = file_exists('config/database.php') ? 'config/database.php' : '../config/database.php';
require_once $config_path;
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
require_login();

// Récupérer les commandes de l'utilisateur
try {
    // Récupérer les factures
    $stmt = $conn->prepare("
        SELECT i.* FROM invoice i 
        WHERE i.id_user = ? 
        ORDER BY i.date_transaction DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $invoices = $stmt->fetchAll();
    
    // Pour chaque facture, récupérer le nombre d'articles
    foreach ($invoices as &$invoice) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM orders 
            WHERE id_user = ? AND date_commande BETWEEN ? AND DATE_ADD(?, INTERVAL 1 MINUTE)
        ");
        $stmt->execute([
            $_SESSION['user_id'], 
            $invoice['date_transaction'],
            $invoice['date_transaction']
        ]);
        $invoice['nb_articles'] = $stmt->fetchColumn();
    }
    
} catch (PDOException $e) {
    $_SESSION['flash_message'] = "Erreur lors de la récupération des commandes: " . $e->getMessage();
    header("Location: index.php");
    exit;
}

// Inclure le header après le traitement
include 'includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Mes commandes</h1>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['flash_message'] ?>
            <?php unset($_SESSION['flash_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($invoices)): ?>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            Vous n'avez pas encore passé de commande. <a href="catalog.php" class="underline">Commencer vos achats</a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Commande</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Articles</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($invoices as $invoice): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">#<?= $invoice['id'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($invoice['date_transaction'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= number_format($invoice['montant'], 2, ',', ' ') ?> €</td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= $invoice['nb_articles'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $status_class = '';
                                    $status_text = '';
                                    
                                    switch($invoice['statut']) {
                                        case 'en_attente':
                                            $status_class = 'bg-yellow-100 text-yellow-800';
                                            $status_text = 'En attente';
                                            break;
                                        case 'payee':
                                            $status_class = 'bg-green-100 text-green-800';
                                            $status_text = 'Payée';
                                            break;
                                        case 'annulee':
                                            $status_class = 'bg-red-100 text-red-800';
                                            $status_text = 'Annulée';
                                            break;
                                    }
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_class ?>">
                                        <?= $status_text ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="order_details.php?id=<?= $invoice['id'] ?>" class="text-blue-600 hover:text-blue-800">
                                        Détails
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 