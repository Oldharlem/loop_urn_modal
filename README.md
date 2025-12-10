# Loop Product Selector

A minimalistic, mobile-only popup for product selection. Available as both **standalone JavaScript** and a **WordPress plugin**.

Originally created for Loop Biotech to help users choose between URN options, now abstracted to work with any products.

---

## 🎯 Features

- ✅ **Mobile-only display** - Only shows on devices ≤ 768px width
- ✅ **One-time display** - Uses localStorage to show popup once per user
- ✅ **Multiple products** - Support for 1, 2, 3, 4+ products
- ✅ **Abstract/Configurable** - No hardcoded product names
- ✅ **Loading animation** - Professional spinner when product is clicked
- ✅ **Responsive design** - Adapts to different mobile screen sizes
- ✅ **Accessible** - ARIA labels and keyboard support (ESC to close)
- ✅ **Google Tag Manager ready** - Dedicated GTM-compatible snippet
- ✅ **WordPress plugin** - Full admin interface for non-technical users

---

## 📦 Available Implementations

### 1. Standalone JavaScript (Abstract)
**File:** `urn-selection-popup-abstract.js`

Generic version that works with any products. Configure via `window.URN_POPUP_CONFIG`.

**Best for:**
- Adding to any website manually
- Custom integrations
- Developers who want full control

### 2. Loop Biotech Specific (Legacy)
**Files:** `urn-selection-popup.js`, `google-tag-manager-snippet.html`

Pre-configured for Loop Biotech URN products (FurEver and EarthRise).

**Best for:**
- Loop Biotech website
- Quick implementation without configuration
- Google Tag Manager deployment

### 3. WordPress Plugin
**Folder:** `wp-plugin/loop-product-selector/`

Full WordPress plugin with admin interface.

**Best for:**
- WordPress sites
- Non-technical users
- Easy product management via admin panel

---

## 🚀 Quick Start

### Option A: Standalone Abstract Version

```html
<script>
  // Configure before loading the script
  window.URN_POPUP_CONFIG = {
    storageKey: 'product_selection_shown',
    mobileMaxWidth: 768,
    title: 'Which product are you interested in?',
    products: [
      {
        title: 'Product 1',
        subtitle: 'Description 1',
        url: 'https://example.com/product1',
        image: 'https://example.com/images/product1.jpg'
      },
      {
        title: 'Product 2',
        subtitle: 'Description 2',
        url: 'https://example.com/product2',
        image: 'https://example.com/images/product2.jpg'
      }
    ]
  };
</script>
<script src="urn-selection-popup-abstract.js"></script>
```

### Option B: Google Tag Manager (Loop Biotech)

1. Open `google-tag-manager-snippet.html`
2. Copy entire contents
3. GTM → Tags → New → Custom HTML
4. Paste code
5. Set trigger for specific product pages
6. Publish

### Option C: WordPress Plugin

1. Upload `wp-plugin/loop-product-selector/` to `/wp-content/plugins/`
2. Activate in WordPress admin
3. Go to **Settings → Product Selector**
4. Configure your products
5. Save and test!

---

## 📖 Documentation

### Standalone JavaScript

See the inline comments in `urn-selection-popup-abstract.js` for configuration options.

**Configuration Options:**

```javascript
{
  storageKey: 'product_selection_shown',  // LocalStorage key
  mobileMaxWidth: 768,                     // Max width for mobile detection
  title: 'Your question here',             // Popup title
  products: [                              // Array of products
    {
      title: 'Product Name',               // Required
      subtitle: 'Description',             // Optional
      url: 'https://...',                  // Required
      image: 'https://...'                 // Required
    }
  ]
}
```

### WordPress Plugin

Complete documentation in `wp-plugin/loop-product-selector/README.md`

**Key Features:**
- Visual admin interface
- Image uploader
- Live preview
- No coding required
- Enable/disable toggle
- Configurable mobile width
- Custom popup title
- Unlimited products

---

## 🧪 Testing

### Test on Desktop

1. Open your website
2. Press **F12** to open DevTools
3. Press **Ctrl+Shift+M** (Windows) or **Cmd+Shift+M** (Mac)
4. Select a mobile device (e.g., "iPhone 12 Pro")
5. Refresh the page
6. Popup should appear after 0.5 seconds

### Test on Mobile

1. Open your website on a phone
2. Popup should appear automatically
3. Click a product to see loading animation
4. Should navigate to product page

### See Popup Again

The popup shows only once. To test again:

- Use **Incognito/Private mode**
- Clear localStorage: `localStorage.removeItem('product_selection_shown')`
- Change the storage key in configuration

---

## 📁 Repository Structure

```
loop_urn_modal/
├── urn-selection-popup-abstract.js      # Generic/abstract version
├── urn-selection-popup.js               # Loop Biotech specific
├── google-tag-manager-snippet.html      # GTM version (Loop Biotech)
├── demo.html                            # Test/demo page
├── README.md                            # This file
└── wp-plugin/
    └── loop-product-selector/           # WordPress plugin
        ├── loop-product-selector.php    # Main plugin file
        ├── admin/
        │   ├── admin-page.php           # Settings page template
        │   ├── admin-scripts.js         # Admin JavaScript
        │   └── admin-styles.css         # Admin styles
        ├── assets/
        │   └── js/
        │       └── popup.js             # Frontend popup script
        ├── README.md                    # Plugin documentation
        └── readme.txt                   # WordPress.org readme
```

