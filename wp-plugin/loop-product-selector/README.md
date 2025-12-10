# Loop Product Selector - WordPress Plugin

A mobile-only popup that displays product selection options. Perfect for e-commerce sites that want to help mobile visitors choose between different product variants or categories.

## Features

- ✅ **Mobile-Only**: Automatically detects mobile devices and only shows on small screens
- ✅ **One-Time Display**: Uses browser storage to show popup only once per visitor
- ✅ **Fully Configurable**: Easy-to-use admin interface, no coding required
- ✅ **Multiple Products**: Support for 1 or more products in the popup
- ✅ **Image Upload**: Built-in WordPress media uploader for product images
- ✅ **Responsive Design**: Adapts to different mobile screen sizes
- ✅ **Loading Animation**: Shows spinner while navigating to product
- ✅ **Accessible**: Keyboard navigation and screen reader support

---

## Installation

### For Non-Technical Users

#### Step 1: Download the Plugin

1. Download the `loop-product-selector` folder to your computer
2. Zip the folder if it's not already zipped (Right-click → Compress/Send to → Compressed folder)

#### Step 2: Install via WordPress Admin

1. Log in to your WordPress admin panel
2. Go to **Plugins → Add New**
3. Click **Upload Plugin** at the top
4. Click **Choose File** and select your zip file
5. Click **Install Now**
6. Click **Activate Plugin**

#### Alternative: Install via FTP

1. Upload the `loop-product-selector` folder to `/wp-content/plugins/`
2. Go to **Plugins** in WordPress admin
3. Find "Loop Product Selector" and click **Activate**

---

## Configuration

### Access Settings

1. Go to **Settings → Product Selector** in your WordPress admin
2. You'll see the configuration page

### Basic Settings

#### 1. Enable Popup
- Toggle this ON to activate the popup on your site
- Toggle OFF to temporarily disable without losing your configuration

#### 2. Mobile Max Width
- Default: **768px**
- The popup will show only on devices with screen width ≤ this value
- Common values:
  - 768px = Tablets and phones
  - 640px = Phones only
  - 1024px = All mobile devices and some tablets

#### 3. Popup Title
- The question shown at the top of the popup
- Example: "In welke urn bent u geïnteresseerd?"
- Example: "Which product are you looking for?"

#### 4. Storage Key
- Advanced setting (usually don't need to change)
- Change this to reset the popup for all users
- Useful if you want everyone to see the popup again

### Adding Products

#### Add Your First Product

1. Click **Add Product** button
2. Fill in the fields:
   - **Product Title**: Name of your product (e.g., "Loop FurEver™")
   - **Subtitle**: Optional description (e.g., "For pets")
   - **Product URL**: Where to send visitors when clicked
   - **Image URL**: Product image

#### Upload Product Image

1. Click the **Upload** button next to Image URL
2. Select image from your Media Library or upload new
3. Click **Use this image**
4. Image URL will be filled automatically

#### Add More Products

- Click **Add Product** again for each additional product
- You can add as many as you want
- Works best with 2-4 products

#### Remove a Product

- Click the **Remove** button (trash icon) next to any product

### Preview Your Popup

1. Configure your settings and products
2. Click **Preview Popup** button (bottom of page)
3. The popup will appear on your screen
4. Check that it looks good before saving

### Save Settings

1. Click **Save Settings** when you're happy with your configuration
2. You'll see a green success message
3. Visit your site on a mobile device to test!

---

## Testing the Popup

### Test on Desktop

1. Open your website in a browser
2. Press **F12** to open Developer Tools
3. Press **Ctrl+Shift+M** (Windows) or **Cmd+Shift+M** (Mac)
4. Select a mobile device from the dropdown (e.g., "iPhone 12 Pro")
5. Refresh the page
6. The popup should appear after 0.5 seconds

### Test on Real Mobile Device

1. Open your website on your phone
2. The popup should appear automatically
3. Try clicking on a product
4. You should see a loading spinner and then navigate to the product

### See the Popup Again

The popup shows only once per visitor. To test it again:

**Option 1: Use Incognito/Private Mode**
- Open your site in an incognito/private browser window
- The popup will show each time in a new incognito window

**Option 2: Clear Browser Storage**
1. On desktop: Press F12 → Application tab → Local Storage → Your site
2. Find the storage key (e.g., "product_selection_shown")
3. Delete it
4. Refresh the page

**Option 3: Change Storage Key**
1. Go to Settings → Product Selector
2. Change the Storage Key to something new
3. Save Settings
4. All users will see the popup again

---

## Customization

### Styling

The plugin includes default styling that matches most WordPress themes. If you want to customize:

1. Go to **Appearance → Customize → Additional CSS**
2. Add your custom CSS, for example:

```css
/* Change popup background color */
.loop-urn-modal {
  background: #f9f9f9;
}

/* Change title color */
.loop-urn-modal-title {
  color: #333;
}

/* Change product card border */
.loop-urn-product-card {
  border-color: #0073aa;
}

/* Change product card hover color */
.loop-urn-product-card:hover {
  border-color: #005177;
}
```

### Advanced Configuration

For developers, you can override the configuration programmatically:

```php
add_filter('lps_popup_config', function($config) {
    // Modify config before it's sent to JavaScript
    $config['mobileMaxWidth'] = 1024;
    return $config;
});
```

---

## Troubleshooting

### Popup Doesn't Show

**Check these things:**

1. ✅ Is the plugin activated?
   - Go to Plugins and make sure it's activated

2. ✅ Is the popup enabled?
   - Go to Settings → Product Selector
   - Check that "Enable Popup" is ON

3. ✅ Are you on mobile?
   - The popup only shows on devices ≤ 768px wide
   - Use DevTools device emulation to test

4. ✅ Have you seen it before?
   - The popup shows only once
   - Try incognito mode or clear localStorage

5. ✅ Do you have products configured?
   - Need at least one product with all required fields filled

6. ✅ Check browser console for errors
   - Press F12 → Console tab
   - Look for any error messages

### Popup Shows on Desktop

- Check your "Mobile Max Width" setting
- It should be 768 or less for mobile-only

### Images Don't Load

- Make sure image URLs are correct and accessible
- Try uploading images through the WordPress media uploader
- Check that images aren't blocked by security plugins

### Popup Shows Every Time

- Check that JavaScript is enabled in browser
- Check that browser allows localStorage
- Try clearing browser cache

### Product Links Don't Work

- Make sure Product URLs are complete (include https://)
- Check for typos in URLs
- Test URLs by pasting them in a browser

---

## Support

### Need Help?

1. Check the troubleshooting section above
2. Review your settings carefully
3. Test in incognito mode
4. Check browser console for errors

### Found a Bug?

Report issues at: https://github.com/Oldharlem/loop_urn_modal/issues

---

## Uninstallation

### To Remove the Plugin

1. Go to **Plugins** in WordPress admin
2. Find "Loop Product Selector"
3. Click **Deactivate**
4. Click **Delete**
5. Confirm deletion

Your settings will be removed from the database automatically.

---

## Changelog

### Version 1.0.0
- Initial release
- Mobile-only product selector popup
- WordPress admin interface
- Multiple product support
- Image uploader integration
- Preview functionality
- Loading animation
- Accessible design

---

## Credits

Developed for Loop Biotech
https://loop-biotech.com

---

## License

GPL v2 or later
