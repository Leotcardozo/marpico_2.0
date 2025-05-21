jQuery(document).ready(function($) {
    function loadProducts(page = 1) {
        const container = $('.mpc-products-container');
        const grid = container.find('.mpc-products-grid');
        const loader = container.find('.mpc-products-loader');
        const pagination = container.find('.mpc-products-pagination');
        
        const category = container.data('category');
        const perPage = container.data('per-page');
        
        loader.show();
        grid.hide();
        pagination.hide();
        
        $.ajax({
            url: mpcApi.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_mpc_products',
                nonce: mpcApi.nonce,
                page: page,
                per_page: perPage,
                category: category
            },
            success: function(response) {
                if (response.success) {
                    grid.html(response.data.html);
                    
                    // Actualizar paginación
                    pagination.empty();
                    if (response.data.total_pages > 1) {
                        for (let i = 1; i <= response.data.total_pages; i++) {
                            const pageLink = $('<button>')
                                .text(i)
                                .toggleClass('active', i === response.data.current_page)
                                .click(function() {
                                    loadProducts(i);
                                });
                            pagination.append(pageLink);
                        }
                        pagination.show();
                    }
                    
                    grid.fadeIn();
                } else {
                    grid.html('<div class="mpc-products-error">Error al cargar los productos</div>').show();
                }
            },
            error: function() {
                grid.html('<div class="mpc-products-error">Error al cargar los productos</div>').show();
            },
            complete: function() {
                loader.hide();
            }
        });
    }
    
    // Cargar productos cuando el contenedor esté en la página
    if ($('.mpc-products-container').length) {
        loadProducts();
    }
});