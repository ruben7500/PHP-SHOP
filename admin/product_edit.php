<?php
// Démarrer la session et inclure les fichiers nécessaires avant tout output HTML
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Chemin vers les fichiers de base
$base_path = '../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'includes/functions.php';

// Vérifier les droits d'administration
require_admin($base_path . 'index.php');

// Vérifier si l'ID du produit est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = intval($_GET['id']);
$errors = [];

// Récupérer les informations du produit
try {
    $stmt = $conn->prepare("SELECT i.*, s.quantite FROM items i JOIN stock s ON i.id = s.id_item WHERE i.id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['flash_message'] = "Le produit demandé n'existe pas.";
        header("Location: products.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['flash_message'] = "Erreur lors de la récupération du produit: " . $e->getMessage();
    header("Location: products.php");
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $nom = trim(htmlspecialchars($_POST['nom']));
    $description = trim(htmlspecialchars($_POST['description']));
    $prix = filter_var($_POST['prix'], FILTER_VALIDATE_FLOAT);
    $marque = trim(htmlspecialchars($_POST['marque']));
    $categorie = trim(htmlspecialchars($_POST['categorie']));
    $caracteristiques = trim(htmlspecialchars($_POST['caracteristiques']));
    $quantite = filter_var($_POST['quantite'], FILTER_VALIDATE_INT);
    
    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom du produit est obligatoire";
    }
    
    if (empty($description)) {
        $errors[] = "La description est obligatoire";
    }
    
    if (empty($prix) || $prix <= 0) {
        $errors[] = "Le prix doit être un nombre positif";
    }
    
    if (!is_int($quantite) || $quantite < 0) {
        $errors[] = "La quantité doit être un nombre entier positif";
    }
    
    // Traitement de l'image
    $image_path = $product['image']; // Conserver l'image existante par défaut
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Seuls les formats JPEG, PNG et GIF sont acceptés";
        } else {
            // Création du dossier s'il n'existe pas
            $upload_dir = $base_path . 'assets/images/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Génération d'un nom de fichier unique
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $new_image_path = 'assets/images/products/' . $filename;
            $target_file = $base_path . $new_image_path;
            
            // Déplacement du fichier
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Supprimer l'ancienne image si elle existe
                if (!empty($image_path) && file_exists($base_path . $image_path)) {
                    unlink($base_path . $image_path);
                }
                $image_path = $new_image_path;
            } else {
                $errors[] = "Erreur lors de l'upload de l'image";
            }
        }
    }
    
    // Mise à jour du produit en base de données
    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            
            // Mise à jour du produit
            $stmt = $conn->prepare("UPDATE items SET nom = ?, description = ?, prix = ?, image = ?, marque = ?, categorie = ?, caracteristiques = ? WHERE id = ?");
            $stmt->execute([$nom, $description, $prix, $image_path, $marque, $categorie, $caracteristiques, $product_id]);
            
            // Mise à jour du stock
            $stmt = $conn->prepare("UPDATE stock SET quantite = ? WHERE id_item = ?");
            $stmt->execute([$quantite, $product_id]);
            
            $conn->commit();
            
            $_SESSION['flash_message'] = "Le produit a été mis à jour avec succès.";
            header("Location: products.php");
            exit;
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = "Erreur lors de la mise à jour du produit: " . $e->getMessage();
        }
    }
}

// Inclure le header après le traitement du formulaire
include $base_path . 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Modifier un produit</h1>
        <a href="products.php" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded">
            <i class="fas fa-arrow-left mr-2"></i> Retour
        </a>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc pl-5 mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="bg-white shadow rounded overflow-hidden">
        <div class="p-6">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom du produit *</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="nom" name="nom" value="<?= $product['nom'] ?>" required>
                    </div>
                    
                    <div>
                        <label for="marque" class="block text-sm font-medium text-gray-700 mb-1">Marque</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="marque" name="marque" value="<?= $product['marque'] ?>">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="prix" class="block text-sm font-medium text-gray-700 mb-1">Prix (€) *</label>
                        <input type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="prix" name="prix" value="<?= $product['prix'] ?>" required>
                    </div>
                    
                    <div>
                        <label for="quantite" class="block text-sm font-medium text-gray-700 mb-1">Quantité en stock *</label>
                        <input type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="quantite" name="quantite" value="<?= $product['quantite'] ?>" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="categorie" class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="categorie" name="categorie" value="<?= $product['categorie'] ?>">
                </div>
                
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="description" name="description" rows="4" required><?= $product['description'] ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="caracteristiques" class="block text-sm font-medium text-gray-700 mb-1">Caractéristiques</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="caracteristiques" name="caracteristiques" rows="4"><?= $product['caracteristiques'] ?></textarea>
                    <p class="text-sm text-gray-500 mt-1">Saisissez les caractéristiques techniques du produit, une par ligne.</p>
                </div>
                
                <div class="mb-6">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image du produit</label>
                    <?php if (!empty($product['image'])): ?>
                        <div class="mb-2">
                            <img src="<?= $base_path . $product['image'] ?>" alt="<?= $product['nom'] ?>" class="w-32 h-32 object-cover border rounded">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="image" name="image">
                    <p class="text-sm text-gray-500 mt-1">Formats acceptés: JPEG, PNG, GIF. Laissez vide pour conserver l'image actuelle.</p>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">
                        <i class="fas fa-save mr-2"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>