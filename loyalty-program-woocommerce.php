<?php
/*
Plugin Name: Loyalty Program for WooCommerce
Description: A complete loyalty program for WooCommerce with points, rewards, and more.
Version: 1.0.0
Author: Votre Nom
Text Domain: loyalty-program-woocommerce
*/

// Sécurité : Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

// Charger les fichiers nécessaires
require_once plugin_dir_path(__FILE__) . 'includes/class-loyalty-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-loyalty-points.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-loyalty-rewards.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-loyalty-frontend.php';

// Initialiser les classes principales
function loyalty_program_init() {
    if (class_exists('WooCommerce')) {
        new Loyalty_Admin();
        new Loyalty_Points();
        new Loyalty_Rewards();
        new Loyalty_Frontend();
    } else {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>Loyalty Program for WooCommerce nécessite WooCommerce pour fonctionner.</p></div>';
        });
    }
}
add_action('plugins_loaded', 'loyalty_program_init');

// Activation et désactivation du plugin
register_activation_hook(__FILE__, 'loyalty_program_activate');
register_deactivation_hook(__FILE__, 'loyalty_program_deactivate');

function loyalty_program_activate() {
    // Code à exécuter lors de l'activation
}

function loyalty_program_deactivate() {
    // Code à exécuter lors de la désactivation
}