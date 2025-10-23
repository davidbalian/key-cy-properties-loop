# Map View Test Page Template

## Quick Test Setup

Copy and paste one of these templates into a WordPress page to test the map view.

---

## Test Page 1: Sale Properties Map

```
<!-- Sale Properties Map View -->
<h1>Properties for Sale - Map View</h1>

[properties_map purpose="sale"]
```

**Expected Result:**

- Map displays with sale properties
- Filters show sale-specific values (price range, property types, locations with sale properties)
- Property cards show sale properties only
- Markers appear at property locations with 100m radius circles

---

## Test Page 2: Rent Properties Map

```
<!-- Rent Properties Map View -->
<h1>Properties for Rent - Map View</h1>

[properties_map purpose="rent"]
```

**Expected Result:**

- Map displays with rent properties
- Filters show rent-specific values (monthly rent prices, property types, locations with rent properties)
- Property cards show rent properties only
- Markers appear at property locations with 100m radius circles

---

## Test Page 3: Both Sale and Rent (Separate Sections)

```
<!-- Combined Page with Both Purposes -->
<h1>All Properties</h1>

<h2>For Sale</h2>
[properties_map purpose="sale"]

<hr style="margin: 60px 0;">

<h2>For Rent</h2>
[properties_map purpose="rent"]
```

**Expected Result:**

- Two separate map views on the same page
- Each map shows only properties of its purpose
- Filters work independently for each section

---

## Pre-Flight Checklist

Before testing, ensure:

### ✅ 1. Google Maps API Key Configured

1. Go to **WordPress Admin > Settings > Properties Map**
2. Enter your Google Maps API key
3. Click "Save Settings"

**How to get API key:**

- Visit: https://console.cloud.google.com/
- Create/select project
- Enable "Maps JavaScript API"
- Create API key under Credentials
- Restrict to your domain

### ✅ 2. Test Properties Have Coordinates

Run this check in your database or via phpMyAdmin:

```sql
SELECT
    p.ID,
    p.post_title,
    pm.meta_value as coordinates
FROM wp_posts p
LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = 'display_coordinates'
WHERE p.post_type = 'properties'
AND p.post_status = 'publish'
LIMIT 10;
```

**Expected:** Each property should have coordinates in format: `"35.1264,33.4299"`

**If missing coordinates:**

1. Edit a property in WordPress
2. Add custom field: `display_coordinates`
3. Value format: `latitude,longitude` (e.g., `35.1264,33.4299`)

### ✅ 3. Properties Have Purpose Taxonomy

Verify properties are assigned to "sale" or "rent" taxonomy terms.

### ✅ 4. Plugin is Active

Ensure "Key CY Properties Filters and Loops" plugin is activated.

---

## Testing Procedure

### Step 1: Basic Display

1. Create new page
2. Add shortcode: `[properties_map purpose="sale"]`
3. Publish page
4. View page on frontend

**✓ Verify:**

- [ ] Map displays without errors
- [ ] Property cards appear in left column
- [ ] Map displays in right column
- [ ] Markers appear on map
- [ ] Blue circles (100m radius) around each marker

### Step 2: Test Filters

**Apply Individual Filters:**

1. **Property Type Filter**

   - Select a property type
   - Click "Apply Filters"
   - Verify: Map updates to show only that type

2. **Location Filter**

   - Select a location
   - Click "Apply Filters"
   - Verify: Map updates to show only that location

3. **Bedrooms Filter**

   - Select bedroom count
   - Click "Apply Filters"
   - Verify: Properties match selection

4. **Price Range Filter**

   - Adjust price slider
   - Click "Apply Filters"
   - Verify: Properties within price range

5. **Reset Button**
   - Click "Reset"
   - Verify: All filters clear, all properties show

**✓ Expected:**

- [ ] Filters update property cards
- [ ] Map markers update simultaneously
- [ ] Results count updates
- [ ] No page reload (AJAX)

### Step 3: Test Map Interactions

**Marker Click:**

1. Click any marker on map
2. **Verify:**
   - [ ] Map pans to marker
   - [ ] Map zooms to level 16
   - [ ] Info window appears with property title
   - [ ] Corresponding card highlights (blue border)
   - [ ] Card scrolls into view in sidebar

**Card Hover:**

1. Hover mouse over a property card
2. **Verify:**
   - [ ] Map pans to that property's marker
   - [ ] Map zooms to marker location

**Marker Clustering:**

1. Zoom out on map
2. **Verify:**
   - [ ] Nearby markers cluster into numbered icons
   - [ ] Cluster shows count
   - [ ] Click cluster zooms in
   - [ ] Markers separate when zoomed in

### Step 4: Test Responsive Design

