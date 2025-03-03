<?php
$base_path = '../';
include $base_path . 'includes/header.php';

// Vérifier les droits d'administration
// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_admin($base_path . 'index.php');

// Vérifier si l'ID de la commande est fourni
if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$invoice_id = intval($_GET['id']);

// Récupérer les détails de la facture
try {
    $stmt = $conn->prepare("
        SELECT i.*, u.nom, u.prenom, u.email 
        FROM invoice i 
        JOIN users u ON i.id_user = u.id 
        WHERE i.id = ?
    ");
    $stmt->execute([$invoice_id]);
    $invoice = $stmt->fetch();
    
    if (!$invoice) {
        $_SESSION['flash_message'] = "Commande introuvable.";
        header("Location: orders.php");
        exit;
    }
    
    // Récupérer les articles de la commande (en utilisant la date de transaction)
    $stmt = $conn->prepare("
        SELECT o.*, i.nom, i.prix, i.image 
        FROM orders o 
        JOIN items i ON o.id_item = i.id 
        WHERE o.id_user = ? AND o.date_commande BETWEEN ? AND DATE_ADD(?, INTERVAL 1 MINUTE)
    ");
    $stmt->execute([
        $invoice['id_user'], 
        $invoice['date_transaction'],
        $invoice['date_transaction']
    ]);
    $order_items = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $_SESSION['flash_message'] = "Erreur lors de la récupération des détails de la commande: " . $e->getMessage();
    header("Location: orders.php");
    exit;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Détails de la commande #<?= $invoice_id ?></h1>
        <a href="orders.php" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-1"></i> Retour aux commandes
        </a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h4 class="font-medium text-gray-700">Articles commandés</h4>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix unitaire</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <img src="<?= $base_path . (!empty($item['image']) ? $item['image'] : 'assets/images/placeholder.jpg') ?>" alt="<?= $item['nom'] ?>" class="w-16 h-16 object-cover mr-4">
                                                <span><?= $item['nom'] ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= number_format($item['prix'], 2, ',', ' ') ?> €</td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= $item['quantite'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= number_format($item['prix'] * $item['quantite'], 2, ',', ' ') ?> €</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50">
                                    <td colspan="3" class="px-6 py-4 text-right font-bold">Total</td>
                                    <td class="px-6 py-4 font-bold"><?= number_format($invoice['montant'], 2, ',', ' ') ?> €</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h4 class="font-medium text-gray-700">Actions</h4>
                </div>
                <div class="p-6">
                    <form action="orders.php?action=update_status&id=<?= $invoice_id ?>" method="POST" class="flex flex-col md:flex-row gap-4">
                        <div class="flex-grow">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Modifier le statut</label>
                            <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="en_attente" <?= $invoice['statut'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="payee" <?= $invoice['statut'] === 'payee' ? 'selected' : '' ?>>Payée</option>
                                <option value="annulee" <?= $invoice['statut'] === 'annulee' ? 'selected' : '' ?>>Annulée</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div>
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h4 class="font-medium text-gray-700">Informations client</h4>
                </div>
                <div class="p-6">
                    <p class="mb-2"><span class="font-semibold">Nom :</span> <?= $invoice['prenom'] ?> <?= $invoice['nom'] ?></p>
                    <p class="mb-2"><span class="font-semibold">Email :</span> <?= $invoice['email'] ?></p>
                    <p class="mb-2"><span class="font-semibold">ID client :</span> <?= $invoice['id_user'] ?></p>
                    <div class="mt-4">
                        <a href="users.php?action=view&id=<?= $invoice['id_user'] ?>" class="text-blue-600 hover:text-blue-800">
                            Voir le profil client
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h4 class="font-medium text-gray-700">Informations de commande</h4>
                </div>
                <div class="p-6">
                    <p class="mb-2"><span class="font-semibold">Numéro de commande :</span> #<?= $invoice_id ?></p>
                    <p class="mb-2"><span class="font-semibold">Date :</span> <?= date('d/m/Y H:i', strtotime($invoice['date_transaction'])) ?></p>
                    <p class="mb-2">
                        <span class="font-semibold">Statut :</span>
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
                    </p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h4 class="font-medium text-gray-700">Adresse de livraison</h4>
                </div>
                <div class="p-6">
                    <p><?= $invoice['adresse_facturation'] ?></p>
                    <p><?= $invoice['code_postal'] ?> <?= $invoice['ville'] ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>