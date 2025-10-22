# How to Add Filter Shortcodes to Your Site

## Quick Start

You can add the filter shortcodes to your site in several ways:

---

## Method 1: Page/Post Editor (Recommended)

This is the easiest method. Just add the shortcodes directly in the WordPress editor.

### Using Block Editor (Gutenberg)

1. **Edit your page** (e.g., "Properties" page)
2. **Add a Shortcode block** (or just type `/shortcode`)
3. **Paste your filter shortcodes**, for example:

```
[property_filter_location]

[property_filter_price]

[property_filters_apply]

[properties_loop]
```

4. **Update/Publish the page**

**Important:** Keep all filter shortcodes in the **same Shortcode block** or **same container**. Don't put each filter in a separate block.

### Using Classic Editor

1. **Edit your page**
2. Switch to **Text mode** (not Visual)
3. **Paste the shortcodes** where you want them:

```
[property_filter_location]
[property_filter_price]
[property_filters_apply]

[properties_loop]
```

4. **Update/Publish**

---

## Method 2: Elementor (or Other Page Builders)

### Elementor

1. **Edit page with Elementor**
2. **Drag a "Shortcode" widget** to where you want the filters
3. **Add ALL filter shortcodes** in that one widget:

```
[property_filter_location]
[property_filter_price]
[property_filter_bedrooms]
[property_filters_apply]
```

4. **Drag another "Shortcode" widget** below it for the properties loop:

```
[properties_loop]
```

5. **Update**

### Bricks Builder

1. **Edit with Bricks**
2. **Add a "Shortcodes" element**
3. **Paste all filter shortcodes** together
4. **Add another "Shortcodes" element** for the loop
5. **Save**

### Divi

1. **Edit page**
2. **Add a "Code" module**
3. **Paste filter shortcodes**
4. **Add another "Code" module** for the loop
5. **Save**

---

## Method 3: Widget Areas (Sidebars)

If you want filters in a sidebar:

1. **Go to Appearance > Widgets**
2. **Add a "Shortcode" widget** to your sidebar
3. **Paste the filter shortcodes:**

```
[property_filter_location]
[property_filter_price]
[property_filters_apply]
```

4. **Save**

Then add the properties loop to your main content area.

---

## Method 4: Template Files (For Developers)

If you're creating a custom template:

```php
<?php
// In your template file (e.g., archive-properties.php)

// Filters section
echo do_shortcode('[property_filter_location]');
echo do_shortcode('[property_filter_price]');
echo do_shortcode('[property_filter_bedrooms]');
echo do_shortcode('[property_filters_apply]');

// Properties loop
echo do_shortcode('[properties_loop]');
?>
```

**Important:** All `do_shortcode()` calls for filters should be in the same PHP block/container so they get wrapped in a form together.

---

## ⚠️ Critical Rules

### Rule 1: Keep Filters Together

**✅ CORRECT:**

```
[property_filter_location]
[property_filter_price]
[property_filter_bedrooms]
[property_filters_apply]
```

All in the same block/widget/container.

**❌ WRONG:**

```
<div>[property_filter_location]</div>
<div>[property_filter_price]</div>
<div>[property_filter_bedrooms]</div>
<div>[property_filters_apply]</div>
```

Each in a separate container - they won't be wrapped in a form together!

**❌ WRONG (Elementor example):**

- Shortcode Widget 1: `[property_filter_location]`
- Shortcode Widget 2: `[property_filter_price]`
- Shortcode Widget 3: `[property_filters_apply]`

They need to be in ONE widget!

### Rule 2: Filters Before Loop

Always put filter shortcodes **before** the `[properties_loop]`:

**✅ CORRECT:**

```
[property_filter_location]
[property_filters_apply]

[properties_loop]
```

**❌ WRONG:**

```
[properties_loop]

[property_filter_location]
[property_filters_apply]
```

### Rule 3: Always Include Apply Button

Don't forget the apply button! Filters won't work without it.

**✅ CORRECT:**

```
[property_filter_location]
[property_filter_price]
[property_filters_apply]
```

**❌ WRONG:**

```
[property_filter_location]
[property_filter_price]
(no apply button)
```

---

## Complete Examples

### Example 1: Minimal Filter Setup

```
[property_filter_location]
[property_filter_price]
[property_filters_apply]

[properties_loop]
```

