<?php include 'includes/header.php'; ?>

<div class="container mx-auto px-4 py-4">
    <h1 class="text-2xl font-bold mb-4">Catalogue de montres</h1>
    
    <div class="flex flex-col md:flex-row mb-4 gap-4">
        <div class="w-full md:w-1/2">
            <form action="" method="GET" class="flex">
                <input type="text" name="search" class="flex-grow border rounded px-3 py-2 mr-2" placeholder="Rechercher une montre..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Rechercher</button>
            </form>
        </div>
        <div class="w-full md:w-1/2">
            <div class="flex md:justify-end">
                <select name="filter" id="filter" class="border rounded px-3 py-2 w-auto" onchange="window.location = this.value">
                    <option value="catalog.php">Toutes les montres</option>
                    <option value="catalog.php?sort=price_asc" <?= isset($_GET['sort']) && $_GET['sort'] == 'price_asc' ? 'selected' : '' ?>>Prix croissant</option>
                    <option value="catalog.php?sort=price_desc" <?= isset($_GET['sort']) && $_GET['sort'] == 'price_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                    <option value="catalog.php?sort=newest" <?= isset($_GET['sort']) && $_GET['sort'] == 'newest' ? 'selected' : '' ?>>Nouveautés</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <?php
        // Requête de base
        $sql = "SELECT i.*, s.quantite FROM items i JOIN stock s ON i.id = s.id_item";
        $params = [];
        
        // Ajout recherche si présente
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = "%" . $_GET['search'] . "%";
            $sql .= " WHERE i.nom LIKE ? OR i.description LIKE ? OR i.marque LIKE ?";
            $params = [$search, $search, $search];
        }
        
        // Ajout tri si présent
        if (isset($_GET['sort'])) {
            switch ($_GET['sort']) {
                case 'price_asc':
                    $sql .= " ORDER BY i.prix ASC";
                    break;
                case 'price_desc':
                    $sql .= " ORDER BY i.prix DESC";
                    break;
                case 'newest':
                    $sql .= " ORDER BY i.date_publication DESC";
                    break;
                default:
                    $sql .= " ORDER BY i.nom ASC";
            }
        } else {
            $sql .= " ORDER BY i.nom ASC";
        }
        
        // Exécution de la requête
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        if (count($products) === 0): ?>
            <div class="col-span-full">
                <div class="bg-blue-100 text-blue-800 p-4 rounded">
                    Aucun produit trouvé. <a href="catalog.php" class="underline">Voir tous les produits</a>
                </div>
            </div>
        <?php else:
            foreach ($products as $product): ?>
                <div class="mb-4">
                    <div class="border rounded shadow h-full flex flex-col">
                        <img src="<?= !empty($product['image']) ? $product['image'] : 'assets/images/placeholder.jpg' ?>" class="w-full h-48 object-cover rounded-t" alt="<?= htmlspecialchars($product['nom']) ?>">
                        <div class="p-4 flex-grow">
                            <h5 class="text-lg font-semibold"><?= htmlspecialchars($product['nom']) ?></h5>
                            <p class="text-gray-500"><?= htmlspecialchars($product['marque']) ?></p>
                            <p class="mt-2"><?= substr(htmlspecialchars($product['description']), 0, 100) ?>...</p>
                            <div class="flex justify-between items-center mt-4">
                                <span class="font-bold text-blue-600"><?= number_format($product['prix'], 2, ',', ' ') ?> €</span>
                                <span class="px-2 py-1 text-xs text-white rounded <?= $product['quantite'] > 0 ? 'bg-green-600' : 'bg-red-600' ?>">
                                    <?= $product['quantite'] > 0 ? 'En stock' : 'Rupture de stock' ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-4 border-t">
                            <div class="flex flex-col gap-2">
                                <a href="product.php?id=<?= $product['id'] ?>" class="block text-center border border-blue-600 text-blue-600 hover:bg-blue-50 px-4 py-2 rounded">Voir détails</a>
                                
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach;
        endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>