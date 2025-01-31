<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Loyalty_Admin {
    public function __construct() {
        // Ajouter un menu dans l'admin
        add_action('admin_menu', array($this, 'add_admin_menu'));
        // Enregistrer les paramètres
        add_action('admin_init', array($this, 'register_settings'));
    }

    // Ajouter un menu dans l'admin
    public function add_admin_menu() {
        add_menu_page(
            'Loyalty Program', // Titre de la page
            'Loyalty Program', // Titre du menu
            'manage_options',  // Capacité requise
            'loyalty-program', // Slug du menu
            array($this, 'admin_page'), // Fonction de rappel
            'dashicons-star-filled', // Icône
            56 // Position dans le menu
        );
    }

    // Afficher la page d'administration
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Loyalty Program Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('loyalty_program_options_group');
                do_settings_sections('loyalty-program');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    // Enregistrer les paramètres
    public function register_settings() {
        // Section principale
        add_settings_section(
            'loyalty_program_main_section',
            'Configuration des points',
            null,
            'loyalty-program'
        );

        // Champ pour le ratio de points
        add_settings_field(
            'loyalty_points_ratio',
            'Ratio de points (points par €)',
            array($this, 'points_ratio_callback'),
            'loyalty-program',
            'loyalty_program_main_section'
        );
        register_setting('loyalty_program_options_group', 'loyalty_points_ratio', 'floatval');

        // Champ pour inclure/exclure les frais de port
        add_settings_field(
            'loyalty_include_shipping',
            'Inclure les frais de port',
            array($this, 'include_shipping_callback'),
            'loyalty-program',
            'loyalty_program_main_section'
        );
        register_setting('loyalty_program_options_group', 'loyalty_include_shipping', 'boolval');

        // Champ pour le statut de commande
        add_settings_field(
            'loyalty_order_status',
            'Statut de commande pour attribuer les points',
            array($this, 'order_status_callback'),
            'loyalty-program',
            'loyalty_program_main_section'
        );
        register_setting('loyalty_program_options_group', 'loyalty_order_status', 'sanitize_text_field');
    }

    // Callback pour le ratio de points
    public function points_ratio_callback() {
        $ratio = get_option('loyalty_points_ratio', 1);
        echo '<input type="number" step="0.1" name="loyalty_points_ratio" value="' . esc_attr($ratio) . '" />';
    }

    // Callback pour inclure/exclure les frais de port
    public function include_shipping_callback() {
        $include_shipping = get_option('loyalty_include_shipping', false);
        echo '<input type="checkbox" name="loyalty_include_shipping" ' . checked($include_shipping, true, false) . ' />';
    }

    // Callback pour le statut de commande
    public function order_status_callback() {
        $status = get_option('loyalty_order_status', 'wc-completed');
        $order_statuses = wc_get_order_statuses();
        echo '<select name="loyalty_order_status">';
        foreach ($order_statuses as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($status, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }
}