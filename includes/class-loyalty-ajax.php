<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Loyalty_Ajax {
    public function __construct() {
        // Ajouter les actions AJAX pour l'ajout de récompenses
        add_action('wp_ajax_loyalty_add_reward', array($this, 'add_reward'));
        add_action('wp_ajax_nopriv_loyalty_add_reward', array($this, 'add_reward'));

        // Ajouter les actions AJAX pour l'échange de points
        add_action('wp_ajax_loyalty_redeem_reward', array($this, 'redeem_reward'));
        add_action('wp_ajax_nopriv_loyalty_redeem_reward', array($this, 'redeem_reward'));
    }

    // Gérer l'ajout de récompenses
    public function add_reward() {
        // Vérifier le nonce pour la sécurité
        check_ajax_referer('loyalty_program_nonce', 'security');

        // Récupérer les données du formulaire
        $reward_name = sanitize_text_field($_POST['reward_name']);
        $reward_type = sanitize_text_field($_POST['reward_type']);
        $reward_value = floatval($_POST['reward_value']);
        $points_required = intval($_POST['points_required']);
        $min_amount = floatval($_POST['min_amount']);

        // Enregistrer la récompense dans la base de données
        $reward_id = wp_insert_post(array(
            'post_title' => $reward_name,
            'post_type' => 'loyalty_reward',
            'post_status' => 'publish',
        ));

        if ($reward_id) {
            // Ajouter les métadonnées de la récompense
            update_post_meta($reward_id, '_reward_type', $reward_type);
            update_post_meta($reward_id, '_reward_value', $reward_value);
            update_post_meta($reward_id, '_points_required', $points_required);
            update_post_meta($reward_id, '_min_amount', $min_amount);

            // Réponse JSON en cas de succès
            wp_send_json_success('Récompense ajoutée avec succès.');
        } else {
            // Réponse JSON en cas d'erreur
            wp_send_json_error('Erreur lors de l\'ajout de la récompense.');
        }
    }

    // Gérer l'échange de points pour une récompense
    public function redeem_reward() {
        // Vérifier le nonce pour la sécurité
        check_ajax_referer('loyalty_program_nonce', 'security');

        // Récupérer les données
        $reward_id = intval($_POST['reward_id']);
        $user_id = get_current_user_id();

        // Récupérer les points requis et les points de l'utilisateur
        $points_required = get_post_meta($reward_id, '_points_required', true);
        $user_points = get_user_meta($user_id, 'loyalty_points', true);

        // Vérifier si l'utilisateur a suffisamment de points
        if ($user_points >= $points_required) {
            // Retirer les points de l'utilisateur
            update_user_meta($user_id, 'loyalty_points', $user_points - $points_required);

            // Générer un code promo (exemple simplifié)
            $coupon_code = 'LOYALTY-' . strtoupper(wp_generate_password(8, false));

            // Réponse JSON en cas de succès
            wp_send_json_success('Récompense échangée avec succès. Code promo : ' . $coupon_code);
        } else {
            // Réponse JSON en cas d'erreur
            wp_send_json_error('Points insuffisants.');
        }
    }
}

// Initialiser la classe AJAX
new Loyalty_Ajax();