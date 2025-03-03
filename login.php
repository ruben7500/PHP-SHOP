<?php
// Démarrer la session et inclure les fichiers nécessaires avant tout output HTML
session_start();
// Détecter si nous sommes dans un sous-dossier
$config_path = file_exists('config/database.php') ? 'config/database.php' : '../config/database.php';
require_once $config_path;
require_once 'includes/functions.php';

// Initialisation des variables
$email = '';
$errors = [];

// Vérifier si un message flash existe
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $email = trim(htmlspecialchars($_POST['email']));
    $mot_de_passe = $_POST['mot_de_passe'];
    
    // Validation
    if (empty($email)) {
        $errors[] = "L'email est obligatoire";
    }
    
    if (empty($mot_de_passe)) {
        $errors[] = "Le mot de passe est obligatoire";
    }
    
    // Vérification des identifiants
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT id, nom, prenom, email, mot_de_passe, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Ajouter un log de débogage
                error_log("Utilisateur connecté: " . $user['email'] . ", Rôle: " . $user['role']);
                
                // Redirection selon le rôle
                if ($user['role'] === 'admin') {
                    // Vérifier si le dossier admin existe
                    if (!file_exists('admin/index.php')) {
                        error_log("Le fichier admin/index.php n'existe pas");
                        $errors[] = "Erreur de configuration: le fichier admin/index.php n'existe pas";
                    } else {
                        // S'assurer que la session est enregistrée avant la redirection
                        session_write_close();
                        header("Location: admin/index.php");
                        exit;
                    }
                } else {
                    header("Location: index.php");
                    exit;
                }
            } else {
                $errors[] = "Email ou mot de passe incorrect";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la connexion: " . $e->getMessage();
        }
    }
}

// Inclure le header après le traitement du formulaire
include 'includes/header.php';
?>

<div class="max-w-screen-xl mx-auto px-4 py-16">
    <div class="flex justify-center">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-600 text-white px-6 py-4">
                    <h4 class="text-xl font-semibold">Connexion</h4>
                </div>
                <div class="p-6">
                    <?php if (isset($flash_message)): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <?= $flash_message ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc pl-5">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                            <input type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" value="<?= $email ?>" required>
                        </div>
                        
                        <div class="mb-6">
                            <label for="mot_de_passe" class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
                            <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="mot_de_passe" name="mot_de_passe" required>
                        </div>
                        
                        <div class="flex items-center justify-center">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">Se connecter</button>
                        </div>
                    </form>
                </div>
                <div class="bg-gray-50 px-6 py-4 text-center">
                    <p class="text-sm text-gray-700">Pas encore de compte ? <a href="register.php" class="text-blue-600 hover:text-blue-800">S'inscrire</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>