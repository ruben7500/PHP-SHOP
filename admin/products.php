<?php
// Chemin vers les fichiers de base
$base_path = '../';
include $base_path . 'includes/header.php';

// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}require_admin($base_path . 'index.php');

// Gestion de la suppression d'un produit
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    try {
        // Supprimer d'abord le stock (contrainte de clé étrangère)
        $stmt = $conn->prepare("DELETE FROM stock WHERE id_item = ?");
        $stmt->execute([$product_id]);
        
        // Puis supprimer le produit
        $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
        $stmt->execute([$product_id]);
        
        $_SESSION['flash_message'] = "Le produit a été supprimé avec succès.";
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Erreur lors de la suppression: " . $e->getMessage();
    }
    
    header("Location: products.php");
    exit;
}

// Récupération des produits
$stmt = $conn->query("SELECT i.*, s.quantite FROM items i JOIN stock s ON i.id = s.id_item ORDER BY i.nom ASC");
$products = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-2xl font-bold mb-4 md:mb-0">Gestion des produits</h1>
        <a href="product_add.php" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded">
            <i class="fas fa-plus mr-2"></i> Ajouter un produit
        </a>
    </div>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['flash_message'] ?>
            <?php unset($_SESSION['flash_message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white shadow rounded overflow-hidden">
        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marque</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date d'ajout</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($products) === 0): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Aucun produit trouvé</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <img src="<?= !empty($product['image']) ? $base_path . $product['image'] : $base_path . 'assets/images/placeholder.jpg' ?>" 
                                            alt="<?= $product['nom'] ?>" class="h-12 w-12 object-cover border border-gray-200 rounded">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= $product['nom'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= $product['marque'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= number_format($product['prix'], 2, ',', ' ') ?> €</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $product['quantite'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $product['quantite'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y', strtotime($product['date_publication'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap space-x-1 space-y-1">
                                        <a href="product_edit.php?id=<?= $product['id'] ?>" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700">
                                            <i class="fas fa-edit mr-1"></i> Modifier
                                        </a>
                                        <a href="products.php?action=delete&id=<?= $product['id'] ?>" 
                                           class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                            <i class="fas fa-trash mr-1"></i> Supprimer
                                        </a>
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

<?php include $base_path . 'includes/footer.php'; ?>