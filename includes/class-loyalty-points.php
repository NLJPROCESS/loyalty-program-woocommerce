<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Loyalty_Points {
    public function __construct() {
        // Attribuer des points lors du changement de statut de commande
        add_action('woocommerce_order_status_changed', array($this, 'handle_order_status_change'), 10, 3);
    }

    // Gérer le changement de statut de commande
    public function handle_order_status_change($order_id, $old_status, $new_status) {
        $target_status = get_option('loyalty_order_status', 'wc-completed');
        $target_status = str_replace('wc-', '', $target_status); // Enlever le préfixe "wc-"

        // Si la commande atteint le statut cible, attribuer les points
        if ($new_status === $target_status) {
            $this->add_points_for_order($order_id);
        }

        // Si la commande est annulée, échouée ou remboursée, retirer les points
        if (in_array($new_status, array('cancelled', 'failed', 'refunded'))) {
            $this->remove_points_for_order($order_id);
        }
    }

    // Ajouter des points pour une commande
    private function add_points_for_order($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        if (!$user_id) {
            return; // Pas d'utilisateur, pas de points
        }

        $ratio = get_option('loyalty_points_ratio', 1);
        $include_shipping = get_option('loyalty_include_shipping', false);

        // Calculer le montant total de la commande
        $total = $order->get_total();
        if (!$include_shipping) {
            $total -= $order->get_shipping_total();
        }

        // Calculer les points
        $points = $total * $ratio;

        // Ajouter les points à l'utilisateur
        $this->update_user_points($user_id, $points);
    }

    // Retirer des points pour une commande
    private function remove_points_for_order($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        if (!$user_id) {
            return; // Pas d'utilisateur, pas de points
        }

        $ratio = get_option('loyalty_points_ratio', 1);
        $include_shipping = get_option('loyalty_include_shipping', false);

        // Calculer le montant total de la commande
        $total = $order->get_total();
        if (!$include_shipping) {
            $total -= $order->get_shipping_total();
        }

        // Calculer les points à retirer
        $points = $total * $ratio;

        // Retirer les points de l'utilisateur
        $this->update_user_points($user_id, -$points);
    }

    // Mettre à jour les points de l'utilisateur
    private function update_user_points($user_id, $points) {
        $current_points = get_user_meta($user_id, 'loyalty_points', true);
        $current_points = $current_points ? (float) $current_points : 0;
        $new