/**
 * Admin Scripts for Loop Product Selector
 */

(function($) {
    'use strict';

    let productIndex = 0;

    $(document).ready(function() {
        // Initialize products from saved data
        const savedProducts = JSON.parse($('#lps_products').val() || '[]');
        savedProducts.forEach(product => {
            addProduct(product);
        });

        // If no products, add one empty product
        if (savedProducts.length === 0) {
            addProduct({});
        }

        // Add product button
        $('#lps-add-product').on('click', function() {
            addProduct({});
        });

        // Remove product (delegated event)
        $('#lps-products-container').on('click', '.lps-remove-product', function() {
            $(this).closest('.lps-product-item').remove();
            updateProductNumbers();
            updateHiddenField();
        });

        // Update hidden field when inputs change
        $('#lps-products-container').on('input', 'input', function() {
            updateHiddenField();
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
                imageInput.val(attachment.url);
                imagePreview.html('<img src="' + attachment.url + '" alt="Preview">');
                updateHiddenField();
            });

            frame.open();
        });

        // Preview button
        $('#lps-preview-button').on('click', function(e) {
            e.preventDefault();
            showPreview();
        });

        // Form validation
        $('#lps-settings-form').on('submit', function(e) {
            const products = getProducts();

            if (products.length === 0) {
                alert('Please add at least one product.');
                e.preventDefault();
                return false;
            }

            for (let i = 0; i < products.length; i++) {
                if (!products[i].title || !products[i].url || !products[i].image) {
                    alert('Please fill in all required fields for Product ' + (i + 1));
                    e.preventDefault();
                    return false;
                }
            }
        });
    });

    function addProduct(product) {
        const template = $('#lps-product-template').html();
        const html = template
            .replace(/\{\{index\}\}/g, productIndex)
            .replace(/\{\{number\}\}/g, productIndex + 1)
            .replace(/\{\{title\}\}/g, product.title || '')
            .replace(/\{\{subtitle\}\}/g, product.subtitle || '')
            .replace(/\{\{url\}\}/g, product.url || '')
            .replace(/\{\{image\}\}/g, product.image || '')
            .replace(/\{\{#if image\}\}(.*?)\{\{\/if\}\}/gs, product.image ?
                '<img src="' + product.image + '" alt="Preview">' : '');

        $('#lps-products-container').append(html);
        productIndex++;
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

        // Load preview script
        const script = document.createElement('script');
        script.src = lpsAdmin.pluginUrl + 'assets/js/popup.js?v=' + Date.now();
        script.onload = function() {
            // Override shouldShowPopup to always show in preview
            console.log('Preview loaded with config:', config);
        };

        document.body.appendChild(script);
    }

})(jQuery);
