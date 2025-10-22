# Dynamic Filter Values Implementation

## Overview

All filter sliders now dynamically get their min/max values from actual listings instead of using hardcoded values.

## Changes Made

### 1. New Class: `class-listing-values.php`

Created a new class `KCPF_Listing_Values` that:

- Queries actual listings to get min/max values for numeric fields
- Supports purpose-based field mapping (sale vs rent)
- Implements caching to improve performance
- Falls back to sensible defaults if no listings exist

### 2. Updated Filter Renderers

Modified the following filter methods in `class-filter-renderer.php`:

#### `renderPrice()`

- Now gets min/max from actual price values in listings
- Dynamically adjusts based on purpose (sale uses `price`, rent uses `rent_price`)

#### `renderCoveredArea()`

- Now gets min/max from actual covered area values in listings
- Dynamically adjusts based on purpose (sale uses `total_covered_area`, rent uses `rent_area`)

#### `renderPlotArea()`

- Now gets min/max from actual plot area values in listings
- Uses `plot_area_land_only` meta key

### 3. Updated Main Plugin File

- Added `class-listing-values.php` to the dependency loading

## How It Works

### Query Process

1. When a filter is rendered, it calls `KCPF_Listing_Values::getMinMax($field, $purpose)`
2. The method queries the database for min/max values from published properties
3. Results are cached to avoid repeated queries
4. If no data exists, sensible defaults are returned

### Purpose-Aware

The system automatically adjusts field names based on purpose:

- **Sale properties**: `price`, `total_covered_area`
- **Rent properties**: `rent_price`, `rent_area`
- **Plot area**: `plot_area_land_only` (same for both)

### Multi-Unit Support

Works seamlessly with multi-unit properties since they use the same meta keys as regular properties.

## Benefits

1. **Dynamic Range**: Sliders always show the actual range of available listings
2. **Purpose-Specific**: Different ranges for sale vs rent properties
3. **Performance**: Results are cached to minimize database queries
4. **Accuracy**: Users see only the values that actually exist in listings
5. **Future-Proof**: Automatically adapts as new listings are added

## Technical Details

### Cache Key Format

`{field}_{purpose}` - e.g., `price_sale`, `covered_area_rent`

### Default Values

If no listings exist or query fails:

- Price: 0 - 10,000,000
- Covered Area: 0 - 10,000
- Plot Area: 0 - 50,000

### Database Query

Uses efficient SQL query with CAST to handle numeric values:

```sql
SELECT MIN(CAST(meta_value AS UNSIGNED)) as min_value,
       MAX(CAST(meta_value AS UNSIGNED)) as max_value
FROM postmeta
WHERE meta_key = '{field_key}'
AND post_type = 'properties'
AND post_status = 'publish'
```

## Usage Example

```php
// Get min/max for price field in sale properties
$range = KCPF_Listing_Values::getMinMax('price', 'sale');
// Returns: ['min' => 50000, 'max' => 5000000]

// Get min/max for covered area in rent properties
$range = KCPF_Listing_Values::getMinMax('covered_area', 'rent');
// Returns: ['min' => 50, 'max' => 500]
```

## Cache Management

To clear the cache (e.g., after bulk importing listings):

```php
KCPF_Listing_Values::clearCache();
```
