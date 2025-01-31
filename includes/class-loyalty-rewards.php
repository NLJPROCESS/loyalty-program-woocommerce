<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Loyalty_Rewards {
    public function __construct() {
        // Enregistrer un type de post personnalisé pour les récompenses
        add_action('init', array($this, 'register_rewards_post_type'));
    }

    // Enregistrer un type de post personnalisé pour les récompenses
    public function register_rewards_post_type() {
        $args = array(
            'public' => false,
            'label'  => 'Récompenses',
            'supports' => array('title'),
        );
        register_post_type('loyalty_reward', $args);
    }
}