# Properties Map View Guide

## Overview

The Map View feature provides an interactive Google Maps-based property browser with filters, property cards, and bidirectional interactivity between the map and property listings.

## Features

- **Interactive Google Map** with property markers
- **100m radius circles** around each property location
- **Marker clustering** for nearby properties
- **Two-column layout**: Property cards (left) + Map (right)
- **Purpose-aware filters** (sale/rent)
- **Bidirectional interactivity**:
  - Hover on card → Map pans to marker
  - Click on marker → Card highlights and scrolls into view
- **AJAX filtering** for real-time updates
- **Responsive design** (mobile-friendly)

## Setup Instructions

### 1. Configure Google Maps API Key

1. Go to **Settings > Properties Map** in WordPress admin
2. Follow the instructions to obtain a Google Maps API Key
3. Enter your API key and save

**Required Google Maps APIs:**

- Maps JavaScript API

**Recommended API Key Restrictions:**

- HTTP referrers (restrict to your domain)
- API restrictions (Maps JavaScript API only)

### 2. Add Shortcode to Page

Use one of the following shortcodes on your page:

```php
// For sale properties
[properties_map purpose="sale"]

// For rent properties
[properties_map purpose="rent"]
```

## Shortcode Reference

### `[properties_map]`

Renders an interactive map view with filters and property cards.

**Attributes:**

- `purpose` (required) - Property purpose: `sale` or `rent`
  - Default: `sale`

**Examples:**

```php
[properties_map purpose="sale"]
[properties_map purpose="rent"]
```

## Feature Details

### Filters

The map view includes the following filters:

1. **Property Type** - Multi-select dropdown
2. **Bedrooms** - Multi-select checkbox
3. **Bathrooms** - Multi-select checkbox
4. **Location** - Multi-select dropdown with property counts
5. **Amenities** - Multi-select checkbox
6. **Price Range** - Slider
7. **Covered Area** - Slider
8. **Plot Area** - Slider
9. **Property ID** - Search field
10. **Apply** - Submit filters
11. **Reset** - Clear all filters

All filters are **purpose-aware** and show only relevant values for the current purpose (sale or rent).

### Map Interactions

**Marker Click:**

- Pans map to marker location
- Zooms to level 16
- Shows info window with property title and link
- Highlights corresponding card in sidebar
- Scrolls card into view

**Card Hover:**

- Pans map to property marker
- Zooms to level 16
- Shows info window (optional)

**Marker Clustering:**

- Nearby markers automatically cluster
- Cluster shows count of properties
- Click cluster to zoom in

**100m Radius Circle:**

- Blue circle around each marker
- Shows approximate property area
- 100 meters radius

### Property Cards

Each card displays:

- Property image
- Title
- City area and location
- Property type
- Price (or "From €X" for multi-unit)
- Bedrooms and bathrooms count

Cards support:

- **Hover effect** - Pans map to property
- **Active state** - Highlighted when marker is clicked
- **Click** - Navigate to property detail page

### Responsive Design

**Desktop (>1024px):**

- Two-column layout: 1fr (cards) : 2fr (map)
- Filters in multi-column grid

**Tablet (768px - 1024px):**

- Two-column layout: 1fr (cards) : 1.5fr (map)
- Filters in two columns

**Mobile (<768px):**

- Single-column stacked layout
- Filters in single column
- Cards section max height: 400px
- Map min height: 400px

## Technical Details

### Coordinates Format

Properties must have the `display_coordinates` meta field in the format:

```
latitude,longitude
```

Example:

```
35.1264,33.4299
```

**Note:** Properties without coordinates will be excluded from the map.

### Performance

- **Initial load:** Maximum 50 properties
- **AJAX filtering:** Real-time updates
- **Marker clustering:** Improves performance with many markers
- **Lazy loading:** Map only loads when shortcode is present

### File Structure

```
key-cy-properties-loop/
├── includes/
│   ├── class-settings-manager.php       (Settings page)
│   ├── class-map-filters.php            (Filter rendering)
│   ├── class-map-card-renderer.php      (Card rendering)
│   └── class-map-shortcode.php          (Main shortcode)
├── assets/
│   ├── css/
│   │   └── map-view.css                 (Map styles)
│   └── js/
│       └── map-view.js                  (Map JavaScript)
```

### AJAX Endpoints

**Action:** `kcpf_load_map_properties`

**Parameters:**

- `purpose` - sale/rent
- All filter parameters (location[], property_type[], bedrooms[], etc.)

**Response:**

```json
{
  "success": true,
  "data": {
    "count": 25,
    "cards_html": "<div>...</div>",
    "properties_data": [
      {
        "id": 123,
        "title": "Property Title",
        "lat": 35.1264,
        "lng": 33.4299,
        "url": "https://..."
      }
    ]
  }
}
```

## Customization

### Styling

The map view uses CSS classes prefixed with `kcpf-map-`:

```css
.kcpf-map-view           /* Main container */
/* Main container */
.kcpf-map-layout         /* Two-column grid */
.kcpf-map-sidebar        /* Left column */
.kcpf-map-container      /* Right column (map) */
.kcpf-map-card           /* Property card */
.kcpf-map-card-active    /* Active/selected card */
.kcpf-google-map; /* Map element */
```

Add custom CSS to your theme's stylesheet or a child theme.

### JavaScript Customization

The map is controlled by the `KCPFMapView` object in `map-view.js`.

**Available callbacks:**

- `window.kcpfInitMap()` - Called when Google Maps API loads

## Troubleshooting

### Map Not Displaying

**Check:**

1. Google Maps API key is configured (Settings > Properties Map)
2. API key is valid and active
3. Maps JavaScript API is enabled in Google Cloud Console
4. No JavaScript console errors

### No Markers on Map

**Check:**

1. Properties have `display_coordinates` meta field
2. Coordinates are in correct format: `lat,lng`
3. Coordinates are valid (not 0,0)
4. Properties match the current purpose (sale/rent)

### Clustering Not Working

**Check:**

1. MarkerClusterer library is loading
2. Multiple properties are close together
3. No JavaScript console errors

### Filters Not Working

**Check:**

1. AJAX endpoint is accessible (check Network tab)
2. Filter form has correct purpose attribute
3. No JavaScript console errors
4. Filters have values to select

## Browser Compatibility

- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support
- Mobile browsers: Full support

**Minimum requirements:**

- JavaScript enabled
- Modern browser (ES6 support)
- Internet connection (for Google Maps API)

## Known Limitations

1. **Maximum properties:** 50 per load (for performance)
2. **API quotas:** Subject to Google Maps API usage limits
3. **Coordinates required:** Properties without coordinates are excluded
4. **Internet required:** Google Maps requires active connection

## Support

For issues or questions, check:

1. JavaScript console for errors
2. WordPress debug log
3. Google Maps API quota/billing
4. Network tab for failed requests

## Credits

- **Google Maps API** - Map rendering
- **MarkerClusterer** - Marker clustering
- **WordPress** - CMS platform
- **jQuery** - DOM manipulation
