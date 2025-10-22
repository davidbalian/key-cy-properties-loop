# Rent Card View Implementation

## Overview

Created a dedicated view for rent property cards with a single column layout and responsive grid system.

**Recent Updates:**

- Featured images set to 250px height with object-cover
- Title font size standardized to 20px for both sale and rent cards
- Added icons for bedrooms, bathrooms, and area to both card types

## Changes Made

### 1. New Class: `class-rent-card-view.php`

Created a new dedicated class `KCPF_Rent_Card_View` following the single responsibility principle. This class handles only the rendering of rent property cards.

**Features:**

- Single column layout (vertical stacking)
- Featured image at the top
- Title
- Location + city_area | property_type
- rent_bedrooms + rent_bathrooms + rent_area
- rent_price/mo

### 2. Updated `class-loop-renderer.php`

- Removed the old `renderRentCard()` method
- Added logic to use `KCPF_Rent_Card_View::render()` for rent properties
- Added `kcpf-grid-rent` class to the grid container for rent properties

### 3. Updated `key-cy-properties-filter.php`

- Added the new rent card view class to the dependency loading

### 4. Updated `assets/css/filters.css`

- Added responsive grid layout for rent properties:

  - **Desktop (default)**: 3 columns
  - **Tablet (≤1024px)**: 2 columns
  - **Mobile (≤768px)**: 1 column

- Added CSS styles for rent card components:
  - `.kcpf-property-card-rent` - Card container
  - `.kcpf-property-image-rent` - Featured image (250px height, object-cover)
  - `.kcpf-property-content-rent` - Content container
  - `.kcpf-property-title-rent` - Title styling (20px font size)
  - `.kcpf-property-meta-row-rent` - Location/area/type row
  - `.kcpf-property-specs-rent` - Bedrooms/bathrooms/area specs with icons
  - `.kcpf-property-price-rent` - Price with "/mo" suffix
  - `.kcpf-property-specs-icon` and `.kcpf-property-specs-rent-icon` - Icon wrappers

## Card Structure

The rent card displays information in this order (top to bottom):

1. **Featured Image** - Full width image at the top (250px height, object-cover)
2. **Title** - Property title as a link (20px font size)
3. **Meta Row** - Location | City Area | Property Type
4. **Specs** - Bedrooms 🛏️ | Bathrooms 🚿 | Area 📐 (with icons and separator line)
5. **Price** - €X,XXX/mo (formatted with "/mo" suffix)

**Icons Used:**

- 🛏️ Bedrooms icon
- 🚿 Bathrooms icon
- 📐 Area icon (floor plan)

## Responsive Behavior

- **Desktop**: 3-column grid layout
- **Tablet**: 2-column grid layout
- **Mobile**: 1-column grid layout

## Usage

The rent card view is automatically used when displaying properties with `purpose=rent` in the `[properties_loop]` shortcode.

```php
[properties_loop purpose="rent"]
```

The grid automatically applies the correct responsive layout based on the purpose attribute.
