# Property Filter Shortcodes - Quick Reference

## Properties Loop

### `[properties_loop]`

Displays the filtered properties grid.

**Attributes:**

- `purpose` (string) - Default purpose filter: `sale` or `rent` (default: `sale`)
- `posts_per_page` (int) - Number of properties per page (default: `10`)

**Examples:**

```
[properties_loop]
[properties_loop purpose="rent" posts_per_page="12"]
[properties_loop purpose="sale" posts_per_page="6"]
```

---

## Filter Shortcodes

### `[property_filter_location]`

Location taxonomy filter.

**Attributes:**

- `type` (string) - Display type: `select` or `checkbox` (default: `select`)
- `placeholder` (string) - Placeholder text for select (default: `Select Location`)
- `show_count` (bool) - Show property count (default: `false`)

**Examples:**

```
[property_filter_location]
[property_filter_location type="checkbox" show_count="true"]
```

### `[homepage_filters]`

Composite, purpose-aware homepage filter with redirect.

**Attributes:**

- `sale_url` (string) - Target URL when purpose is `sale` (default: `/test-sale-archive`)
- `rent_url` (string) - Target URL when purpose is `rent` (default: `/test-rent-page`)
- `apply_text` (string) - Button text (default: `Filter results`)

Includes:

- Purpose radio (sale/rent)
- Property type (purpose-filtered)
- Location with purpose-specific counts
- Bedrooms
- Price range slider with purpose-specific min/max

Behavior:

- When toggling purpose, type/location options and price min/max refresh live
- Clicking "Filter results" redirects to the purpose-specific URL with all selections added to the query string

**Example:**

```
[homepage_filters sale_url="/test-sale-archive" rent_url="/test-rent-page" apply_text="Filter results"]
```

---

### `[property_filter_purpose]`

Purpose filter (Sale/Rent).

**Attributes:**

- `type` (string) - Display type: `select`, `toggle`, or `radio` (default: `select`)
- `default` (string) - Default selected value: `sale` or `rent` (default: `sale`)

**Examples:**

```
[property_filter_purpose type="toggle"]
[property_filter_purpose type="radio" default="rent"]
```

---

### `[property_filter_price]`

Price range filter with slider.

**Attributes:**

- `type` (string) - Display type: `slider` (default: `slider`)
- `min` (int) - Minimum price value (default: `0`)
- `max` (int) - Maximum price value (default: `10000000`)
- `step` (int) - Step increment (default: `10000`)

**Examples:**

```
[property_filter_price]
[property_filter_price min="100000" max="5000000" step="50000"]
[property_filter_price min="500" max="5000" step="100"]
```

---

### `[property_filter_bedrooms]`

Bedrooms filter (uses JetEngine Bedrooms glossary).

**Attributes:**

- `type` (string) - Display type: `select`, `checkbox`, or `buttons` (default: `checkbox`)

**Examples:**

```
[property_filter_bedrooms]
[property_filter_bedrooms type="select"]
[property_filter_bedrooms type="buttons"]
```

---

### `[property_filter_bathrooms]`

Bathrooms filter (uses JetEngine Bathrooms glossary).

**Attributes:**

- `type` (string) - Display type: `select`, `checkbox`, or `buttons` (default: `checkbox`)

**Examples:**

```
[property_filter_bathrooms]
[property_filter_bathrooms type="select"]
[property_filter_bathrooms type="buttons"]
```

---

### `[property_filter_type]`

Property type taxonomy filter.

**Attributes:**

- `type` (string) - Display type: `select` or `checkbox` (default: `select`)

**Examples:**

```
[property_filter_type]
[property_filter_type type="checkbox"]
```

---

### `[property_filter_amenities]`

Amenities filter (uses JetEngine Amenities glossary).

**Attributes:**

- `type` (string) - Display type: `checkbox` (default: `checkbox`)

**Examples:**

```
[property_filter_amenities]
```

---

### `[property_filter_covered_area]`

Covered area range filter with slider.

**Attributes:**

- `min` (int) - Minimum area value in m² (default: `0`)
- `max` (int) - Maximum area value in m² (default: `10000`)
- `step` (int) - Step increment (default: `10`)

**Examples:**

```
[property_filter_covered_area]
[property_filter_covered_area min="50" max="1000" step="5"]
```

**Note:** Automatically uses `total_covered_area` for sale properties and `rent_area` for rent properties.

---

### `[property_filter_plot_area]`

Plot area range filter with slider.

**Attributes:**