---

## 🎨 Customization

### Styling

All versions use inline styles but can be overridden with CSS:

```css
/* Change popup background */
.loop-urn-modal {
  background: #f9f9f9;
}

/* Change title color (already forced to black) */
.loop-urn-modal-title {
  color: #333 !important;
}

/* Change product card border */
.loop-urn-product-card {
  border-color: #0073aa;
}

/* Change hover effect */
.loop-urn-product-card:hover {
  border-color: #005177;
  transform: translateY(-3px);
}
```

### Grid Layout

The abstract version automatically adjusts grid columns based on product count:

- 1 product: Single column
- 2 products: Two columns
- 3 products: Three columns
- 4+ products: Auto-fit with min 150px

---

## 🔧 WordPress Plugin Details

### Installation

**Via WordPress Admin:**
1. Go to Plugins → Add New → Upload Plugin
2. Upload `loop-product-selector.zip`
3. Activate
4. Configure at Settings → Product Selector

**Via FTP:**
1. Upload folder to `/wp-content/plugins/`
2. Activate in WordPress admin

### Admin Interface

- **Enable/Disable toggle** - Turn on/off without losing settings
- **Mobile width** - Control when popup shows (default 768px)
- **Popup title** - Customize the question
- **Storage key** - Advanced: Reset popup for all users
- **Product manager:**
  - Add/remove products
  - Upload images via Media Library
  - Set title, subtitle, URL for each product
  - Live preview
- **Save & Preview** - Test before going live

### For Non-Technical Users

Complete step-by-step guide in `wp-plugin/loop-product-selector/README.md`

---

## 💻 Development

### Loop Biotech Specific Files

The original implementation for Loop Biotech:

- `urn-selection-popup.js` - ES6 version with FurEver/EarthRise hardcoded
- `google-tag-manager-snippet.html` - ES5 GTM version

These files include:
- Product subtitles ("Voor huisdieren" / "Voor mensen")
- Inline title color forcing (black)
- Loading spinner animation
- Specific product image URLs

### Creating Abstract Version

The abstract version (`urn-selection-popup-abstract.js`):
- Removed hardcoded product names
- Products now in array instead of named object
- Dynamic grid layout based on product count
- Configuration via `window.URN_POPUP_CONFIG`
- XSS protection with HTML escaping
- Supports 1-N products

---

## 🐛 Troubleshooting

### Popup doesn't show

- ✅ Check screen width is ≤ 768px (or your configured value)
- ✅ Clear localStorage or use incognito mode
- ✅ Check browser console for errors
- ✅ Verify at least one product is configured
- ✅ Check JavaScript is enabled

### Popup shows on desktop

- Increase screen width threshold in configuration
- Default is 768px (tablets and phones)

### Subtitles don't show

- Hard refresh browser (Ctrl+Shift+R)
- Clear browser cache
- Check configuration includes subtitle field

### Images don't load

- Verify image URLs are accessible
- Check HTTPS/HTTP mismatch
- Test URLs directly in browser

---

## 📝 License

GPL v2 or later

---

## 👥 Credits

**Developed for:** Loop Biotech
**Website:** https://loop-biotech.com
**Repository:** https://github.com/Oldharlem/loop_urn_modal

---

## 🔄 Version History

### v1.0.0 - Current
- ✅ Initial Loop Biotech implementation
- ✅ Mobile-only URN selector popup
- ✅ Google Tag Manager compatible version
- ✅ Loading animation
- ✅ Inline title color forcing
- ✅ Product subtitles
- ✅ Abstract/generic version created
- ✅ WordPress plugin with full admin interface
- ✅ Support for 1-N products
- ✅ Dynamic grid layout
- ✅ Image uploader integration
- ✅ Preview functionality

---

## 🚀 Getting Started Checklist

### For Standalone Use:
- [ ] Choose version (abstract or Loop Biotech specific)
- [ ] Configure products in `URN_POPUP_CONFIG`
- [ ] Add script tag to your HTML
- [ ] Test on mobile device or DevTools
- [ ] Customize styling if needed

### For Google Tag Manager:
- [ ] Copy `google-tag-manager-snippet.html` contents
- [ ] Create new Custom HTML tag in GTM
- [ ] Set trigger for product pages
- [ ] Test in GTM preview mode
- [ ] Publish container

### For WordPress:
- [ ] Upload and activate plugin
- [ ] Go to Settings → Product Selector
- [ ] Add your products with images
- [ ] Click Preview to test
- [ ] Save settings
- [ ] Test on mobile device

---

## 📞 Support

For issues, questions, or contributions:
- Open an issue: https://github.com/Oldharlem/loop_urn_modal/issues
- WordPress plugin documentation: `wp-plugin/loop-product-selector/README.md`

---

**Made with ❤️ for Loop Biotech**
