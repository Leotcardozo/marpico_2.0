<?php
class Promo_API_Handler {
    private $api_url = 'https://apipromocionales.marpico.co/api/inventarios/materialesAPI';
    private $api_token = 'eyJhbGciOiJIUzI1NiJ9.TUFSS0VUSU5HIFBMVVNTIFBST01PQ0lPTkFMIFMuQS5T.a8gUIh62bLqACN_vS1fwPAtAtUvSCTL3a3gl6MH4beE';

    public function __construct() {
        // Registrar shortcode
        add_shortcode('mpc_productos', array($this, 'products_shortcode'));
        
        // Registrar endpoint AJAX
        add_action('wp_ajax_get_promo_products', array($this, 'get_products_ajax'));
        add_action('wp_ajax_nopriv_get_promo_products', array($this, 'get_products_ajax'));
    }

    // Obtener productos desde la API
    public function get_products() {
        $args = array(
            'headers' => array(
                'Authorization' => 'Api-Key ' . $this->api_token,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30
        );

        $response = wp_remote_get($this->api_url, $args);

        if (is_wp_error($response)) {
            error_log('Error al conectar con la API: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Error al decodificar JSON: ' . json_last_error_msg());
            return false;
        }

        return $data;
    }

    // Shortcode para mostrar productos
    public function products_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'per_page' => 12
        ), $atts);

        ob_start();
        ?>
        <div class="promo-products-container" 
             data-category="<?php echo esc_attr($atts['category']); ?>" 
             data-per-page="<?php echo esc_attr($atts['per_page']); ?>">
            <div class="promo-products-loader">Cargando productos...</div>
            <div class="promo-products-grid"></div>
            <div class="promo-products-pagination"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Manejar solicitud AJAX
    public function get_products_ajax() {
        check_ajax_referer('promo_api_nonce', 'nonce');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 12;
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

        $cache_handler = new Promo_Cache_Handler();
        $products = $cache_handler->get_cached_products();

        if (!$products) {
            $products = $this->get_products();
            if ($products && isset($products['response'])) {
                $cache_handler->set_cache($products['response']);
                $products = $products['response'];
            } else {
                wp_send_json_error('No se pudieron obtener los productos');
            }
        }

        // Filtrar por categoría si se especifica
        if (!empty($category)) {
            $products = array_filter($products, function($product) use ($category) {
                return strtolower($product['categorias']) === strtolower($category);
            });
        }

        // Paginar
        $total = count($products);
        $total_pages = ceil($total / $per_page);
        $offset = ($page - 1) * $per_page;
        $paginated_products = array_slice($products, $offset, $per_page);

        ob_start();
        foreach ($paginated_products as $product) {
            $this->render_product_card($product);
        }
        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'total_pages' => $total_pages,
            'current_page' => $page
        ));
    }

    // Renderizar tarjeta de producto
    private function render_product_card($product) {
        $main_image = !empty($product['imagen']) ? $product['imagen'] : '';
        ?>
        <div class="promo-product-card">
            <div class="promo-product-image">
                <?php if ($main_image): ?>
                    <img src="<?php echo esc_url($main_image); ?>" alt="<?php echo esc_attr($product['descripcion_comercial']); ?>" loading="lazy">
                <?php endif; ?>
            </div>
            <div class="promo-product-info">
                <h3><?php echo esc_html($product['descripcion_comercial']); ?></h3>
                <div class="promo-product-category">
                    <?php echo esc_html($product['familia']); ?> / <?php echo esc_html($product['subfamilia']); ?>
                </div>
                <div class="promo-product-desc">
                    <?php echo esc_html($product['descripcion_larga']); ?>
                </div>
                <div class="promo-product-sku">
                    SKU: <?php echo esc_html($product['codigo']); ?>
                </div>
                
            </div>
        </div>
        <?php
    }
}