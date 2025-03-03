<?php
// Démarrer la session et inclure les fichiers nécessaires avant tout output HTML
session_start();
// Détecter si nous sommes dans un sous-dossier
$config_path = file_exists('config/database.php') ? 'config/database.php' : '../config/database.php';
require_once $config_path;
require_once 'includes/functions.php';

// Initialisation des variables
$nom = $prenom = $email = '';
$errors = [];
$admin_key = "admin123"; // Clé secrète pour créer un compte admin

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $nom = trim(htmlspecialchars($_POST['nom']));
    $prenom = trim(htmlspecialchars($_POST['prenom']));
    $email = trim(htmlspecialchars($_POST['email']));
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirmation = $_POST['confirmation'];
    $admin_code = isset($_POST['admin_code']) ? trim($_POST['admin_code']) : '';
    
    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire";
    }
    
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire";
    }
    
    if (empty($email)) {
        $errors[] = "L'email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }
    
    if (empty($mot_de_passe)) {
        $errors[] = "Le mot de passe est obligatoire";
    } 
    
    if ($mot_de_passe !== $confirmation) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }
    
    // Vérifier si l'email existe déjà
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $errors[] = "Cet email est déjà utilisé";
        }
    }
    
    // Inscription de l'utilisateur
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            
            // Déterminer le rôle (admin ou client)
            $role = 'client';
            if (!empty($admin_code) && $admin_code === $admin_key) {
                $role = 'admin';
            }
            
            $stmt = $conn->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $hashed_password, $role]);
            
            // Redirection vers la page de connexion
            $_SESSION['flash_message'] = "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.";
            if ($role === 'admin') {
                $_SESSION['flash_message'] .= " Vous avez été enregistré en tant qu'administrateur.";
            }
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'inscription: " . $e->getMessage();
        }
    }
}

// Inclure le header après le traitement du formulaire
include 'includes/header.php';
?>

<div class="flex justify-center py-8">
    <div class="w-full md:w-1/2 max-w-lg">
        <div class="border rounded shadow">
            <div class="bg-blue-600 text-white p-4">
                <h4 class="text-lg font-semibold m-0">Créer un compte</h4>
            </div>
            <div class="p-6">
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc pl-5 m-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        <input type="text" class="w-full px-3 py-2 border rounded" id="nom" name="nom" value="<?= $nom ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                        <input type="text" class="w-full px-3 py-2 border rounded" id="prenom" name="prenom" value="<?= $prenom ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" class="w-full px-3 py-2 border rounded" id="email" name="email" value="<?= $email ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="mot_de_passe" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                        <input type="password" class="w-full px-3 py-2 border rounded" id="mot_de_passe" name="mot_de_passe" required>
                        <p class="text-xs text-gray-500 mt-1">Le mot de passe doit contenir au moins 8 caractères.</p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                        <input type="password" class="w-full px-3 py-2 border rounded" id="confirmation" name="confirmation" required>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="show_admin_code" class="mr-2" onclick="toggleAdminCode()">
                            <label for="show_admin_code" class="text-sm text-gray-700">Je souhaite créer un compte administrateur</label>
                        </div>
                    </div>
                    
                    <div id="admin_code_container" class="mb-4 hidden">
                        <label for="admin_code" class="block text-sm font-medium text-gray-700 mb-1">Code administrateur</label>
                        <input type="password" class="w-full px-3 py-2 border rounded" id="admin_code" name="admin_code">
                        <p class="text-xs text-gray-500 mt-1">Entrez le code secret pour créer un compte administrateur.</p>
                    </div>
                    
                    <div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">S'inscrire</button>
                    </div>
                </form>
            </div>
            <div class="border-t p-4 text-center">
                <p class="m-0">Déjà un compte ? <a href="login.php" class="text-blue-600 hover:underline">Se connecter</a></p>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAdminCode() {
    const container = document.getElementById('admin_code_container');
    const checkbox = document.getElementById('show_admin_code');
    
    if (checkbox.checked) {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
        document.getElementById('admin_code').value = '';
    }
}
</script>

<?php include 'includes/footer.php'; ?>