### Example 2: Sale Properties with All Filters

```
[property_filter_location]
[property_filter_type]
[property_filter_price min="100000" max="5000000" step="50000"]
[property_filter_bedrooms type="checkbox"]
[property_filter_bathrooms type="checkbox"]
[property_filter_covered_area]
[property_filter_plot_area]
[property_filter_amenities]
[property_filters_apply text="Search Properties"]
[property_filters_reset]

[properties_loop purpose="sale" posts_per_page="12"]
```

### Example 3: Rent Properties

```
[property_filter_purpose type="toggle"]
[property_filter_location]
[property_filter_price min="500" max="10000" step="100"]
[property_filter_bedrooms type="buttons"]
[property_filter_bathrooms type="buttons"]
[property_filter_amenities]
[property_filters_apply]

[properties_loop purpose="rent" posts_per_page="9"]
```

### Example 4: With Custom Styling

You can wrap filters in your own HTML for styling:

```
<div class="my-filters-container">
    <div class="filter-row">
        [property_filter_location]
        [property_filter_price]
    </div>
    <div class="filter-row">
        [property_filter_bedrooms]
        [property_filter_bathrooms]
    </div>
    <div class="filter-buttons">
        [property_filters_apply]
        [property_filters_reset]
    </div>
</div>

<div class="my-properties-grid">
    [properties_loop]
</div>
```

**Important:** All the filter shortcodes must still be within the same parent container (`my-filters-container` in this example).

---

## Archive Page Example

If you want filters on your properties archive page (`/properties/`):

1. **Create a new page** called "Properties Archive" or similar
2. **Add your shortcodes:**

```
<h2>Find Your Perfect Property</h2>

[property_filter_location placeholder="Select Location"]
[property_filter_type type="checkbox"]
[property_filter_price]
[property_filter_bedrooms]
[property_filters_apply text="Search"]
[property_filters_reset text="Clear All"]

[properties_loop posts_per_page="12"]
```

3. **Set this page as your properties archive** (if using custom post types)

---

## Testing Your Setup

After adding the shortcodes:

1. **View the page** on the frontend
2. **Open browser console** (F12)
3. Look for: `[KCPF] Filters initialized`
4. **Select some filter options**
5. **Click Apply**
6. You should see the results update without page reload

---

## Common Mistakes

### ❌ Mistake 1: Separating Filters in Different Blocks

**Wrong (Elementor):**

```
[Shortcode Widget 1]
[property_filter_location]

[Shortcode Widget 2]
[property_filter_price]

[Shortcode Widget 3]
[property_filters_apply]
```

**Right:**

```
[Shortcode Widget]
[property_filter_location]
[property_filter_price]
[property_filters_apply]
```

### ❌ Mistake 2: No Apply Button

**Wrong:**

```
[property_filter_location]
[property_filter_price]
```

**Right:**

```
[property_filter_location]
[property_filter_price]
[property_filters_apply]
```

### ❌ Mistake 3: Loop Before Filters

**Wrong:**

```
[properties_loop]
[property_filter_location]
```

**Right:**

```
[property_filter_location]
[properties_loop]
```

---

## How the Plugin Works Behind the Scenes

Understanding this will help you use it correctly:

1. **Plugin detects all `.kcpf-filter` elements** on the page
2. **Wraps them in a form** automatically (if they're siblings in the same container)
3. **When you click Apply**, the form submits via AJAX
4. **Results load** without page reload
5. **URL updates** with your filter parameters

**This is why filters must be together** - so they get wrapped in the same form!

---

## Need Help?

If filters aren't working:

1. Check browser console for `[KCPF]` messages
2. Make sure all filters are in the same container
3. Verify the apply button is included
4. Clear your browser cache
5. See `DEBUGGING_GUIDE.md` for detailed troubleshooting

---

## Quick Reference

**All Available Shortcodes:**

```
[property_filter_location]
[property_filter_purpose]
[property_filter_price]
[property_filter_bedrooms]
[property_filter_bathrooms]
[property_filter_type]
[property_filter_amenities]
[property_filter_covered_area]
[property_filter_plot_area]
[property_filter_id]
[property_filters_apply]
[property_filters_reset]
[properties_loop]
```

See `SHORTCODE_REFERENCE.md` for all attributes and options.
