# Mega Filters Usage Guide

## Overview

The `[mega_filters]` shortcode provides a comprehensive filter interface that combines all available property filters in a specific order. It automatically detects and works with properties loops on the current page.

## Filter Order

The mega filters display filters in this exact order:

1. **Type** (Property Type) - Checkbox multiselect
2. **Location** - Checkbox multiselect with property counts
3. **Bedrooms** - Checkbox multiselect
4. **Bathrooms** - Checkbox multiselect
5. **Price** - Range slider with purpose-aware min/max
6. **Covered Area** - Range slider (m²)
7. **Amenities** - Checkbox multiselect
8. **Land Area** - Range slider (plot area, renamed from "Plot Area")
9. **Search by ID** - Text input for property ID search

## Basic Usage

### Simple Implementation

```php
[mega_filters]

[properties_loop posts_per_page="12"]
```

### With Custom Button Text

```php
[mega_filters apply_text="Search Properties" reset_text="Clear All"]

[properties_loop purpose="sale" posts_per_page="9"]
```

### Hide Buttons

```php
[mega_filters show_apply="false" show_reset="true"]

[properties_loop purpose="rent" posts_per_page="15"]
```

## Features

- **Purpose-Aware**: Automatically adjusts filter options based on sale/rent context
- **URL Integration**: All filters read from and write to URL parameters
- **Loop Detection**: Automatically works with `[properties_loop]` on the same page
- **Form Submission**: Reloads the page with filter parameters applied
- **Responsive Design**: Works on all screen sizes

## URL Parameters

The mega filters use these URL parameters:

- `purpose` - sale or rent (default: sale)
- `property_type[]` - Array of selected property types
- `location[]` - Array of selected locations
- `bedrooms[]` - Array of selected bedroom counts
- `bathrooms[]` - Array of selected bathroom counts
- `price_min` - Minimum price
- `price_max` - Maximum price
- `covered_area_min` - Minimum covered area (m²)
- `covered_area_max` - Maximum covered area (m²)
- `amenities[]` - Array of selected amenities
- `plot_area_min` - Minimum land area (m²)
- `plot_area_max` - Maximum land area (m²)
- `property_id` - Specific property ID to search for

## CSS Classes

The mega filters container uses these CSS classes:

- `.kcpf-mega-filters` - Main container
- `.kcpf-filters-form` - Form wrapper
- Individual filters use their standard classes (`.kcpf-filter-location`, etc.)

## Example Complete Implementation

```php
<div class="property-search-container">
    <div class="search-filters">
        [mega_filters apply_text="Find Properties" reset_text="Reset Search"]
    </div>

    <div class="search-results">
        [properties_loop posts_per_page="12"]
    </div>
</div>
```

## Integration with Existing Systems

The mega filters work seamlessly with:

- **Properties Loop**: `[properties_loop]`
- **Map View**: `[properties_map]`
- **Individual Filters**: All existing filter shortcodes
- **AJAX Filtering**: Supports AJAX filter updates
- **Purpose Switching**: Automatically adapts to sale/rent context

## Browser Compatibility

The mega filters use modern CSS and JavaScript features and are compatible with:

- Chrome/Edge 88+
- Firefox 85+
- Safari 14+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance Notes

- Filters are rendered server-side for better SEO
- Range sliders use client-side JavaScript for interactivity
- Form submission uses standard browser navigation
- All filter values are cached and optimized for performance
