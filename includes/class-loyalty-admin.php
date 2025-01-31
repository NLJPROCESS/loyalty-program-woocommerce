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

            <!-- Formulaire pour configurer les points -->
            <h2>Configuration des points</h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('loyalty_program_options_group');
                do_settings_sections('loyalty-program');
                submit_button();
                ?>
            </form>

            <!-- Formulaire pour ajouter une récompense -->
            <h2>Ajouter une récompense</h2>
            <form id="loyalty-add-reward-form">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="loyalty-reward-name">Nom de la récompense</label></th>
                        <td><input type="text" id="loyalty-reward-name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="loyalty-reward-type">Type de récompense</label></th>
                        <td>
                            <select id="loyalty-reward-type" required>
                                <option value="percentage">Pourcentage</option>
                                <option value="fixed">Montant fixe</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="loyalty-reward-value">Valeur</label></th>
                        <td><input type="number" id="loyalty-reward-value" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="loyalty-points-required">Points requis</label></th>
                        <td><input type="number" id="loyalty-points-required" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="loyalty-min-amount">Montant minimum</label></th>
                        <td><input type="number" id="loyalty-min-amount" class="regular-text" required></td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary loyalty-add-reward-button">Ajouter une récompense</button>
                </p>
            </form>

            <!-- Affichage des récompenses existantes -->
            <h2>Récompenses existantes</h2>
            <div id="loyalty-rewards-list">
                <?php $this->display_rewards_list(); ?>
            </div>
        </div>
        <?php
    }

    // Enregistrer les paramètres
    public function register_settings() {
        // Section principale pour les points
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

    // Afficher la liste des récompenses existantes
    public function display_rewards_list() {
        $rewards = get_posts(array(
            'post_type' => 'loyalty_reward',
            'numberposts' => -1,
        ));

        if (empty($rewards)) {
            echo '<p>Aucune récompense disponible pour le moment.</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Nom</th><th>Type</th><th>Valeur</th><th>Points requis</th><th>Montant minimum</th><th>Actions</th></tr></thead>';
            echo '<tbody>';

            foreach ($rewards as $reward) {
                $reward_type = get_post_meta($reward->ID, '_reward_type', true);
                $reward_value = get_post_meta($reward->ID, '_reward_value', true);
                $points_required = get_post_meta($reward->ID, '_points_required', true);
                $min_amount = get_post_meta($reward->ID, '_min_amount', true);

                echo '<tr>';
                echo '<td>' . esc_html($reward->post_title) . '</td>';
                echo '<td>' . esc_html($reward_type) . '</td>';
                echo '<td>' . esc_html($reward_value) . '</td>';
                echo '<td>' . esc_html($points_required) . '</td>';
                echo '<td>' . esc_html($min_amount) . '</td>';
                echo '<td><a href="#" class="button">Modifier</a> <a href="#" class="button">Supprimer</a></td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        }
    }
}