# Key CY Properties Filter

A custom WordPress plugin that provides a complete filtering system for properties with individual shortcodes for filters and the properties loop.

## Features

- ✅ Completely custom - no dependency on JetSmartFilters or other plugins
- ✅ Individual shortcodes for each filter type
- ✅ URL-based filtering (SEO-friendly)
- ✅ AJAX support (optional)
- ✅ Responsive design
- ✅ Clean, modular code following OOP principles

## Installation

1. Upload the `key-cy-properties-filter` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the shortcodes on any page or post

## Available Shortcodes

### Properties Loop

```
[properties_loop purpose="sale" posts_per_page="10"]
```

**Parameters:**

- `purpose` - Default purpose filter (default: 'sale')
- `posts_per_page` - Number of properties per page (default: 10)

### Filter Shortcodes

#### Location Filter

```
[property_filter_location type="select" placeholder="Select Location" show_count="false"]
```

#### Purpose Filter (Sale/Rent)

```
[property_filter_purpose type="toggle" default="sale"]
```

**Types:** `select`, `toggle`, `radio`

#### Price Range Filter

```
[property_filter_price type="inputs" min="0" max="10000000" step="10000"]
```

#### Bedrooms Filter

```
[property_filter_bedrooms type="select"]
```

**Types:** `select`, `buttons`

#### Bathrooms Filter

```
[property_filter_bathrooms type="select"]
```

**Types:** `select`, `buttons`

#### Property Type Filter

```
[property_filter_type type="select"]
```

**Types:** `select`, `checkboxes`

#### Apply Button

```
[property_filters_apply text="Apply Filters" type="reload"]
```

**Types:** `reload` (page reload), `ajax` (AJAX update)

#### Reset Button

```
[property_filters_reset text="Reset Filters"]
```

## Example Usage

### Basic Setup

```
<div class="property-filters">
    [property_filter_purpose type="toggle"]
    [property_filter_location type="select"]
    [property_filter_price]
    [property_filter_bedrooms type="buttons"]
    [property_filter_bathrooms type="buttons"]
    [property_filters_apply text="Search Properties"]
    [property_filters_reset]
</div>

<div class="property-results">
    [properties_loop]
</div>
```

### With AJAX

```
[property_filter_location type="select"]
[property_filter_price]
[property_filters_apply type="ajax" text="Apply"]
[properties_loop]
```

## Required Custom Fields & Taxonomies

The plugin expects the following WordPress setup:

### Post Type

- `properties`

### Taxonomies

- `location` - Property locations
- `purpose` - Sale/Rent
- `property-type` - Apartment, Villa, etc.

### Custom Fields (Post Meta)

- `price` - Property price (numeric)
- `bedrooms` - Number of bedrooms (numeric)
- `bathrooms` - Number of bathrooms (numeric)

## Customization

### Styling

Edit `/assets/css/filters.css` to customize the appearance of filters and properties.

### Templates

The HTML structure is defined in:

- `/includes/class-loop-renderer.php` - Properties loop HTML
- `/includes/class-filter-renderer.php` - Filter HTML

## URL Structure

Filters are applied via URL parameters:

```
/properties/?location=paphos&purpose=sale&price_min=100000&price_max=500000&bedrooms=3
```

This makes the filters:

- SEO-friendly
- Bookmarkable
- Shareable

## Support

For support, please contact Key CY.

## License

GPL v2 or later