- `min` (int) - Minimum area value in m² (default: `0`)
- `max` (int) - Maximum area value in m² (default: `50000`)
- `step` (int) - Step increment (default: `50`)

**Examples:**

```
[property_filter_plot_area]
[property_filter_plot_area min="100" max="10000" step="100"]
```

---

### `[property_filter_id]`

Property ID search filter.

**Attributes:**

- `placeholder` (string) - Placeholder text (default: `Search by Property ID`)

**Examples:**

```
[property_filter_id]
[property_filter_id placeholder="Enter Property ID"]
```

---

### `[property_filters_apply]`

Apply/Submit button.

**Attributes:**

- `text` (string) - Button text (default: `Apply Filters`)
- `type` (string) - Submission type: `reload` or `ajax` (default: `reload`)

**Examples:**

```
[property_filters_apply]
[property_filters_apply text="Search Properties"]
[property_filters_apply type="ajax" text="Update Results"]
```

---

### `[property_filters_reset]`

Reset/Clear filters button.

**Attributes:**

- `text` (string) - Button text (default: `Reset Filters`)

**Examples:**

```
[property_filters_reset]
[property_filters_reset text="Clear All"]
```

---

## Complete Filter Examples

### Minimal Filter (Sale Properties)

```
[property_filter_location]
[property_filter_price]
[property_filter_bedrooms]
[property_filters_apply]

[properties_loop purpose="sale"]
```

### Minimal Filter (Rent Properties)

```
[property_filter_location]
[property_filter_price min="300" max="5000" step="50"]
[property_filter_bedrooms]
[property_filters_apply]

[properties_loop purpose="rent"]
```

### Complete Filter (All Options)

```
<div class="property-filters-wrapper">
    [property_filter_purpose type="toggle"]
    [property_filter_location type="select"]
    [property_filter_type type="checkbox"]
    [property_filter_price min="0" max="10000000" step="50000"]
    [property_filter_bedrooms type="checkbox"]
    [property_filter_bathrooms type="checkbox"]
    [property_filter_amenities]
    [property_filter_covered_area min="0" max="1000" step="10"]
    [property_filter_plot_area min="0" max="20000" step="100"]
    [property_filter_id]

    <div class="filter-buttons">
        [property_filters_apply text="Search Properties"]
        [property_filters_reset text="Clear Filters"]
    </div>
</div>

[properties_loop posts_per_page="12"]
```

### Rent-Specific Filter

```
[property_filter_location]
[property_filter_type]
[property_filter_price min="500" max="10000" step="100"]
[property_filter_bedrooms type="buttons"]
[property_filter_bathrooms type="buttons"]
[property_filter_amenities]
[property_filters_apply]

[properties_loop purpose="rent" posts_per_page="9"]
```

### Sale-Specific Filter

```
[property_filter_location]
[property_filter_type]
[property_filter_price min="100000" max="5000000" step="50000"]
[property_filter_bedrooms type="checkbox"]
[property_filter_bathrooms type="checkbox"]
[property_filter_covered_area min="50" max="1000" step="10"]
[property_filter_plot_area min="100" max="50000" step="100"]
[property_filter_amenities]
[property_filters_apply]

[properties_loop purpose="sale" posts_per_page="12"]
```

---

## CSS Classes Reference

All filters include CSS classes for custom styling:

- `.kcpf-filter` - Main filter wrapper
- `.kcpf-filter-location` - Location filter
- `.kcpf-filter-purpose` - Purpose filter
- `.kcpf-filter-price` - Price filter
- `.kcpf-filter-bedrooms` - Bedrooms filter
- `.kcpf-filter-bathrooms` - Bathrooms filter
- `.kcpf-filter-type` - Property type filter
- `.kcpf-filter-amenities` - Amenities filter
- `.kcpf-filter-covered-area` - Covered area filter
- `.kcpf-filter-plot-area` - Plot area filter
- `.kcpf-filter-property-id` - Property ID filter
- `.kcpf-range-slider` - Range slider element
- `.kcpf-range-inputs` - Range input container
- `.kcpf-filter-checkboxes` - Checkbox group container
- `.kcpf-button-group` - Button group container

---

## JetEngine Glossaries Required

Make sure these glossaries are created in JetEngine:

1. **Amenities** - All available property amenities
2. **Bedrooms** - Bedroom options (e.g., Studio, 1, 2, 3, 4, 5, 6+)
3. **Bathrooms** - Bathroom options (e.g., 1, 2, 3, 4, 5+)

If glossaries are not found, the plugin will use hardcoded fallback values.
