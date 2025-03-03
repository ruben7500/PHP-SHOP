<?php 
// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'ID du produit est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: catalog.php");
    exit;
}

$product_id = intval($_GET['id']);

// Récupérer les informations du produit
require_once 'config/database.php';
$stmt = $conn->prepare("SELECT i.*, s.quantite FROM items i JOIN stock s ON i.id = s.id_item WHERE i.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// Si le produit n'existe pas, redirection
if (!$product) {
    header("Location: catalog.php");
    exit;
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <nav class="flex mb-5" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="index.php" class="text-gray-700 hover:text-blue-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                    Accueil
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a href="catalog.php" class="ml-1 text-gray-700 hover:text-blue-600 md:ml-2">Catalogue</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-gray-500 md:ml-2"><?= htmlspecialchars($product['nom']) ?></span>
                </div>
            </li>
        </ol>
    </nav>
    
    <div class="flex flex-col md:flex-row -mx-4">
        <div class="md:w-1/2 px-4 mb-6 md:mb-0">
            <img src="<?= !empty($product['image']) ? htmlspecialchars($product['image']) : 'assets/images/placeholder.jpg' ?>" 
                 class="w-full h-auto object-cover rounded-lg shadow-md" 
                 alt="<?= htmlspecialchars($product['nom']) ?>">
        </div>
        <div class="md:w-1/2 px-4">
            <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($product['nom']) ?></h1>
            <p class="text-gray-600 mb-4"><?= htmlspecialchars($product['marque']) ?></p>
            
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-blue-600"><?= number_format($product['prix'], 2, ',', ' ') ?> €</h2>
                <span class="inline-block px-3 py-1 mt-2 text-sm font-semibold rounded-full <?= $product['quantite'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <?= $product['quantite'] > 0 ? 'En stock' : 'Rupture de stock' ?>
                </span>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2">Description</h3>
                <div class="text-gray-700 prose max-w-none">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>
            </div>
            
            <?php if (!empty($product['caracteristiques'])): ?>
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-2">Caractéristiques</h3>
                    <div class="text-gray-700 prose max-w-none">
                        <?= nl2br(htmlspecialchars($product['caracteristiques'])) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($product['quantite'] > 0): ?>
                <form action="cart.php" method="GET" class="mb-6">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                    <div class="mb-4">
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantité</label>
                        <select name="quantity" id="quantity" class="block w-full md:w-1/3 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <?php for($i = 1; $i <= min(10, $product['quantite']); $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"></path>
                        </svg>
                        Ajouter au panier
                    </button>
                </form>
            <?php else: ?>
                <button class="inline-flex items-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-gray-400 cursor-not-allowed" disabled>
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                    </svg>
                    Indisponible
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>