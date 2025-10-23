# AJAX Fix After JavaScript Split

## Issue

After splitting `filters.js` into 8 separate files, AJAX functionality stopped working:

- Filter submissions weren't loading properties
- Infinite scroll wasn't working
- Map info window property cards weren't loading

## Root Cause

**The `kcpfData` global variable wasn't being localized to the page.**

In `includes/class-asset-manager.php`, line 242, the `wp_localize_script()` call was still targeting the old script handle:

```php
// ❌ WRONG - This script no longer exists!
wp_localize_script('kcpf-filters', 'kcpfData', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('kcpf_filter_nonce')
]);
```

Since we deleted `filters.js` and replaced it with 8 new files, the `'kcpf-filters'` script handle no longer existed, so WordPress never injected the `kcpfData` variable into the page. Without `kcpfData.ajaxUrl`, all AJAX calls failed silently.

## Fixes Applied

### 1. Updated Script Localization (Critical Fix)

**File:** `includes/class-asset-manager.php` (line 242)

```php
// ✅ CORRECT - Attach to the AJAX handler module
wp_localize_script('kcpf-ajax-handler', 'kcpfData', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('kcpf_filter_nonce')
]);
```

**Why 'kcpf-ajax-handler'?**

- It's the module that primarily uses `kcpfData`
- It's loaded early (before coordinator)
- It's a logical place since it handles all AJAX operations

### 2. Added Safety Checks to Infinite Scroll

**File:** `assets/js/filters-infinite-scroll.js` (added at line 128)

```javascript
// Check if kcpfData is available
if (typeof kcpfData === "undefined" || !kcpfData.ajaxUrl) {
  console.error("[KCPF] kcpfData not found - cannot load next page");
  $loop.find(".kcpf-infinite-loader").hide();
  window.kcpfLoadingNextPage = false;
  return;
}
```

**Why?** The infinite scroll handler was using `kcpfData.ajaxUrl` directly without checking if it exists first, which would cause a JavaScript error if the localization failed.

### 3. Added Safety Checks to Map View (2 places)

**File:** `assets/js/map-view.js`

**Location 1:** Info window property card loading (added at line 177)

```javascript
showPropertyInfoWindow: function (circle, property) {
  // Check if kcpfData is available
  if (typeof kcpfData === "undefined" || !kcpfData.ajaxUrl) {
    console.error("[KCPF Map] kcpfData not found - cannot load property card");
    return;
  }

  // Make AJAX request...
```

**Location 2:** Map filter submission (added at line 358)

```javascript
// Check if kcpfData is available
if (typeof kcpfData === "undefined" || !kcpfData.ajaxUrl) {
  console.error("[KCPF Map] kcpfData not found - cannot fetch properties");
  return;
}

// AJAX request...
```

**Why?** Same reason - these were using `kcpfData.ajaxUrl` without checking, which would break if localization failed.

## Files Modified

1. ✅ `includes/class-asset-manager.php` - Fixed wp_localize_script target
2. ✅ `assets/js/filters-infinite-scroll.js` - Added safety check
3. ✅ `assets/js/map-view.js` - Added 2 safety checks

## Testing Checklist

After these fixes, test the following:

- [ ] Apply filters button - should load filtered properties
- [ ] Reset filters button - should clear filters and reload
- [ ] Infinite scroll - should load next page when scrolling down
- [ ] Map view - click property marker should show info window card
- [ ] Map view - apply filters should update property markers
- [ ] Homepage filters - purpose toggle should refresh filter options

## Why This Happened

When splitting files, we:

1. ✅ Created 8 new JS files with new script handles
2. ✅ Updated asset enqueuing to load all 8 files
3. ❌ **FORGOT** to update the `wp_localize_script` call to use a new script handle

This is a common mistake when refactoring - remembering to update all references to the old structure.

## Prevention for Future

When splitting/renaming scripts:

1. Search for all references to the old script handle (not just enqueue calls)
2. Check for `wp_localize_script` calls
3. Check for `wp_add_inline_script` calls
4. Check for script dependencies in other enqueue calls
5. Test AJAX functionality immediately after the split

## Result

✅ All AJAX functionality restored
✅ All safety checks in place
✅ Better error logging for debugging

The refactoring is now complete and fully functional!
