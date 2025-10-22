# Debugging Guide - AJAX Filters

## Quick Debugging Steps

If the apply button doesn't work or filters aren't loading, follow these steps:

### Step 1: Open Browser Console

1. Open your page with the filters
2. Press `F12` (or `Cmd+Option+I` on Mac)
3. Click on the "Console" tab
4. Refresh the page
5. Look for messages starting with `[KCPF]`

### Step 2: Check Initial Load Messages

You should see these messages when the page loads:

```
[KCPF] Found X filter elements
[KCPF] Wrapping X filters in form
[KCPF] Filters wrapped successfully
[KCPF] Filters initialized
```

**If you don't see these messages:**

- The JavaScript file isn't loading correctly
- jQuery might not be available
- There's a JavaScript error preventing execution

### Step 3: Click the Apply Button

When you click "Apply Filters", you should see:

```
[KCPF] Apply button clicked
[KCPF] Form found: 1
[KCPF] Form submit event triggered
[KCPF] Form submitted, processing...
[KCPF] Form data: {location: "limassol", price_min: "100000", ...}
[KCPF] URL params: location=limassol&price_min=100000...
[KCPF] Loading filtered results...
[KCPF] AJAX URL: https://your-site.com/wp-admin/admin-ajax.php?action=kcpf_load_properties&...
[KCPF] New URL: /properties/?location=limassol&price_min=100000...
[KCPF] Sending AJAX request...
[KCPF] AJAX response received: {success: true, data: {...}}
[KCPF] Results updated successfully
```

### Step 4: Common Issues and Solutions

#### Issue 1: "Apply button clicked" but "Form found: 0"

**Problem:** The apply button is not inside the form.

**Solution:** Make sure all filter shortcodes are placed together:

```
[property_filter_location]
[property_filter_price]
[property_filters_apply]

[properties_loop]
```

Don't separate filters into different containers/divs.

#### Issue 2: "kcpfData not found - AJAX URL not available"

**Problem:** WordPress didn't localize the AJAX URL.

**Solution:**

1. Make sure the plugin is activated
2. Clear your cache (browser and WordPress)
3. Check if other plugins are conflicting

#### Issue 3: AJAX error with 400/404/500 status

**Problem:** Server error or wrong AJAX endpoint.

**Solution:**

1. Check the error message in console
2. Look at the "Response" tab in Network panel
3. Make sure WordPress AJAX is working (test with other plugins)

#### Issue 4: No console messages at all

**Problem:** JavaScript file not loading or jQuery not available.

**Solution:**

1. Check Network tab in browser dev tools
2. Look for `filters.js` - should return 200 status
3. Check for JavaScript errors (red text in console)
4. Make sure jQuery is loaded on the page

### Step 5: Check Network Tab

1. Open browser dev tools (F12)
2. Click "Network" tab
3. Filter by "XHR" or "Fetch"
4. Click the apply button
5. You should see a request to `admin-ajax.php?action=kcpf_load_properties`

Click on this request to see:

- **Headers:** Check the URL and parameters
- **Response:** Should be JSON with `{success: true, data: {html: "..."}}`

### Step 6: Check Filter Form

In the browser console, type:

```javascript
$(".kcpf-filters-form").length;
```

**Expected:** `1` (or the number of filter groups you have)

**If 0:** The form wasn't created - filters aren't being wrapped correctly.

### Step 7: Check AJAX URL

In the browser console, type:

```javascript
kcpfData;
```

**Expected:**

```javascript
{
  ajaxUrl: "https://your-site.com/wp-admin/admin-ajax.php",
  nonce: "abc123..."
}
```

**If undefined:** The localized script data isn't available.

---

## Manual Testing Commands

Run these in the browser console to test manually:

### Test 1: Check if filters exist

```javascript
console.log("Filters:", $(".kcpf-filter").length);
console.log("Form:", $(".kcpf-filters-form").length);
console.log("Apply button:", $(".kcpf-apply-button").length);
```

### Test 2: Manually trigger AJAX

```javascript
jQuery.ajax({
  url: kcpfData.ajaxUrl + "?action=kcpf_load_properties&location=limassol",
  type: "GET",
  dataType: "json",
  success: function (response) {
    console.log("Success:", response);
  },
  error: function (xhr, status, error) {
    console.log("Error:", status, error);
  },
});
```

### Test 3: Check form data

```javascript
var formData = $(".kcpf-filters-form").serializeArray();
console.log("Form data:", formData);
```

---

## Common WordPress Issues

### Issue: AJAX returns "0"

**Cause:** AJAX handler not registered or function doesn't exist.

**Fix:** Make sure in `key-cy-properties-filter.php`:

```php
add_action('wp_ajax_kcpf_load_properties', [$this, 'ajaxLoadProperties']);
add_action('wp_ajax_nopriv_kcpf_load_properties', [$this, 'ajaxLoadProperties']);
```

### Issue: AJAX returns HTML instead of JSON

**Cause:** PHP error or output before AJAX response.

**Fix:**

1. Enable WordPress debugging
2. Check error logs
3. Make sure no `echo` statements before `wp_send_json_success()`

### Issue: Filters work but URL doesn't update

**Cause:** Browser doesn't support `history.pushState` or there's an error.

**Fix:** Check browser console for errors. Modern browsers all support this.

---

## Plugin Conflicts

If filters don't work after checking everything:

1. **Disable all other plugins** temporarily
2. Test if filters work
3. Re-enable plugins one by one to find the conflict
4. Common conflicts:
   - Caching plugins (clear cache)
   - Other AJAX/filter plugins
   - Page builders that modify JavaScript

---

## Still Not Working?

If you've checked all of the above and it still doesn't work:

1. **Share the console output** - Copy all `[KCPF]` messages
2. **Share any error messages** - Red text in console
3. **Test on a default WordPress theme** - Switch temporarily to Twenty Twenty-Four
4. **Check server PHP error logs** - Look for PHP errors

---

## Expected Console Output (Working)

When everything works, this is what you should see:

```
[KCPF] Found 3 filter elements
[KCPF] Wrapping 3 filters in form
[KCPF] Filters wrapped successfully
[KCPF] Filters initialized

[User clicks Apply]

[KCPF] Apply button clicked
[KCPF] Form found: 1
[KCPF] Form submit event triggered
[KCPF] Form submitted, processing...
[KCPF] Form data: {location: "limassol"}
[KCPF] URL params: location=limassol
[KCPF] Loading filtered results...
[KCPF] AJAX URL: https://site.com/wp-admin/admin-ajax.php?action=kcpf_load_properties&location=limassol
[KCPF] New URL: /properties/?location=limassol
[KCPF] Sending AJAX request...
[KCPF] AJAX response received: {success: true, data: {html: "..."}}
[KCPF] Results updated successfully
```

The URL should change to include your filter parameters, and the results should update without page reload.
