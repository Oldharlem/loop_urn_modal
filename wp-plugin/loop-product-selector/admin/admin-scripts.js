/**
 * Admin Scripts for Loop Product Selector
 */

(function($) {
    'use strict';

    let productIndex = 0;

    $(document).ready(function() {
        // Initialize products from saved data
        const savedProductsJson = $('#lps_products').val();
        let savedProducts = [];

        try {
            savedProducts = savedProductsJson ? JSON.parse(savedProductsJson) : [];
        } catch (e) {
            console.error('Error parsing saved products:', e);
            savedProducts = [];
        }

        // Render saved products
        if (savedProducts.length > 0) {
            savedProducts.forEach(product => {
                addProduct(product);
            });
        } else {
            // Add one empty product if none exist
            addProduct({});
        }

        // Add product button
        $('#lps-add-product').on('click', function() {
            addProduct({});
        });

        // Remove product (delegated event)
        $('#lps-products-container').on('click', '.lps-remove-product', function() {
            const $container = $('#lps-products-container');

            // Don't allow removing the last product
            if ($container.find('.lps-product-item').length <= 1) {
                alert('You must have at least one product.');
                return;
            }

            $(this).closest('.lps-product-item').remove();
            updateProductNumbers();
            updateHiddenField();
        });

        // Update hidden field when inputs change
        $('#lps-products-container').on('input change', 'input', function() {
            updateHiddenField();
        });

        // Update image preview when URL changes
        $('#lps-products-container').on('input', '.lps-product-image', function() {
            const $input = $(this);
            const url = $input.val().trim();
            const $preview = $input.closest('.lps-product-fields').find('.lps-image-preview');

            if (url) {
                $preview.html('<img src="' + url + '" alt="Preview" onerror="this.style.display=\'none\'">');
            } else {
                $preview.empty();
            }
        });

        // Media uploader
        $('#lps-products-container').on('click', '.lps-upload-image', function(e) {
            e.preventDefault();

            const button = $(this);
            const imageInput = button.siblings('.lps-product-image');
            const imagePreview = button.closest('.lps-product-fields').find('.lps-image-preview');

            const frame = wp.media({
                title: 'Select Product Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });

            frame.on('select', function() {
                const attachment = frame.state().get('selection').first().toJSON();
                imageInput.val(attachment.url).trigger('change');
                imagePreview.html('<img src="' + attachment.url + '" alt="Preview">');
            });

            frame.open();
        });

        // Preview button
        $('#lps-preview-button').on('click', function(e) {
            e.preventDefault();
            showPreview();
        });

        // Form validation before submit
        $('#lps-settings-form').on('submit', function(e) {
            // Update hidden field one last time before submit
            updateHiddenField();

            const products = getProducts();

            if (products.length === 0) {
                alert('Please add at least one product with all required fields filled.');
                e.preventDefault();
                return false;
            }

            // Validate each product
            for (let i = 0; i < products.length; i++) {
                const product = products[i];
                if (!product.title || !product.url || !product.image) {
                    alert('Please fill in all required fields (Title, URL, Image) for Product ' + (i + 1));
                    e.preventDefault();
                    return false;
                }
            }

            // Update hidden field again with validated products
            $('#lps_products').val(JSON.stringify(products));
        });
    });

    function addProduct(product) {
        product = product || {};

        const html = `
            <div class="lps-product-item" data-index="${productIndex}">
                <div class="lps-product-header">
                    <h3>Product <span class="lps-product-number">${productIndex + 1}</span></h3>
                    <button type="button" class="button button-link-delete lps-remove-product">
                        <span class="dashicons dashicons-trash"></span>
                        Remove
                    </button>
                </div>
                <div class="lps-product-fields">
                    <div class="lps-field">
                        <label>Product Title *</label>
                        <input type="text" class="regular-text lps-product-title" value="${escapeHtml(product.title || '')}" required>
                    </div>
                    <div class="lps-field">
                        <label>Subtitle (optional)</label>
                        <input type="text" class="regular-text lps-product-subtitle" value="${escapeHtml(product.subtitle || '')}">
                    </div>
                    <div class="lps-field">
                        <label>Product URL *</label>
                        <input type="url" class="regular-text lps-product-url" value="${escapeHtml(product.url || '')}" required>
                    </div>
                    <div class="lps-field">
                        <label>Image URL *</label>
                        <div class="lps-image-field">
                            <input type="url" class="regular-text lps-product-image" value="${escapeHtml(product.image || '')}" required>
                            <button type="button" class="button lps-upload-image">Upload</button>
                        </div>
                        <div class="lps-image-preview">
                            ${product.image ? '<img src="' + escapeHtml(product.image) + '" alt="Preview">' : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#lps-products-container').append(html);
        productIndex++;
        updateHiddenField();
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function updateProductNumbers() {
        $('#lps-products-container .lps-product-item').each(function(index) {
            $(this).find('.lps-product-number').text(index + 1);
        });
    }

    function getProducts() {
        const products = [];

        $('#lps-products-container .lps-product-item').each(function() {
            const $item = $(this);
            const product = {
                title: $item.find('.lps-product-title').val().trim(),
                subtitle: $item.find('.lps-product-subtitle').val().trim(),
                url: $item.find('.lps-product-url').val().trim(),
                image: $item.find('.lps-product-image').val().trim()
            };

            // Only add if required fields are filled
            if (product.title && product.url && product.image) {
                products.push(product);
            }
        });

        return products;
    }

    function updateHiddenField() {
        const products = getProducts();
        $('#lps_products').val(JSON.stringify(products));
    }

    function showPreview() {
        const config = {
            storageKey: 'preview_' + Date.now(),
            mobileMaxWidth: parseInt($('#lps_mobile_max_width').val()) || 768,
            title: $('#lps_title').val() || 'Product Selection',
            products: getProducts()
        };

        if (config.products.length === 0) {
            alert('Please add at least one product before previewing.');
            return;
        }

        // Remove existing preview
        $('#lps-preview-container').empty();

        // Set window config
        window.URN_POPUP_CONFIG = config;

        // Remove any existing preview script
        $('script[src*="popup.js"]').remove();

        // Load preview script
        const script = document.createElement('script');
        script.src = lpsAdmin.pluginUrl + 'assets/js/popup.js?v=' + Date.now();
        document.body.appendChild(script);
    }

})(jQuery);
