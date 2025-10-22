# Version 2.0.0 - AJAX Update

## What's New

Version 2.0.0 adds **full AJAX functionality** to the properties filter plugin. All filtering and pagination now happens instantly without page reloads, while keeping filter values in the URL.

---

## Key Features

### 🚀 Full AJAX Filtering

- Click "Apply Filters" and results load instantly via AJAX
- No page refresh needed
- Smooth, modern user experience

### 🔗 URL Parameters Preserved

- Filter values are **always shown in the URL**
- Users can bookmark filtered results
- Share filter URLs with others
- SEO-friendly URLs maintained

### 📄 AJAX Pagination

- Clicking page numbers loads results via AJAX
- Fast navigation between pages
- No full page reloads

### ⬅️ Browser Navigation Support

- Back button works correctly
- Forward button works correctly
- Browser history preserved with filters

### ⏳ Loading Indicator

- Animated spinner shows during AJAX requests
- Visual feedback for users
- Professional appearance

### 📍 Auto-Scroll to Results

- Automatically scrolls to results after filtering
- Smooth animation
- Better user experience

---

## What Changed

### Plugin Metadata

- **Version:** 1.0.0 → 2.0.0
- **Author:** Key CY → balian.cy
- **Author URI:** https://key-cy.com → https://balian.cy

### Functionality

- **Before:** Form submission triggered full page reload
- **After:** Form submission uses AJAX with URL updates

- **Before:** Pagination links caused page refreshes
- **After:** Pagination links use AJAX

- **Before:** URL parameters only updated on page load
- **After:** URL updates dynamically via `history.pushState()`

---

## Technical Implementation

### PHP Changes

**File:** `key-cy-properties-filter.php`

Added AJAX handler:

```php
public function ajaxLoadProperties()
{
    $attrs = [
        'purpose' => isset($_GET['purpose']) ? sanitize_text_field($_GET['purpose']) : 'sale',
        'posts_per_page' => isset($_GET['posts_per_page']) ? intval($_GET['posts_per_page']) : 10,
    ];

    $html = KCPF_Loop_Renderer::render($attrs);

    wp_send_json_success([
        'html' => $html,
    ]);
}
```

Registered AJAX actions:

- `wp_ajax_kcpf_load_properties` (logged-in users)
- `wp_ajax_nopriv_kcpf_load_properties` (non-logged-in users)

### JavaScript Changes

**File:** `assets/js/filters.js`

Key functions added/modified:

- `handleFormSubmission()` - Now always uses AJAX
- `loadPropertiesAjax()` - Unified AJAX loader for filters and pagination
- Added pagination click handler
- Added `popstate` event handler for browser navigation

AJAX flow:

1. User clicks "Apply" or pagination link
2. JavaScript prevents default behavior
3. Collects all form data
4. Sends AJAX request to `admin-ajax.php?action=kcpf_load_properties`
5. Updates page content with response
6. Updates URL using `history.pushState()`
7. Scrolls to results

### CSS Changes

**File:** `assets/css/filters.css`

Added loading styles:

```css
.kcpf-properties-loop.kcpf-loading {
  opacity: 0.6;
  pointer-events: none;
}

.kcpf-properties-loop.kcpf-loading::after {
  /* Animated spinner */
}
```

---

## Browser Compatibility

The AJAX functionality uses modern browser APIs:

- `fetch` API or jQuery AJAX
- `history.pushState()`
- `popstate` event
- `URLSearchParams`

**Supported browsers:**

- Chrome 34+
- Firefox 26+
- Safari 8+
- Edge (all versions)
- Modern mobile browsers

---

## How It Works

### Filtering Flow

```
User fills filters
    ↓
Clicks "Apply"
    ↓
JavaScript intercepts submit
    ↓
Collects form data
    ↓
AJAX request to server
    ↓
Server returns filtered HTML
    ↓
JavaScript replaces content
    ↓
URL updated with parameters
    ↓
Smooth scroll to results
```

### URL Update Example

**Before filtering:**

```
https://example.com/properties/
```

**After filtering (AJAX):**

```
https://example.com/properties/?purpose=rent&price_min=500&price_max=2000&bedrooms=2
```

URL updates instantly without page reload!

---

## No Breaking Changes

✅ **All shortcodes remain the same**
✅ **No changes to shortcode attributes**
✅ **Backward compatible with existing installations**
✅ **All existing filter configurations work as before**

The only difference is the improved user experience with AJAX loading.

---

## Developer Notes

### AJAX Endpoint

The plugin registers a WordPress AJAX action:

**Action name:** `kcpf_load_properties`

**Endpoint:** `wp-admin/admin-ajax.php?action=kcpf_load_properties`

**Method:** GET

**Parameters:** Same as URL filter parameters (location, purpose, price_min, etc.)

**Response format:**

```json
{
  "success": true,
  "data": {
    "html": "<div class='kcpf-properties-loop'>...</div>"
  }
}
```

### Customization

If you want to modify AJAX behavior:

1. **Change scroll offset:**

```javascript
// In filters.js, line ~156
scrollTop: $(".kcpf-properties-loop").offset().top - 100, // Change 100 to your value
```

2. **Disable smooth scrolling:**

```javascript
// Comment out lines 156-160 in filters.js
```

3. **Change loading spinner color:**

```css
/* In filters.css, line ~263 */
border-top: 4px solid #0073aa; /* Change color */
```

---

## Upgrade Instructions

1. Replace plugin files with v2.0.0
2. Clear browser cache
3. Test filters on your site
4. Verify URL parameters update correctly
5. Test browser back/forward buttons

**No database changes required!**

---

## Support

If you encounter any issues with AJAX functionality:

1. Check browser console for JavaScript errors
2. Verify `kcpfData.ajaxUrl` is defined (should be in page source)
3. Test with browser network tab to see AJAX requests
4. Ensure jQuery is loaded on the page

---

**Version 2.0.0 by balian.cy** 🚀
