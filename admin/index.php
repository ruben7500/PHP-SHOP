<?php

$base_path = '../';
include $base_path . 'includes/header.php';

// Vérifier les droits d'administration
// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_admin($base_path . 'index.php');
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Tableau de bord d'administration</h1>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['flash_message'] ?>
            <?php unset($_SESSION['flash_message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="flex flex-col md:flex-row gap-6">
        <div class="w-full md:w-1/4">
            <div class="bg-white shadow rounded overflow-hidden">
                <a href="index.php" class="block px-4 py-2 bg-blue-600 text-white font-medium">Tableau de bord</a>
                <a href="products.php" class="block px-4 py-2 hover:bg-gray-100 transition">Gestion des produits</a>
                <a href="users.php" class="block px-4 py-2 hover:bg-gray-100 transition">Gestion des utilisateurs</a>
                <a href="orders.php" class="block px-4 py-2 hover:bg-gray-100 transition">Gestion des commandes</a>
            </div>
        </div>
        
        <div class="w-full md:w-3/4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <?php
                // Statistiques du nombre de produits
                $stmt = $conn->query("SELECT COUNT(*) FROM items");
                $products_count = $stmt->fetchColumn();
                
                // Statistiques du nombre d'utilisateurs
                $stmt = $conn->query("SELECT COUNT(*) FROM users");
                $users_count = $stmt->fetchColumn();
                
                // Statistiques du stock total
                $stmt = $conn->query("SELECT SUM(quantite) FROM stock");
                $stock_count = $stmt->fetchColumn();
                ?>
                
                <div class="bg-blue-600 text-white rounded shadow overflow-hidden">
                    <div class="p-4">
                        <h5 class="font-medium">Produits</h5>
                        <h2 class="text-2xl font-bold my-2"><?= $products_count ?></h2>
                        <p class="mb-4">Produits dans le catalogue</p>
                        <a href="products.php" class="inline-block bg-white text-blue-600 px-3 py-1 rounded text-sm font-medium">Gérer</a>
                    </div>
                </div>
                
                <div class="bg-green-600 text-white rounded shadow overflow-hidden">
                    <div class="p-4">
                        <h5 class="font-medium">Utilisateurs</h5>
                        <h2 class="text-2xl font-bold my-2"><?= $users_count ?></h2>
                        <p class="mb-4">Comptes enregistrés</p>
                        <a href="users.php" class="inline-block bg-white text-green-600 px-3 py-1 rounded text-sm font-medium">Gérer</a>
                    </div>
                </div>
                
                <div class="bg-cyan-600 text-white rounded shadow overflow-hidden">
                    <div class="p-4">
                        <h5 class="font-medium">Stock</h5>
                        <h2 class="text-2xl font-bold my-2"><?= $stock_count ?: 0 ?></h2>
                        <p class="mb-4">Produits en stock</p>
                        <a href="products.php" class="inline-block bg-white text-cyan-600 px-3 py-1 rounded text-sm font-medium">Gérer</a>
                    </div>
                </div>
            </div>
            
            <div class="bg-white shadow rounded overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h5 class="font-medium">Derniers produits ajoutés</h5>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date d'ajout</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                $stmt = $conn->query("SELECT i.*, s.quantite FROM items i JOIN stock s ON i.id = s.id_item ORDER BY i.date_publication DESC LIMIT 5");
                                $recent_products = $stmt->fetchAll();
                                
                                foreach ($recent_products as $product): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap"><?= $product['nom'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= number_format($product['prix'], 2, ',', ' ') ?> €</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $product['quantite'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= $product['quantite'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y', strtotime($product['date_publication'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="product_edit.php?id=<?= $product['id'] ?>" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                Modifier
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>