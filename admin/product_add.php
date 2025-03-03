<?php
// Chemin vers les fichiers de base
$base_path = '../';
include $base_path . 'includes/header.php';

// Vérifier les droits d'administration
require_admin($base_path . 'index.php');
// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Initialisation des variables
$nom = $description = $prix = $marque = $categorie = $caracteristiques = '';
$quantite = 0;
$errors = [];

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
    $image_path = '';
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
            $image_path = 'assets/images/products/' . $filename;
            $target_file = $base_path . $image_path;
            
            // Déplacement du fichier
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $errors[] = "Erreur lors de l'upload de l'image";
                $image_path = '';
            }
        }
    }
    
    // Ajout du produit en base de données
    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            
            // Insertion du produit
            $stmt = $conn->prepare("INSERT INTO items (nom, description, prix, image, marque, categorie, caracteristiques) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $description, $prix, $image_path, $marque, $categorie, $caracteristiques]);
            
            // Récupération de l'ID du produit
            $product_id = $conn->lastInsertId();
            
            // Ajout du stock
            $stmt = $conn->prepare("INSERT INTO stock (id_item, quantite) VALUES (?, ?)");
            $stmt->execute([$product_id, $quantite]);
            
            $conn->commit();
            
            $_SESSION['flash_message'] = "Le produit a été ajouté avec succès.";
            header("Location: products.php");
            exit;
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = "Erreur lors de l'ajout du produit: " . $e->getMessage();
            
            // Supprimer l'image en cas d'erreur
            if (!empty($image_path) && file_exists($base_path . $image_path)) {
                unlink($base_path . $image_path);
            }
        }
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Ajouter un produit</h1>
        <a href="products.php" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded">
            <i class="fas fa-arrow-left mr-2"></i> Retour
        </a>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc pl-5">
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
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="nom" name="nom" value="<?= $nom ?>" required>
                    </div>
                    
                    <div>
                        <label for="marque" class="block text-sm font-medium text-gray-700 mb-1">Marque</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="marque" name="marque" value="<?= $marque ?>">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="prix" class="block text-sm font-medium text-gray-700 mb-1">Prix (€) *</label>
                        <input type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="prix" name="prix" value="<?= $prix ?>" required>
                    </div>
                    
                    <div>
                        <label for="quantite" class="block text-sm font-medium text-gray-700 mb-1">Quantité en stock *</label>
                        <input type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="quantite" name="quantite" value="<?= $quantite ?>" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="categorie" class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="categorie" name="categorie" value="<?= $categorie ?>">
                </div>
                
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="description" name="description" rows="4" required><?= $description ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="caracteristiques" class="block text-sm font-medium text-gray-700 mb-1">Caractéristiques</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="caracteristiques" name="caracteristiques" rows="4"><?= $caracteristiques ?></textarea>
                    <p class="mt-1 text-sm text-gray-500">Entrez les caractéristiques principales du produit (matériaux, dimensions, etc.)</p>
                </div>
                
                <div class="mb-6">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image du produit</label>
                    <input type="file" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="image" name="image">
                    <p class="mt-1 text-sm text-gray-500">Formats acceptés: JPEG, PNG, GIF. Taille recommandée: 800x600px</p>
                </div>
                
                <div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Ajouter le produit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>