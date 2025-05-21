<?php
/*
Plugin Name: Catálogo Marpico 2.1
Description: Muestra productos desde la API de Marpico.
Version: 2.1
Author: Tova De.
*/

defined('ABSPATH') or die('Acceso directo no permitido');

// Definir constantes
define('PROMO_API_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PROMO_API_PLUGIN_URL', plugin_dir_url(__FILE__));

// Incluir archivos necesarios
require_once PROMO_API_PLUGIN_DIR . 'includes/class-api-handler.php';
require_once PROMO_API_PLUGIN_DIR . 'includes/class-cache-handler.php';
require_once PROMO_API_PLUGIN_DIR . 'includes/class-products-display.php';

// Inicializar el plugin
function promo_api_init() {
    new Promo_API_Handler();
    new Promo_Cache_Handler();
    new Promo_Products_Display();
}
add_action('plugins_loaded', 'promo_api_init');

// Registrar estilos y scripts
function promo_api_register_assets() {
    wp_register_style('promo-api-style', PROMO_API_PLUGIN_URL . 'assets/css/style.css');
    wp_register_script('promo-api-script', PROMO_API_PLUGIN_URL . 'assets/js/script.js', array('jquery'), '1.0', true);
    
    // Pasar variables a JavaScript
    wp_localize_script('promo-api-script', 'promoApi', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('promo_api_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'promo_api_register_assets');