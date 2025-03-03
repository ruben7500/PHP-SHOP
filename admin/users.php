<?php
// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Chemin vers les fichiers de base
$base_path = '../';

// Détecter le chemin correct pour la configuration
$config_path = file_exists($base_path . 'config/database.php') ? $base_path . 'config/database.php' : '../../config/database.php';
require_once $config_path;
require_once $base_path . 'includes/functions.php';

// Vérifier les droits d'administration avant toute action
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash_message'] = "Vous devez être administrateur pour accéder à cette page.";
    header("Location: " . $base_path . "login.php");
    exit;
}

// Gestion de la suppression d'un utilisateur
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    // Empêcher la suppression de son propre compte
    if ($user_id === $_SESSION['user_id']) {
        $_SESSION['flash_message'] = "Vous ne pouvez pas supprimer votre propre compte.";
    } else {
        try {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            
            $_SESSION['flash_message'] = "L'utilisateur a été supprimé avec succès.";
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Erreur lors de la suppression: " . $e->getMessage();
        }
    }
    
    header("Location: users.php");
    exit;
}

// Gestion du changement de rôle
if (isset($_GET['action']) && $_GET['action'] === 'role' && isset($_GET['id']) && isset($_GET['role'])) {
    $user_id = intval($_GET['id']);
    $role = $_GET['role'] === 'admin' ? 'admin' : 'client';
    
    // Empêcher la modification de son propre rôle
    if ($user_id === $_SESSION['user_id']) {
        $_SESSION['flash_message'] = "Vous ne pouvez pas modifier votre propre rôle.";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$role, $user_id]);
            
            $_SESSION['flash_message'] = "Le rôle de l'utilisateur a été modifié avec succès.";
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Erreur lors de la modification: " . $e->getMessage();
        }
    }
    
    header("Location: users.php");
    exit;
}

// Inclure le header après le traitement des redirections
include $base_path . 'includes/header.php';

// Récupération des utilisateurs
$stmt = $conn->query("SELECT id, nom, prenom, email, role, date_creation FROM users ORDER BY date_creation DESC");
$users = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Gestion des utilisateurs</h1>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['flash_message'] ?>
            <?php unset($_SESSION['flash_message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white shadow rounded overflow-hidden">
        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date d'inscription</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($users) === 0): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Aucun utilisateur trouvé</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap"><?= $user['prenom'] . ' ' . $user['nom'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= $user['email'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                            <?= $user['role'] === 'admin' ? 'Administrateur' : 'Client' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($user['date_creation'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap space-y-1">
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <a href="users.php?action=role&id=<?= $user['id'] ?>&role=client" 
                                                   class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-yellow-600 hover:bg-yellow-700"
                                                   onclick="return confirm('Êtes-vous sûr de vouloir retirer les droits d\'administrateur ?')">
                                                    <i class="fas fa-user mr-1"></i> Passer en client
                                                </a>
                                            <?php else: ?>
                                                <a href="users.php?action=role&id=<?= $user['id'] ?>&role=admin" 
                                                   class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700"
                                                   onclick="return confirm('Êtes-vous sûr de vouloir donner les droits d\'administrateur ?')">
                                                    <i class="fas fa-user-shield mr-1"></i> Passer en admin
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="users.php?action=delete&id=<?= $user['id'] ?>" 
                                               class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                                <i class="fas fa-trash mr-1"></i> Supprimer
                                            </a>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500">Vous-même</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>