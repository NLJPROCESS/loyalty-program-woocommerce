<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Loyalty_Frontend {
    public function __construct() {
        // Ajouter l'affichage des points dans "Mon compte"
        add_action('woocommerce_account_dashboard', array($this, 'display_loyalty_points'));
        // Ajouter l'affichage des récompenses dans "Mon compte"
        add_action('woocommerce_account_dashboard', array($this, 'display_loyalty_rewards'));
        // Ajouter un shortcode pour afficher les points
        add_shortcode('loyalty_points', array($this, 'loyalty_points_shortcode'));
    }

    // Afficher les points dans "Mon compte"
    public function display_loyalty_points() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return; // Pas d'utilisateur connecté
        }

        $points = get_user_meta($user_id, 'loyalty_points', true);
        $points = $points ? (float) $points : 0;

        echo '<div class="loyalty-points">';
        echo '<h2>Vos points de fidélité</h2>';
        echo '<p>Vous avez <strong>' . esc_html($points) . '</strong> points.</p>';
        echo '</div>';

        // Afficher l'historique des points
        $history = get_user_meta($user_id, 'loyalty_points_history', true);
        if ($history) {
            echo '<div class="loyalty-points-history">';
            echo '<h3>Historique des points</h3>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Date</th><th>Points</th><th>Action</th><th>Commande</th></tr></thead>';
            echo '<tbody>';

            foreach ($history as $entry) {
                echo '<tr>';
                echo '<td>' . esc_html($entry['date']) . '</td>';
                echo '<td>' . esc_html($entry['points']) . '</td>';
                echo '<td>' . esc_html($entry['action']) . '</td>';
                echo '<td>' . ($entry['order_id'] ? '<a href="' . esc_url(wc_get_order($entry['order_id'])->get_view_order_url()) . '">Commande #' . esc_html($entry['order_id']) . '</a>' : 'N/A') . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
            echo '</div>';
        }
    }

    // Afficher les récompenses disponibles dans "Mon compte"
    public function display_loyalty_rewards() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return; // Pas d'utilisateur connecté
        }

        $points = get_user_meta($user_id, 'loyalty_points', true);
        $points = $points ? (float) $points : 0;

        $rewards = get_posts(array(
            'post_type' => 'loyalty_reward',
            'numberposts' => -1,
        ));

        echo '<div class="loyalty-rewards">';
        echo '<h2>Récompenses disponibles</h2>';

        if (empty($rewards)) {
            echo '<p>Aucune récompense disponible pour le moment.</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Récompense</th><th>Points requis</th><th>Actions</th></tr></thead>';
            echo '<tbody>';

            foreach ($rewards as $reward) {
                $points_required = get_post_meta($reward->ID, '_points_required', true);
                $can_claim = $points >= $points_required;

                echo '<tr>';
                echo '<td>' . esc_html($reward->post_title) . '</td>';
                echo '<td>' . esc_html($points_required) . '</td>';
                echo '<td>';
                if ($can_claim) {
                    echo '<a href="#" class="button loyalty-redeem-button" data-reward-id="' . esc_attr($reward->ID) . '">Échanger</a>';
                } else {
                    echo '<em>Points insuffisants</em>';
                }
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        }

        echo '</div>';
    }

    // Shortcode pour afficher les points
    public function loyalty_points_shortcode() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return ''; // Pas d'utilisateur connecté
        }

        $points = get_user_meta($user_id, 'loyalty_points', true);
        $points = $points ? (float) $points : 0;

        return '<div class="loyalty-points-shortcode">Vous avez <strong>' . esc_html($points) . '</strong> points.</div>';
    }
}