<?php
/*
Plugin Name: Catálogo Marpico 2.1
Description: Muestra productos desde la API de Marpico.
Version: 2.1
Author: Tova De.
*/

defined('ABSPATH') or die('Acceso directo no permitido');

// Definir constantes
define('MPC_API_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MPC_API_PLUGIN_URL', plugin_dir_url(__FILE__));

// Incluir archivos necesarios
require_once MPC_API_PLUGIN_DIR . 'includes/class-api-handler.php';
require_once MPC_API_PLUGIN_DIR . 'includes/class-cache-handler.php';
require_once MPC_API_PLUGIN_DIR . 'includes/class-products-display.php';

// Inicializar el plugin
function mpc_api_init() {
    new Mpc_API_Handler();
    new Mpc_Cache_Handler();
    new Mpc_Products_Display();
}
add_action('plugins_loaded', 'mpc_api_init');

// Registrar estilos y scripts
function mpc_api_register_assets() {
    wp_register_style('mpc-api-style', MPC_API_PLUGIN_URL . 'assets/css/style.css');
    wp_register_script('mpc-api-script', MPC_API_PLUGIN_URL . 'assets/js/script.js', array('jquery'), '1.0', true);
    
    // Pasar variables a JavaScript
    wp_localize_script('mpc-api-script', 'mpcApi', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mpc_api_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'mpc_api_register_assets');