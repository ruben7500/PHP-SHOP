<?php
/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur est administrateur
 * @return bool
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirige les utilisateurs non connectés
 * @param string $redirect_url URL de redirection
 */
function require_login($redirect_url = 'login.php') {
    if (!is_logged_in()) {
        $_SESSION['flash_message'] = "Vous devez être connecté pour accéder à cette page.";
        header("Location: $redirect_url");
        exit;
    }
}

/**
 * Redirige les utilisateurs non administrateurs
 * @param string $redirect_url URL de redirection
 */
function require_admin($redirect_url = 'index.php') {
    if (!is_admin()) {
        $_SESSION['flash_message'] = "Vous n'avez pas les droits pour accéder à cette page.";
        header("Location: $redirect_url");
        exit;
    }
}
?>