<?php
// Démarrer la session et inclure les fichiers nécessaires avant tout output HTML
session_start();
// Détecter si nous sommes dans un sous-dossier
$config_path = file_exists('config/database.php') ? 'config/database.php' : '../config/database.php';
require_once $config_path;
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
require_login();

// Vérifier si le panier est vide
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Initialisation des variables
$adresse = $ville = $code_postal = '';
$errors = [];

// Calculer le total du panier
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $adresse = trim(htmlspecialchars($_POST['adresse']));
    $ville = trim(htmlspecialchars($_POST['ville']));
    $code_postal = trim(htmlspecialchars($_POST['code_postal']));
    
    // Validation
    if (empty($adresse)) {
        $errors[] = "L'adresse est obligatoire";
    }
    
    if (empty($ville)) {
        $errors[] = "La ville est obligatoire";
    }
    
    if (empty($code_postal)) {
        $errors[] = "Le code postal est obligatoire";
    } elseif (!preg_match('/^[0-9]{5}$/', $code_postal)) {
        $errors[] = "Le code postal doit être composé de 5 chiffres";
    }
    
    // Traitement de la commande
    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            
            // Création de la facture
            $stmt = $conn->prepare("INSERT INTO invoice (id_user, montant, adresse_facturation, ville, code_postal, statut) VALUES (?, ?, ?, ?, ?, 'en_attente')");
            $stmt->execute([$_SESSION['user_id'], $total, $adresse, $ville, $code_postal]);
            
            $invoice_id = $conn->lastInsertId();
            
            // Enregistrement des articles de la commande
            foreach ($_SESSION['cart'] as $item) {
                // Vérifier le stock avant de finaliser
                $stmt = $conn->prepare("SELECT quantite FROM stock WHERE id_item = ?");
                $stmt->execute([$item['id']]);
                $stock = $stmt->fetchColumn();
                
                if ($stock < $item['quantity']) {
                    throw new Exception("Stock insuffisant pour l'article " . $item['name']);
                }
                
                // Enregistrement de la commande (sans id_invoice)
                $stmt = $conn->prepare("INSERT INTO orders (id_user, id_item, quantite) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $item['id'], $item['quantity']]);
                
                // Mise à jour du stock
                $stmt = $conn->prepare("UPDATE stock SET quantite = quantite - ? WHERE id_item = ?");
                $stmt->execute([$item['quantity'], $item['id']]);
            }
            
            $conn->commit();
            
            // Vider le panier
            $_SESSION['cart'] = [];
            
            // Redirection vers une page de confirmation
            $_SESSION['invoice_id'] = $invoice_id;
            header("Location: confirmation.php");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = "Erreur lors de la commande: " . $e->getMessage();
        }
    }
}

// Inclure le header après le traitement du formulaire
include 'includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Finaliser votre commande</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="flex flex-col md:flex-row gap-6">
        <div class="w-full md:w-2/3">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h5 class="font-medium text-gray-700">Informations de livraison</h5>
                </div>
                <div class="p-4">
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="adresse" class="block text-sm font-medium text-gray-700 mb-1">Adresse *</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="adresse" name="adresse" value="<?= $adresse ?>" required>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label for="ville" class="block text-sm font-medium text-gray-700 mb-1">Ville *</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="ville" name="ville" value="<?= $ville ?>" required>
                            </div>
                            
                            <div>
                                <label for="code_postal" class="block text-sm font-medium text-gray-700 mb-1">Code postal *</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="code_postal" name="code_postal" value="<?= $code_postal ?>" required>
                            </div>
                        </div>
                        
                        <h5 class="font-medium text-gray-700 mt-6 mb-3">Mode de paiement</h5>
                        <div class="mb-4">
                            <div class="flex items-center mb-2">
                                <input class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" type="radio" name="paiement" id="carte" value="carte" checked>
                                <label class="ml-2 text-sm text-gray-700" for="carte">
                                    Carte bancaire
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" type="radio" name="paiement" id="paypal" value="paypal">
                                <label class="ml-2 text-sm text-gray-700" for="paypal">
                                    PayPal
                                </label>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-md transition duration-200">Confirmer la commande</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-lg shadow-md">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h5 class="font-medium text-gray-700">Récapitulatif de commande</h5>
                </div>
                <div class="p-4">
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <li class="py-3 flex justify-between items-center">
                                <div>
                                    <span class="font-medium"><?= $item['name'] ?></span>
                                    <br>
                                    <span class="text-sm text-gray-500">Quantité: <?= $item['quantity'] ?></span>
                                </div>
                                <span><?= number_format($item['price'] * $item['quantity'], 2, ',', ' ') ?> €</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="flex justify-between items-center mt-4 p-3 bg-gray-50 rounded">
                        <span class="font-bold">Total</span>
                        <span class="font-bold text-blue-600"><?= number_format($total, 2, ',', ' ') ?> €</span>
                    </div>
                    
                    <a href="cart.php" class="mt-4 block text-center py-2 px-4 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-arrow-left"></i> Retour au panier
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>