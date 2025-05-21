<?php
class Mpc_Products_Display {
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function enqueue_assets() {
        if (has_shortcode(get_post()->post_content, 'mpc_productos')) {
            wp_enqueue_style('mpc-api-style');
            wp_enqueue_script('mpc-api-script');
        }
    }
}