<?php
// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialisation du panier s'il n'existe pas
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Traitement des actions sur le panier
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // Ajouter un produit au panier
    if ($action === 'add' && isset($_GET['id'])) {
        $product_id = intval($_GET['id']);
        $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
        
        // Vérifier que le produit existe et est en stock
        require_once 'config/database.php';
        $stmt = $conn->prepare("SELECT i.*, s.quantite FROM items i JOIN stock s ON i.id = s.id_item WHERE i.id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product && $product['quantite'] > 0) {
            // Limiter la quantité au stock disponible
            $quantity = min($quantity, $product['quantite']);
            
            // Ajouter ou mettre à jour le produit dans le panier
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = [
                    'id' => $product_id,
                    'name' => $product['nom'],
                    'price' => $product['prix'],
                    'quantity' => $quantity,
                    'image' => $product['image']
                ];
            }
            
            $_SESSION['flash_message'] = "Produit ajouté au panier.";
        }
        
        // Redirection vers la page des produits ou le panier
        if (isset($_GET['redirect']) && $_GET['redirect'] === 'cart') {
            header("Location: cart.php");
        } else {
            header("Location: product.php?id=$product_id");
        }
        exit;
    }
    
    // Supprimer un produit du panier
    else if ($action === 'remove' && isset($_GET['id'])) {
        $product_id = intval($_GET['id']);
        
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            $_SESSION['flash_message'] = "Produit retiré du panier.";
        }
        
        header("Location: cart.php");
        exit;
    }
    
    // Mettre à jour la quantité d'un produit
    else if ($action === 'update' && isset($_POST['update_cart'])) {
        require_once 'config/database.php';
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            $product_id = intval($product_id);
            $quantity = intval($quantity);
            
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                // Vérifier le stock disponible
                $stmt = $conn->prepare("SELECT quantite FROM stock WHERE id_item = ?");
                $stmt->execute([$product_id]);
                $stock = $stmt->fetchColumn();
                
                // Limiter la quantité au stock disponible
                $quantity = min($quantity, $stock);
                
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                }
            }
        }
        
        $_SESSION['flash_message'] = "Panier mis à jour.";
        header("Location: cart.php");
        exit;
    }
    
    // Vider le panier
    else if ($action === 'clear') {
        $_SESSION['cart'] = [];
        $_SESSION['flash_message'] = "Le panier a été vidé.";
        header("Location: cart.php");
        exit;
    }
}

// Calculer le total du panier
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Inclure le header après le traitement des redirections
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Votre panier</h1>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['flash_message'] ?>
            <?php unset($_SESSION['flash_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            Votre panier est vide. <a href="catalog.php" class="underline">Continuer vos achats</a>
        </div>
    <?php else: ?>
        <form action="cart.php?action=update" method="POST">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix unitaire</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img src="<?= !empty($item['image']) ? $item['image'] : 'assets/images/placeholder.jpg' ?>" alt="<?= $item['name'] ?>" class="w-16 h-16 object-cover mr-4">
                                        <a href="product.php?id=<?= $item['id'] ?>" class="text-blue-600 hover:text-blue-800"><?= $item['name'] ?></a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= number_format($item['price'], 2, ',', ' ') ?> €</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="quantity[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="1" max="10" class="w-20 border rounded px-2 py-1">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= number_format($item['price'] * $item['quantity'], 2, ',', ' ') ?> €</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="cart.php?action=remove&id=<?= $item['id'] ?>" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash mr-1"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 text-right font-bold">Total</td>
                            <td class="px-6 py-4 font-bold"><?= number_format($total, 2, ',', ' ') ?> €</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="flex flex-col md:flex-row justify-between mt-6">
                <a href="catalog.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded mb-4 md:mb-0 inline-flex items-center justify-center">
                    <i class="fas fa-arrow-left mr-2"></i> Continuer les achats
                </a>
                <div class="flex flex-col md:flex-row gap-2">
                    <button type="submit" name="update_cart" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded inline-flex items-center justify-center">
                        <i class="fas fa-sync mr-2"></i> Mettre à jour
                    </button>
                    <a href="cart.php?action=clear" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded inline-flex items-center justify-center">
                        <i class="fas fa-trash mr-2"></i> Vider le panier
                    </a>
                    <a href="checkout.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded inline-flex items-center justify-center">
                        <i class="fas fa-check mr-2"></i> Commander
                    </a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>