**Desktop View (>1024px):**

- [ ] Two columns: cards (1fr) and map (2fr)
- [ ] Filters in multi-column grid

**Tablet View (768px - 1024px):**

- [ ] Two columns: cards (1fr) and map (1.5fr)
- [ ] Filters in two columns

**Mobile View (<768px):**

- [ ] Single column (stacked)
- [ ] Cards section scrollable (max 400px)
- [ ] Map below cards (min 400px)
- [ ] Filters in single column

**Test by:**

- Resizing browser window, or
- Using browser dev tools responsive mode, or
- Testing on actual mobile device

### Step 5: Test Edge Cases

**No Results:**

1. Apply filters that match no properties
2. **Verify:**
   - [ ] "No properties found" message displays
   - [ ] Map shows no markers
   - [ ] No JavaScript errors

**Properties Without Coordinates:**

1. View page with some properties missing coordinates
2. **Verify:**
   - [ ] Only properties with coordinates appear
   - [ ] No errors in console
   - [ ] Cards still display (but those properties excluded from map)

**No API Key:**

1. Temporarily remove API key (Settings > Properties Map)
2. View map page
3. **Verify (as admin):**
   - [ ] Warning message appears
   - [ ] Link to settings page
   - [ ] No map displayed
4. **Verify (as visitor):**
   - [ ] Generic error message (no settings link)

---

## Browser Testing

Test in these browsers:

- [ ] Chrome (Windows/Mac)
- [ ] Firefox (Windows/Mac)
- [ ] Safari (Mac/iOS)
- [ ] Edge (Windows)
- [ ] Mobile Safari (iOS)
- [ ] Mobile Chrome (Android)

---

## Common Issues & Solutions

### Issue: Map Doesn't Display

**Solutions:**

1. Check API key is configured (Settings > Properties Map)
2. Check browser console for errors
3. Verify Google Maps API is enabled in Cloud Console
4. Check API key has no usage limits hit
5. Verify API key restrictions allow your domain

### Issue: No Markers on Map

**Solutions:**

1. Verify properties have `display_coordinates` meta field
2. Check coordinates format is correct: `"lat,lng"`
3. Verify coordinates are valid (not `0,0` or empty)
4. Check browser console for JavaScript errors
5. Inspect property data JSON in page source (`kcpf-map-properties-data`)

### Issue: Filters Not Working

**Solutions:**

1. Check browser console for AJAX errors
2. Verify AJAX URL is correct (Network tab)
3. Check filter form has correct purpose attribute
4. Verify properties exist matching filter criteria
5. Test with different filter combinations

### Issue: Card/Marker Interaction Not Working

**Solutions:**

1. Check browser console for JavaScript errors
2. Verify jQuery is loaded
3. Check map-view.js is enqueued
4. Verify property cards have `data-property-id` attribute
5. Test with browser dev tools to inspect event listeners

### Issue: Clustering Not Working

**Solutions:**

1. Check if MarkerClusterer library is loading
2. Verify multiple markers are close together
3. Check browser console for errors
4. Try zooming out to see clusters
5. Inspect network tab for CDN request

---

## Success Criteria

✅ **All Tests Pass If:**

1. Map displays correctly with all property markers
2. All 9 filters work and update results via AJAX
3. Hover on card pans map to marker
4. Click on marker highlights card
5. Marker clustering works when zoomed out
6. 100m radius circles display around markers
7. Responsive design works on all screen sizes
8. No JavaScript errors in console
9. No PHP errors in debug log
10. Smooth performance (no lag or delays)

---

## Debug Mode

Enable WordPress debug mode to see detailed errors:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check debug log at: `wp-content/debug.log`

---

## Browser Console Commands

Test JavaScript functionality directly:

```javascript
// Check if map is initialized
console.log(window.KCPFMapView);

// Check properties data
console.log(
  JSON.parse(document.getElementById("kcpf-map-properties-data").textContent)
);

// Check Google Maps API
console.log(typeof google !== "undefined" && google.maps);

// Check markers
console.log(window.KCPFMapView.markers);

// Check if MarkerClusterer is available
console.log(typeof MarkerClusterer);
```

---

## Need Help?

1. **Check documentation:** MAP_VIEW_GUIDE.md
2. **Check implementation:** MAP_VIEW_IMPLEMENTATION.md
3. **Check browser console** for JavaScript errors
4. **Check WordPress debug log** for PHP errors
5. **Check Network tab** for failed AJAX requests

---

## Report Results

After testing, document:

✅ **Working Features:**

- (List what works)

❌ **Issues Found:**

- (List any problems with details)

📝 **Notes:**

- (Any observations or suggestions)
