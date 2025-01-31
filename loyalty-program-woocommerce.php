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
require_once plugin_dir_path(__FILE__) . 'includes/class-loyalty-ajax.php';

// Initialiser les classes principales
function loyalty_program_init() {
    if (class_exists('WooCommerce')) {
        new Loyalty_Admin();
        new Loyalty_Points();
        new Loyalty_Rewards();
        new Loyalty_Frontend();
        new Loyalty_Ajax();
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

// Charger les scripts et styles
function loyalty_program_enqueue_scripts($hook) {
    // Charger le script uniquement sur la page d'administration de l'extension
    if ($hook === 'toplevel_page_loyalty-program') {
        wp_enqueue_script(
            'loyalty-program-inject-script', // Handle unique
            plugin_dir_url(__FILE__) . 'assets/js/inject-script.js', // URL du fichier JS
            array('jquery'), // Dépendances
            '1.0.0', // Version
            true // Charger dans le footer
        );

        // Localiser le script pour passer des variables PHP à JavaScript
        wp_localize_script(
            'loyalty-program-inject-script', // Handle du script
            'loyaltyProgramAjax', // Nom de l'objet JavaScript
            array(
                'ajax_url' => admin_url('admin-ajax.php'), // URL pour les requêtes AJAX
                'nonce' => wp_create_nonce('loyalty_program_nonce'), // Nonce pour la sécurité
            )
        );
    }
}
add_action('admin_enqueue_scripts', 'loyalty_program_enqueue_scripts');