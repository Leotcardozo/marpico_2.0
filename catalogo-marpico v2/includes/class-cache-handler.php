<?php
class Promo_Cache_Handler {
    private $cache_key = 'promo_products_cache';
    private $cache_expiration = 12 * HOUR_IN_SECONDS; // 12 horas

    public function __construct() {
        // Programar limpieza de caché
        add_action('wp', array($this, 'schedule_cache_cleanup'));
        add_action('promo_api_cleanup_cache', array($this, 'cleanup_cache'));
    }

    // Programar limpieza de caché diaria
    public function schedule_cache_cleanup() {
        if (!wp_next_scheduled('promo_api_cleanup_cache')) {
            wp_schedule_event(time(), 'daily', 'promo_api_cleanup_cache');
        }
    }

    // Obtener productos desde caché
    public function get_cached_products() {
        $cached = get_transient($this->cache_key);
        return $cached ? $cached : false;
    }

    // Almacenar productos en caché
    public function set_cache($products) {
        set_transient($this->cache_key, $products, $this->cache_expiration);
    }

    // Limpiar caché
    public function cleanup_cache() {
        delete_transient($this->cache_key);
    }

    // Limpiar al desactivar el plugin
    public static function deactivation() {
        wp_clear_scheduled_hook('promo_api_cleanup_cache');
    }
}

// Registrar función de desactivación
register_deactivation_hook(__FILE__, array('Promo_Cache_Handler', 'deactivation'));