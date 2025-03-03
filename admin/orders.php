<?php
$base_path = '../';
include $base_path . 'includes/header.php';

// Vérifier les droits d'administration
// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_admin($base_path . 'index.php');

// Traitement des actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $invoice_id = intval($_GET['id']);
    
    if ($action === 'update_status' && isset($_POST['status'])) {
        $status = $_POST['status'];
        
        try {
            $stmt = $conn->prepare("UPDATE invoice SET statut = ? WHERE id = ?");
            $stmt->execute([$status, $invoice_id]);
            
            $_SESSION['flash_message'] = "Statut de la commande #$invoice_id mis à jour.";
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Erreur lors de la mise à jour du statut: " . $e->getMessage();
        }
        
        header("Location: orders.php");
        exit;
    }
}

// Récupérer toutes les commandes
try {
    // Récupérer les factures avec les informations utilisateur
    $stmt = $conn->prepare("
        SELECT i.*, u.nom, u.prenom
        FROM invoice i 
        JOIN users u ON i.id_user = u.id
        ORDER BY i.date_transaction DESC
    ");
    $stmt->execute();
    $invoices = $stmt->fetchAll();
    
    // Pour chaque facture, récupérer le nombre d'articles
    foreach ($invoices as &$invoice) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM orders 
            WHERE id_user = ? AND date_commande BETWEEN ? AND DATE_ADD(?, INTERVAL 1 MINUTE)
        ");
        $stmt->execute([
            $invoice['id_user'], 
            $invoice['date_transaction'],
            $invoice['date_transaction']
        ]);
        $invoice['nb_articles'] = $stmt->fetchColumn();
    }
} catch (PDOException $e) {
    $_SESSION['flash_message'] = "Erreur lors de la récupération des commandes: " . $e->getMessage();
}
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Gestion des commandes</h1>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['flash_message'] ?>
            <?php unset($_SESSION['flash_message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="flex flex-col md:flex-row gap-6">
        <div class="w-full md:w-1/4">
            <div class="bg-white shadow rounded overflow-hidden">
                <a href="index.php" class="block px-4 py-2 hover:bg-gray-100 transition">Tableau de bord</a>
                <a href="products.php" class="block px-4 py-2 hover:bg-gray-100 transition">Gestion des produits</a>
                <a href="users.php" class="block px-4 py-2 hover:bg-gray-100 transition">Gestion des utilisateurs</a>
                <a href="orders.php" class="block px-4 py-2 bg-blue-600 text-white font-medium">Gestion des commandes</a>
            </div>
        </div>
        
        <div class="w-full md:w-3/4">
            <div class="bg-white shadow rounded overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h5 class="font-medium">Liste des commandes</h5>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Commande</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre d'articles</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($invoices)): ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Aucune commande trouvée</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($invoices as $invoice): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">#<?= $invoice['id'] ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?= $invoice['prenom'] ?> <?= $invoice['nom'] ?></td>
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
                                                <div class="flex space-x-2">
                                                    <a href="order_details.php?id=<?= $invoice['id'] ?>" class="text-blue-600 hover:text-blue-800">
                                                        Détails
                                                    </a>
                                                    <button onclick="openStatusModal(<?= $invoice['id'] ?>, '<?= $invoice['statut'] ?>')" class="text-green-600 hover:text-green-800">
                                                        Statut
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour modifier le statut -->
<div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Modifier le statut de la commande</h3>
        <form id="statusForm" method="POST" action="">
            <div class="mb-4">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="en_attente">En attente</option>
                    <option value="payee">Payée</option>
                    <option value="annulee">Annulée</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeStatusModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openStatusModal(invoiceId, currentStatus) {
    document.getElementById('statusForm').action = `orders.php?action=update_status&id=${invoiceId}`;
    document.getElementById('status').value = currentStatus;
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}
</script>

<?php include $base_path . 'includes/footer.php'; ?> 