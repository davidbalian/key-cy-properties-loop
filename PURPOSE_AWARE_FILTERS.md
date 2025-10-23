# Purpose-Aware Filters Implementation

## Overview

All filters now detect whether the loop on the page is for sale or rent properties, and filter their values accordingly.

## Changes Made

### 1. New Helper Method: `getTermsByPurpose()`

**File:** `includes/class-filter-renderer.php`

Added a private static method that filters taxonomy terms to only include those that have properties for a given purpose (sale or rent).

**How it works:**

- Queries each term to check if it has any properties with the specified purpose
- Returns only terms that have matching properties
- Ensures filters only show relevant options

### 2. Updated Location Filter

**File:** `includes/class-filter-renderer.php` (line 88-91)

The location filter now:

- Gets the purpose from URL parameters or defaults to 'sale'
- Uses `getTermsByPurpose()` to filter locations
- Only shows locations that have properties for the current purpose

**Before:** Showed all locations regardless of purpose
**After:** Shows only locations with properties matching the current purpose

### 3. Updated Property Type Filter

**File:** `includes/class-filter-renderer.php` (line 544-547)

The property type filter now:

- Gets the purpose from URL parameters or defaults to 'sale'
- Uses `getTermsByPurpose()` to filter property types
- Only shows types that have properties for the current purpose

**Before:** Showed all property types regardless of purpose
**After:** Shows only property types with properties matching the current purpose

### 4. Already Purpose-Aware Filters

These filters were already purpose-aware and didn't need changes:

- **Price Filter:** Uses `KCPF_Listing_Values::getMinMax('price', $purpose)` to get min/max values from actual properties
- **Covered Area Filter:** Uses `KCPF_Listing_Values::getMinMax('covered_area', $purpose)` to get min/max values
- **Plot Area Filter:** Uses `KCPF_Listing_Values::getMinMax('plot_area', $purpose)` to get min/max values

### 5. Purpose-Agnostic Filters

These filters don't need purpose filtering:

- **Bedrooms/Bathrooms:** Options come from JetEngine glossaries and use purpose-aware meta keys when querying
- **Amenities:** Options come from JetEngine glossaries and apply to all properties
- **Property ID:** Search field that doesn't need filtering

## How Purpose Detection Works

1. **Priority Order:**

   - URL parameter (`?purpose=sale` or `?purpose=rent`)
   - Shortcode attribute (`[properties_loop purpose="sale"]`)
   - Default: 'sale'

2. **JavaScript Integration:**

   - JavaScript already handles purpose detection and passing
   - Detects purpose from form inputs or loop's `data-purpose` attribute
   - Passes purpose parameter in AJAX requests

3. **Loop Context:**
   - Each loop includes `data-purpose` attribute for identification
   - Filters use this context to determine current purpose

## Benefits

1. **Better User Experience:** Users only see relevant filter options
2. **Accurate Results:** Filter values come from actual properties in the current context
3. **Dynamic Adaptation:** Filters automatically adjust when switching between sale and rent
4. **Performance:** Fewer irrelevant options to parse and display

## Example Flow

### Sale Properties Page:

1. Page loads with `purpose=sale` parameter
2. Location filter shows only locations with sale properties
3. Property type filter shows only types with sale properties
4. Price range is based on sale property prices
5. Other filters use sale-specific meta keys

### Rent Properties Page:

1. Page loads with `purpose=rent` parameter
2. Location filter shows only locations with rent properties
3. Property type filter shows only types with rent properties
4. Price range is based on rent property prices
5. Other filters use rent-specific meta keys

## Technical Notes

- The implementation follows the single responsibility principle
- Uses composition (helper method) rather than duplication
- Maintains backward compatibility with existing shortcodes
- No changes required to existing filter usage
- JavaScript already handles purpose propagation correctly
