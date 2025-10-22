# Multi-Unit Property Support

## Overview

The plugin now supports **multi-unit properties** that have range-based pricing and area fields. This feature allows properties to specify minimum and maximum values for price, covered area, and plot area.

## How It Works

### Property Types

1. **Regular Properties** (default)

   - Single value fields: `price`, `total_covered_area`, `plot_area_land_only`
   - Multi-unit switcher: OFF or not set

2. **Multi-Unit Properties**
   - Range fields: `minimum_buy_price`, `maximum_buy_price`, `minimum_covered_area`, `maximum_covered_area`, `minimum_plot_area`, `maximum_plot_area`
   - Multi-unit switcher: ON (`multi-unit` = `1`)

### Filter Behavior

When users apply range filters (e.g., price €100,000 - €500,000):

#### For Regular Properties

- The single value must fall within the filter range
- Example: A property with `price = €300,000` matches the filter

#### For Multi-Unit Properties

- The property's minimum value must be >= filter minimum
- The property's maximum value must be <= filter maximum
- Example: A property with `minimum_buy_price = €200,000` and `maximum_buy_price = €400,000` matches the filter

### Supported Filters

The multi-unit feature applies to these range filters:

1. **Price Filter**

   - Regular field: `price` (or `rent_price` for rentals)
   - Multi-unit fields: `minimum_buy_price`, `maximum_buy_price`

2. **Covered Area Filter**

   - Regular field: `total_covered_area` (or `rent_area` for rentals)
   - Multi-unit fields: `minimum_covered_area`, `maximum_covered_area`

3. **Plot Area Filter**
   - Regular field: `plot_area_land_only`
   - Multi-unit fields: `minimum_plot_area`, `maximum_plot_area`

## Technical Implementation

### New Files

- `includes/class-multiunit-query-builder.php` - Handles query building for both regular and multi-unit properties

### Modified Files

- `includes/class-query-handler.php` - Updated to delegate range queries to the multi-unit query builder
- `key-cy-properties-filter.php` - Updated to load the new multi-unit query builder class

### Architecture

The implementation follows the single responsibility principle:

1. **KCPF_MultiUnit_Query_Builder** - Responsible for:

   - Building OR queries that handle both property types
   - Creating regular property queries (excluding multi-unit properties)
   - Creating multi-unit property queries (including multi-unit properties)

2. **KCPF_Query_Handler** - Delegates range filter building to the multi-unit query builder

### Query Structure

For each range filter, the system creates an OR query:

```php
[
    'relation' => 'OR',

    // Regular properties
    [
        'relation' => 'AND',
        // multi-unit is NOT set or is false
        // value comparison
    ],

    // Multi-unit properties
    [
        'relation' => 'AND',
        // multi-unit = 1
        // minimum value comparison
        // maximum value comparison
    ]
]
```

## Usage Example

### Setting Up a Multi-Unit Property

1. Enable the "multi-unit" switcher on the property
2. Populate the range fields:
   - `minimum_buy_price`: €200,000
   - `maximum_buy_price`: €500,000
   - `minimum_covered_area`: 80 m²
   - `maximum_covered_area`: 150 m²
   - `minimum_plot_area`: 200 m²
   - `maximum_plot_area`: 400 m²

### Filter Results

When a user filters for:

- Price: €100,000 - €600,000
- Covered Area: 50 - 200 m²

The property will appear in results because:

- Minimum price (€200,000) >= filter minimum (€100,000) ✓
- Maximum price (€500,000) <= filter maximum (€600,000) ✓
- Minimum area (80 m²) >= filter minimum (50 m²) ✓
- Maximum area (150 m²) <= filter maximum (200 m²) ✓

## Code Quality

All code follows the project's architectural principles:

- ✅ Files under 500 lines
- ✅ Single responsibility principle
- ✅ Object-oriented design with dedicated classes
- ✅ Modular and reusable components
- ✅ Clear separation of concerns

## Backwards Compatibility

The feature is **fully backwards compatible**:

- Existing properties without the multi-unit switcher continue to work as before
- No database migrations required
- No changes to existing shortcodes or frontend code
