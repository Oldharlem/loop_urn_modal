/**
 * Abstract URN/Product Selection Popup
 * Displays a minimalistic mobile-only popup for product selection
 *
 * Supports 1-N products dynamically from configuration
 * Supports multiple popup configurations (shows first matching one)
 *
 * Usage: Include this script and configure via CONFIG object
 */

(function() {
  'use strict';

  // Get configurations - support both single and multiple popups
  let CONFIGS = [];

  // Check for multiple popups first (WordPress plugin multi-popup mode)
  if (window.URN_POPUP_CONFIGS && Array.isArray(window.URN_POPUP_CONFIGS)) {
    CONFIGS = window.URN_POPUP_CONFIGS;
  }
  // Fall back to single popup (backward compatibility or preview mode)
  else if (window.URN_POPUP_CONFIG) {
    CONFIGS = [window.URN_POPUP_CONFIG];
  }
  // No configuration found
  else {
    console.log('URN Popup: No configuration found');
    return;
  }

  // Find first popup that should be shown
  let CONFIG = null;

  for (let i = 0; i < CONFIGS.length; i++) {
    const config = CONFIGS[i];

    // Backward compatibility: ensure showOnDesktop exists
    if (typeof config.showOnDesktop === 'undefined') {
      config.showOnDesktop = false;
    }

    // Validate configuration
    if (!config.products || !Array.isArray(config.products) || config.products.length === 0) {
      continue;
    }

    // Check if this popup should be shown
    if (shouldShowPopup(config)) {
      CONFIG = config;
      break;
    }
  }

  // No popup should be shown
  if (!CONFIG) {
    return;
  }

  // Check if popup should be shown
  function shouldShowPopup(config) {
    // Debug logging
    console.log('Loop Magic Popup: Checking if should show', {
      showOnDesktop: config.showOnDesktop,
      windowWidth: window.innerWidth,
      debug: config._debug || 'No debug info available'
    });

    // Check if mobile device (unless showOnDesktop is enabled)
    // Mobile max width is hardcoded to 768px
    if (!config.showOnDesktop && window.innerWidth > 768) {
      console.log('Loop Magic Popup: Skipping - desktop and showOnDesktop is false');
      return false;
    }

    // Check if already shown and handle time-based re-display
    const lastShown = localStorage.getItem(config.storageKey);

    if (lastShown) {
      // If redisplayDays is 0, show only once
      if (!config.redisplayDays || config.redisplayDays === 0) {
        return false;
      }

      // Check if enough time has passed
      const lastShownTime = parseInt(lastShown);
      const now = Date.now();
      const daysSinceShown = (now - lastShownTime) / (1000 * 60 * 60 * 24);

      if (daysSinceShown < config.redisplayDays) {
        return false;
      }
    }

    return true;
  }

  // Create and inject styles
  function injectStyles() {
    const productCount = CONFIG.products.length;
    const gridColumns = productCount === 1 ? '1fr' :
                       productCount === 2 ? '1fr 1fr' :
                       productCount === 3 ? 'repeat(3, 1fr)' :
                       'repeat(auto-fit, minmax(150px, 1fr))';

    const styles = `
      .loop-urn-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        transition: opacity 0.3s ease;
      }

      .loop-urn-modal-overlay.visible {
        opacity: 1;
      }

      .loop-urn-modal {
        background: white;
        border-radius: 8px;
        padding: 30px 20px;
        max-width: ${productCount === 1 ? '350px' : '500px'};
        width: 100%;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        animation: slideUp 0.3s ease;
      }

      @keyframes slideUp {
        from {
          transform: translateY(30px);
          opacity: 0;
        }
        to {
          transform: translateY(0);
          opacity: 1;
        }
      }

      .loop-urn-modal-title {
        font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-size: 20px;
        font-weight: 600;
        color: #000;
        text-align: center;
        margin: 0 0 25px 0;
        line-height: 1.4;
      }

      .loop-urn-modal-products {
        display: grid;
        grid-template-columns: ${gridColumns};
        gap: 15px;
        margin-bottom: 15px;
      }

      .loop-urn-product-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        padding: 15px;
        border: 2px solid #e5e5e5;
        border-radius: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
        background: white;
      }

      .loop-urn-product-card:hover,
      .loop-urn-product-card:active {
        border-color: #000;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      }

      .loop-urn-product-image {
        width: 100%;
        aspect-ratio: 1;
        object-fit: cover;
        border-radius: 4px;
        margin-bottom: 12px;
      }

      .loop-urn-product-title {
        font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-size: 14px;
        font-weight: 600;
        color: #000;
        text-align: center;
        margin: 0 0 4px 0;
        line-height: 1.3;
      }

      .loop-urn-product-subtitle {
        font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-size: 12px;
        font-weight: 400;
        color: #666;
        text-align: center;
        margin: 0;
      }

      .loop-urn-modal-close {
        display: block;
        width: 100%;
        padding: 12px;
        margin-top: 10px;
        background: transparent;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-size: 14px;
        color: #666;
        cursor: pointer;
        transition: all 0.2s ease;
      }

      .loop-urn-modal-close:hover,
      .loop-urn-modal-close:active {
        background: #f5f5f5;
        border-color: #999;
        color: #000;
      }

      .loop-urn-modal-close.loading {
        color: transparent;
        position: relative;
        pointer-events: none;
        border-color: #999;
      }

      .loop-urn-loader {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #666;
        border-radius: 50%;
        animation: loop-urn-spin 0.8s linear infinite;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
      }

      @keyframes loop-urn-spin {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
      }

      @media (max-width: 360px) {
        .loop-urn-modal {
          padding: 25px 15px;
        }

        .loop-urn-modal-title {
          font-size: 18px;
        }

        .loop-urn-product-title {
          font-size: 13px;
        }

        .loop-urn-product-subtitle {
          font-size: 11px;
        }

        .loop-urn-modal-products {
          grid-template-columns: ${productCount > 2 ? '1fr 1fr' : gridColumns};
        }
      }
    `;

    const styleEl = document.createElement('style');
    styleEl.textContent = styles;
    document.head.appendChild(styleEl);
  }

  // Create modal HTML
  function createModal() {
    const overlay = document.createElement('div');
    overlay.className = 'loop-urn-modal-overlay';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-labelledby', 'loop-urn-modal-title');

    // Generate product cards HTML
    const productCardsHTML = CONFIG.products.map((product, index) => {
      const subtitle = product.subtitle ?
        `<div class="loop-urn-product-subtitle">${escapeHtml(product.subtitle)}</div>` : '';

      return `
        <a href="${escapeHtml(product.url)}" class="loop-urn-product-card" data-product-index="${index}">
          <img
            src="${escapeHtml(product.image)}"
            alt="${escapeHtml(product.title)}"
            class="loop-urn-product-image"
            loading="eager"
          />
          <div class="loop-urn-product-title">${escapeHtml(product.title)}</div>
          ${subtitle}
        </a>
      `;
    }).join('');

    overlay.innerHTML = `
      <div class="loop-urn-modal">
        <h2 id="loop-urn-modal-title" class="loop-urn-modal-title" style="color: #000 !important;">
          ${escapeHtml(CONFIG.title)}
        </h2>
        <div class="loop-urn-modal-products">
          ${productCardsHTML}
        </div>
        <button type="button" class="loop-urn-modal-close">
          Sluiten
        </button>
      </div>
    `;

    return overlay;
  }

  // Escape HTML to prevent XSS
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // Show modal
  function showModal() {
    const modal = createModal();
    document.body.appendChild(modal);

    // Prevent body scroll
    document.body.style.overflow = 'hidden';

    // Animate in
    requestAnimationFrame(() => {
      modal.classList.add('visible');
    });

    // Handle product selection
    const productCards = modal.querySelectorAll('.loop-urn-product-card');
    const closeBtn = modal.querySelector('.loop-urn-modal-close');

    productCards.forEach(card => {
      card.addEventListener('click', (e) => {
        const productIndex = card.dataset.productIndex;

        // Show loader in close button
        closeBtn.classList.add('loading');
        const loader = document.createElement('span');
        loader.className = 'loop-urn-loader';
        closeBtn.appendChild(loader);

        markAsShown();
        // Let the link navigate naturally
      });
    });

    // Handle close button
    closeBtn.addEventListener('click', () => {
      closeModal(modal);
    });

    // Handle overlay click (outside modal)
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        closeModal(modal);
      }
    });

    // Handle escape key
    const handleEscape = (e) => {
      if (e.key === 'Escape') {
        closeModal(modal);
        document.removeEventListener('keydown', handleEscape);
      }
    };
    document.addEventListener('keydown', handleEscape);
  }

  // Close modal
  function closeModal(modal) {
    modal.classList.remove('visible');
    document.body.style.overflow = '';

    setTimeout(() => {
      modal.remove();
    }, 300);

    markAsShown();
  }

  // Mark as shown in localStorage
  function markAsShown() {
    // Store timestamp instead of just 'true' for time-based re-display
    localStorage.setItem(CONFIG.storageKey, Date.now().toString());
  }

  // Initialize
  function init() {
    injectStyles();

    // Show modal after a brief delay to ensure page is loaded
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => {
        setTimeout(showModal, 500);
      });
    } else {
      setTimeout(showModal, 500);
    }
  }

  // Start the script
  init();

})();
