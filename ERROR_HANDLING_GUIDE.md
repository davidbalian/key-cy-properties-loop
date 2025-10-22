# Error Handling Guide

## Version 2.1.1 Updates

The plugin now includes comprehensive error handling to prevent critical errors and provide helpful debugging information.

---

## What Was Added

### 1. Try-Catch Blocks

All filter rendering methods now have try-catch blocks:

- `renderLocation()`
- `renderPrice()`
- `renderBedrooms()`
- `renderBathrooms()`
- `renderAmenities()`
- `renderCoveredArea()`
- `renderPlotArea()`
- And all other filter methods

**Result:** If a filter fails, it simply doesn't render instead of breaking the entire page.

### 2. Input Validation

All range filters now validate their attributes:

```php
// Price filter example
$attrs['min'] = absint($attrs['min']);  // Ensures positive integer
$attrs['max'] = absint($attrs['max']);
$attrs['step'] = absint($attrs['step']);

if ($attrs['min'] >= $attrs['max']) {
    error_log('Min must be less than max');
    return '';  // Fail gracefully
}
```

**Prevents:** Critical errors from invalid shortcode attributes.

### 3. Detailed Error Logging

All errors are logged to WordPress error logs with specific messages:

```php
error_log('KCPF Price Filter Error: Min must be less than max');
error_log('KCPF Glossary Handler: JetEngine is not available');
error_log('KCPF Amenities Filter: No amenities found in glossary "Amenities"');
```

**Location:** WordPress debug log (usually `wp-content/debug.log`)

### 4. AJAX Error Handling

AJAX requests now catch and return errors properly:

```php
try {
    $html = KCPF_Loop_Renderer::render($attrs);
    wp_send_json_success(['html' => $html]);
} catch (Exception $e) {
    error_log('KCPF AJAX Error: ' . $e->getMessage());
    wp_send_json_error(['message' => 'Error loading properties']);
}
```

**Result:** AJAX failures return proper JSON error responses instead of breaking.

---

## How to Check for Errors

### 1. Enable WordPress Debug Mode

Add to your `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### 2. Check the Error Log

Errors are written to: `wp-content/debug.log`

Look for lines starting with `KCPF`:

```
[22-Oct-2025 10:30:15 UTC] KCPF Glossary Handler: JetEngine is not available
[22-Oct-2025 10:30:16 UTC] KCPF Amenities Filter: No amenities found in glossary "Amenities"
[22-Oct-2025 10:30:17 UTC] KCPF Price Filter Error: Min must be less than max
```

### 3. Check Browser Console

AJAX errors also appear in the browser console:

```javascript
[KCPF] AJAX error:
Status: error
Error: Internal Server Error
Response: {"success":false,"data":{"message":"Error loading properties"}}
```

---

## Common Error Messages

### "JetEngine is not available"

**Cause:** JetEngine plugin is not installed or activated.

**Solution:**

1. Install and activate JetEngine
2. Or remove glossary-dependent filters (amenities, bedrooms, bathrooms)

**Shortcodes affected:**

- `[property_filter_amenities]`
- `[property_filter_bedrooms]` (with glossary)
- `[property_filter_bathrooms]` (with glossary)

---

### "Glossary [Name] not found"

**Cause:** The specified glossary doesn't exist in JetEngine.

**Solution:**

1. Go to JetEngine > Glossaries
2. Create glossaries named exactly:
   - `Amenities`
   - `Bedrooms`
   - `Bathrooms`

**Important:** Names are case-sensitive!

---

### "No amenities found in glossary"

**Cause:** The glossary exists but has no fields/values.

**Solution:**

1. Go to JetEngine > Glossaries
2. Open the "Amenities" glossary
3. Add at least one field with value and label

---

### "Min must be less than max"

**Cause:** Invalid shortcode attributes in a range filter.

**Wrong:**

```
[property_filter_price min="1000000" max="100000"]
```

**Right:**

```
[property_filter_price min="100000" max="1000000"]
```

**Affected filters:**

- `[property_filter_price]`
- `[property_filter_covered_area]`
- `[property_filter_plot_area]`

---

### "AJAX Error: Error loading properties"

**Cause:** PHP error during properties loop rendering.

**Solution:**

1. Check `debug.log` for the specific error
2. Common causes:
   - Missing post type "properties"
   - Missing taxonomies (location, purpose, property-type)
   - Theme conflict
   - Memory limit exceeded

---

## Graceful Degradation

The plugin now fails gracefully:

### Before v2.1.1:

```
❌ Critical Error - White screen of death
❌ Entire page breaks
❌ No helpful error message
```

### After v2.1.1:

```
✅ Filter silently doesn't render
✅ Page continues to work
✅ Error logged for debugging
✅ Other filters still work
```

---

## Validation Rules

### Price Filter

```php
- min: Must be positive integer
- max: Must be positive integer
- step: Must be positive integer
- min < max: Required
- step > 0: Required
```

### Covered Area Filter

```php
- min: Must be positive integer (default: 0)
- max: Must be positive integer (default: 10000)
- step: Must be positive integer (default: 10)
- min < max: Required
```

### Plot Area Filter

```php
- min: Must be positive integer (default: 0)
- max: Must be positive integer (default: 50000)
- step: Must be positive integer (default: 50)
- min < max: Required
```

---

## Best Practices

### 1. Always Test After Adding Filters

After adding new filter shortcodes:

1. View the page
2. Check browser console for errors
3. Check `debug.log` for PHP errors
4. Test clicking "Apply Filters"

### 2. Use Correct Attributes

**Wrong:**

```
[property_filter_price min="-100" max="abc" step="0"]
```

**Right:**

```
[property_filter_price min="100000" max="5000000" step="50000"]
```

### 3. Create Required Glossaries

Before using these filters, create glossaries in JetEngine:

- Amenities
- Bedrooms
- Bathrooms

### 4. Monitor Error Logs

Regularly check `wp-content/debug.log` for `KCPF` errors.

---

## Debugging Steps

If you get a critical error:

### Step 1: Enable Debug Mode

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Step 2: Check Debug Log

```bash
tail -f wp-content/debug.log
```

### Step 3: Identify the Problem Filter

Remove filters one by one until error disappears:

1. Start with all filters
2. Remove amenities filter → test
3. Remove covered area → test
4. Remove plot area → test
5. etc.

### Step 4: Fix the Issue

Based on the error message:

- Missing glossary → Create it
- Invalid attributes → Fix shortcode
- JetEngine missing → Install it

### Step 5: Re-add Filters

Add them back one by one to ensure they work.

---

## What to Share When Reporting Issues

If you encounter an error, share:

1. **Error log entries** (from `debug.log`)
2. **Browser console errors** (F12 → Console tab)
3. **Shortcodes used** (copy/paste exact shortcodes)
4. **WordPress version**
5. **JetEngine version** (if applicable)
6. **Active theme name**

Example:

```
Error log:
[22-Oct-2025 10:30:15] KCPF Glossary Handler: JetEngine is not available

Shortcodes:
[property_filter_amenities]

WordPress: 6.4
Theme: Bricks
```

---

## Error-Free Setup Checklist

✅ JetEngine plugin installed and activated  
✅ Glossaries created (Amenities, Bedrooms, Bathrooms)  
✅ Each glossary has at least one field  
✅ Post type "properties" exists  
✅ Taxonomies exist (location, purpose, property-type)  
✅ Debug mode enabled during testing  
✅ All shortcode attributes are valid  
✅ Filters are in same container  
✅ Apply button is included

---

**Version 2.1.1 ensures your site stays online even when filters encounter issues!** 🛡️
