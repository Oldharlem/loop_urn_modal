# Loop Biotech - URN Selection Popup

A minimalistic, mobile-only popup that helps users choose between the Furever (pet) and EarthRise (human) urns on the Loop Biotech website.

## Features

- **Mobile-only display**: Only shows on devices with screen width ≤ 768px
- **One-time display**: Uses localStorage to ensure the popup is shown only once per user
- **Minimalistic design**: Matches the Loop Biotech website aesthetic with clean, simple styling
- **Standalone**: No dependencies, works independently
- **Responsive**: Adapts to different mobile screen sizes
- **Accessible**: Includes ARIA labels and keyboard support (ESC to close)

## Installation

### Option 1: Direct Script Include

Add the script to your product pages (Furever and EarthRise):

```html
<script src="/path/to/urn-selection-popup.js"></script>
```

Place this script tag just before the closing `</body>` tag for optimal performance.

### Option 2: WordPress Integration

If you're using WordPress, you can add the script through your theme:

1. Upload `urn-selection-popup.js` to your theme's `js` folder
2. Enqueue the script in your theme's `functions.php`:

```php
function loop_enqueue_urn_popup() {
    if (is_product() && (is_page('furever') || is_page('earthrise'))) {
        wp_enqueue_script(
            'loop-urn-popup',
            get_template_directory_uri() . '/js/urn-selection-popup.js',
            array(),
            '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'loop_enqueue_urn_popup');
```

### Option 3: Google Tag Manager

1. Go to your Google Tag Manager workspace
2. Create a new Tag → Custom HTML
3. Paste the contents of `urn-selection-popup.js` wrapped in `<script>` tags
4. Set the trigger to fire on specific product pages
5. Publish the container

## Configuration

The popup can be customized by editing the `CONFIG` object in the script:

```javascript
const CONFIG = {
  storageKey: 'loop_urn_selection_shown',  // LocalStorage key
  mobileMaxWidth: 768,                      // Max width for mobile detection
  products: {
    furever: {
      title: 'Loop FurEver™',
      subtitle: 'Voor huisdieren',
      url: 'https://loop-biotech.com/nl/product/furever/',
      image: 'https://...'  // Product image URL
    },
    earthrise: {
      title: 'Loop EarthRise™',
      subtitle: 'Voor mensen',
      url: 'https://loop-biotech.com/nl/product/earthrise/',
      image: 'https://...'  // Product image URL
    }
  }
};
```

## How It Works

1. **Mobile Detection**: Checks if viewport width is ≤ 768px
2. **Storage Check**: Verifies if the popup has been shown before using localStorage
3. **Display**: If conditions are met, shows the popup after a 500ms delay
4. **User Action**:
   - Clicking a product navigates to that product page and marks popup as shown
   - Clicking "Sluiten" (Close) closes the popup and marks it as shown
   - Clicking outside the modal or pressing ESC also closes it
5. **Storage**: Saves to localStorage to prevent showing again

## Styling

The popup uses:
- **Font**: Montserrat (falls back to system fonts if unavailable)
- **Colors**: Black (#000) text on white background
- **Border radius**: 8px for modern, soft appearance
- **Transitions**: Smooth 0.3s animations
- **Shadow**: Subtle shadows for depth

Styles are injected directly into the page, so no external CSS file is needed.

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- iOS Safari 12+
- Android Chrome 80+
- Requires localStorage support

## Testing

To test the popup:

1. Open the demo file `demo.html` in your browser
2. Resize your browser window to mobile size (≤ 768px wide), or use developer tools device emulation
3. The popup should appear after page load
4. Test all interactions:
   - Click product cards
   - Click close button
   - Click outside modal
   - Press ESC key
5. Refresh the page - popup should not appear again
6. Clear localStorage to reset: `localStorage.removeItem('loop_urn_selection_shown')`

## Troubleshooting

**Popup doesn't show:**
- Check browser console for errors
- Verify screen width is ≤ 768px
- Clear localStorage: `localStorage.clear()`
- Ensure script is loaded: Check Network tab in DevTools

**Popup shows on desktop:**
- Increase `mobileMaxWidth` value in CONFIG, or
- Check if mobile device detection is working correctly

**Popup shows every time:**
- Check if localStorage is enabled in browser
- Verify no errors in console preventing storage

## Resetting the Popup

For testing purposes, clear the localStorage flag:

```javascript
localStorage.removeItem('loop_urn_selection_shown');
```

Or clear all localStorage:

```javascript
localStorage.clear();
```

## License

© 2024 Loop Biotech. All rights reserved.
