<?php
// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Détecter si nous sommes dans un sous-dossier
$config_path = file_exists('config/database.php') ? 'config/database.php' : '../config/database.php';
require_once $config_path;
require_once __DIR__ . '/functions.php';

// Déterminer le chemin de base pour les liens
$root_path = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    $root_path = '../';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MontresLuxe - Votre Boutique de Montres</title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= $root_path ?>assets/css/style.css">
</head>
<body>
    <header class="bg-white shadow-sm fixed w-full z-10">
        <div class="px-4 py-3">
            <div class="flex justify-between items-center">
                <a href="<?= $root_path ?>index.php" class="text-2xl font-bold text-gray-900">Montres<span class="text-blue-600">Luxe</span></a>
                
                <nav class="hidden md:flex space-x-8">
                    <a href="<?= $root_path ?>index.php" class="font-medium text-gray-900 hover:text-blue-600 transition">Accueil</a>
                    <a href="<?= $root_path ?>catalog.php" class="font-medium text-gray-600 hover:text-blue-600 transition">Collection</a>
                    <a href="<?= $root_path ?>about.php" class="font-medium text-gray-600 hover:text-blue-600 transition">Contact</a>
                </nav>
                
                <div class="flex items-center space-x-4">
                    <a href="#" class="text-gray-600 hover:text-blue-600 transition">
                        <i class="fas fa-search"></i>
                    </a>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="relative" id="userDropdown">
                            <button onclick="toggleUserMenu()" class="flex items-center space-x-1 text-gray-600 hover:text-blue-600 transition focus:outline-none">
                                <i class="fas fa-user"></i>
                                <span class="hidden md:inline-block text-sm font-medium">
                                    <?= isset($_SESSION['user_prenom']) ? htmlspecialchars($_SESSION['user_prenom']) : 'Mon compte' ?>
                                </span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20 hidden">
                                <div class="px-4 py-2 border-b">
                                    <p class="text-sm font-medium text-gray-900">
                                        <?= isset($_SESSION['user_prenom']) && isset($_SESSION['user_nom']) ? 
                                            htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) : 
                                            'Utilisateur' ?>
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">
                                        <?= isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '' ?>
                                    </p>
                                </div>
                                <a href="<?= $root_path ?>profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user-circle mr-2"></i>Mon profil
                                </a>
                                <a href="<?= $root_path ?>orders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-shopping-basket mr-2"></i>Mes commandes
                                </a>
                                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <a href="<?= $root_path ?>admin/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-cog mr-2"></i>Administration
                                    </a>
                                <?php endif; ?>
                                <div class="border-t"></div>
                                <button onclick="openLogoutModal()" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center space-x-2">
                            <a href="<?= $root_path ?>login.php" class="text-gray-600 hover:text-blue-600 transition flex items-center">
                                <i class="fas fa-sign-in-alt mr-1"></i>
                                <span class="hidden md:inline-block text-sm">Connexion</span>
                            </a>
                            <span class="hidden md:inline-block text-gray-300">|</span>
                            <a href="<?= $root_path ?>register.php" class="hidden md:inline-block text-sm text-gray-600 hover:text-blue-600 transition">
                                Inscription
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <a href="<?= $root_path ?>cart.php" class="text-gray-600 hover:text-blue-600 transition relative">
                        <i class="fas fa-shopping-bag"></i>
                        <?php 
                        $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                        ?>
                        <span class="absolute -top-2 -right-2 bg-blue-600 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">
                            <?= $cart_count ?>
                        </span>
                    </a>
                    
                    <button class="md:hidden text-gray-600 focus:outline-none">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>
    <!-- Ajout d'un espace pour compenser le header fixe -->
    <div class="pt-16"></div>
    
    <!-- Modal de déconnexion -->
    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="fas fa-sign-out-alt text-red-600 text-xl"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-center mb-4">Confirmation de déconnexion</h3>
                <p class="text-gray-600 text-center mb-6">Êtes-vous sûr de vouloir vous déconnecter de votre compte ?</p>
                <div class="flex justify-center space-x-4">
                    <button onclick="closeLogoutModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded text-gray-800 font-medium transition">
                        Annuler
                    </button>
                    <a href="<?= $root_path ?>logout.php" class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded text-white font-medium transition">
                        Se déconnecter
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Gestion du menu utilisateur
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }
        
        // Fermer le menu si on clique ailleurs sur la page
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const menu = document.getElementById('userMenu');
            
            if (dropdown && !dropdown.contains(event.target) && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });
        
        // Gestion de la modal de déconnexion
        function openLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Empêche le défilement
        }
        
        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
            document.body.style.overflow = 'auto'; // Réactive le défilement
        }
        
        // Fermer la modal si on clique en dehors
        document.getElementById('logoutModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });
        
        // Fermer la modal avec la touche Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (!document.getElementById('logoutModal').classList.contains('hidden')) {
                    closeLogoutModal();
                }
                if (!document.getElementById('userMenu').classList.contains('hidden')) {
                    document.getElementById('userMenu').classList.add('hidden');
                }
            }
        });
    </script>
</body>
</html>
    