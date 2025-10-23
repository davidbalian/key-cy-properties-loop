# .cursorrules Compliance Violations Report

## Executive Summary

This report identifies all parts of the plugin that do not comply with the established .cursorrules. The violations are categorized by severity and type.

---

## 🚨 CRITICAL VIOLATIONS (Must Fix Immediately)

### 1. File Length Violations - UNACCEPTABLE

These files **drastically exceed** the 500-line hard limit and violate the "treat 1000 lines as unacceptable" rule:

#### `assets/css/filters.css` - **1,137 lines** ❌

**Severity:** CRITICAL - 227% over limit
**Violation:** Exceeds 500-line limit by 637 lines (127% over)
**Impact:** God file containing ALL filter and card styles
**Required Action:** Split immediately into:

- `filters-form.css` - Form layout and filter controls (~200 lines)
- `property-cards-sale.css` - Sale card styles (~300 lines)
- `property-cards-rent.css` - Rent card styles (~200 lines)
- `property-cards-shared.css` - Base card styles (~150 lines)
- `multiunit-tables.css` - Multi-unit table styles (~150 lines)
- `responsive.css` - Media queries (~137 lines)

#### `assets/js/filters.js` - **991 lines** ❌

**Severity:** CRITICAL - 198% over limit
**Violation:** Exceeds 500-line limit by 491 lines (98% over)
**Impact:** God file containing ALL filter and loop JavaScript
**Required Action:** Split immediately into:

- `filters-form-manager.js` - Form wrapping and submission (~150 lines)
- `filters-toggle-handler.js` - Toggle button interactions (~100 lines)
- `filters-range-sliders.js` - Range slider initialization (~150 lines)
- `filters-ajax-handler.js` - AJAX requests and responses (~200 lines)
- `infinite-scroll-handler.js` - Infinite scroll logic (~150 lines)
- `url-state-manager.js` - URL parameter handling (~150 lines)
- `filters-helpers.js` - Utility functions (~91 lines)

#### `includes/class-filter-renderer.php` - **956 lines** ❌

**Severity:** CRITICAL - 191% over limit
**Violation:** Exceeds 500-line limit by 456 lines (91% over)
**Impact:** God class with 12+ public static methods rendering different filter types
**Required Action:** Split immediately into:

- `class-location-filter-renderer.php` (~100 lines)
- `class-type-filter-renderer.php` (~100 lines)
- `class-bedrooms-filter-renderer.php` (~100 lines)
- `class-bathrooms-filter-renderer.php` (~100 lines)
- `class-price-filter-renderer.php` (~150 lines)
- `class-area-filter-renderer.php` (~150 lines)
- `class-purpose-filter-renderer.php` (~80 lines)
- `class-filter-renderer-base.php` - Shared functionality (~176 lines)

---

## ⚠️ HIGH PRIORITY VIOLATIONS

### 2. File Length Violations - Approaching Unacceptable

#### `key-cy-properties-filter.php` - **482 lines** ⚠️

**Severity:** HIGH - 96% of limit
**Violation:** Exceeds 400-line warning threshold, approaching 500-line hard limit
**Impact:** Main plugin file handling too many responsibilities
**Required Action:** Split into:

- `key-cy-properties-filter.php` - Main plugin bootstrap (~100 lines)
- `includes/class-plugin-loader.php` - Dependency loading (~100 lines)
- `includes/class-shortcode-manager.php` - Shortcode registration (~100 lines)
- `includes/class-ajax-manager.php` - AJAX handler registration (~100 lines)
- `includes/class-asset-manager.php` - Asset enqueuing and critical CSS (~82 lines)

---

## 📋 MODERATE VIOLATIONS

### 3. Code Duplication - Violates "NEVER duplicate functionality"

#### Duplicate Card Rendering HTML

**Files Affected:**

- `includes/class-loop-renderer.php` (lines 128-211)
- `includes/class-map-card-renderer.php` (lines 76-166)

**Violation Details:**
Both files contain **IDENTICAL** HTML rendering logic for sale property cards:

- Same `renderSaleCard()` method with identical markup
- Same multi-unit table rendering in `renderMultiUnitTable()` (lines 217-271 vs 184-234)
- Same SVG icons embedded inline
- Only difference: `class-map-card-renderer.php` adds data attributes

**Rules Violated:**

- ❌ "NEVER duplicate functionality that already exists in the codebase"
- ❌ "ALWAYS reuse existing classes, helpers, and renderers before creating new ones"
- ❌ "Prefer composition and delegation over duplication"

**Required Action:**

1. Keep `KCPF_Map_Card_Renderer::renderCard()` as the SINGLE source of truth
2. Delete duplicate methods from `class-loop-renderer.php`
3. Update `class-loop-renderer.php` to call `KCPF_Map_Card_Renderer::renderCard()`:

```php
// BEFORE (WRONG - Duplicate rendering)
while ($query->have_posts()) {
    $query->the_post();
    self::renderPropertyCard();
}

// AFTER (CORRECT - Reuse renderer)
while ($query->have_posts()) {
    $query->the_post();
    echo KCPF_Map_Card_Renderer::renderCard(get_the_ID(), $purpose);
}
```

**Lines of Duplicate Code:** ~150 lines × 2 files = 300 lines of unnecessary duplication

---

### 4. WordPress Loop Function Violations

#### `includes/class-loop-renderer.php` - Using Global Post Context

**Lines:** 130, 132-133, 141

**Violation Details:**

```php
// Line 130 - WRONG
<a href="<?php the_permalink(); ?>" class="kcpf-property-card-link">

// Line 132-133 - WRONG
<?php if (has_post_thumbnail()) :
    $image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');

// Line 141 - WRONG
<?php the_title(); ?>
```

**Rules Violated:**

- ❌ "Pass property ID explicitly (no global post reliance)"
- ❌ Common Mistake #4: "Using WordPress loop functions without property ID"

**Required Action:**
Since this class will be refactored to use `KCPF_Map_Card_Renderer`, this will be automatically fixed. However, if keeping any rendering logic:

```php
// CORRECT
<a href="<?php echo get_permalink($property_id); ?>">
<?php if (has_post_thumbnail($property_id)) :
    $image_url = get_the_post_thumbnail_url($property_id, 'full');
<?php echo get_the_title($property_id); ?>
```

---

### 5. Direct Post Meta Access - Violates Helper Usage Rule

#### `includes/class-map-card-renderer.php` - Line 39

**Violation Details:**

```php
// Line 39 - WRONG
$coordinates = get_post_meta($property_id, 'display_coordinates', true);
```

**Rules Violated:**

- ❌ "ALWAYS use KCPF_Card_Data_Helper for retrieving property data"
- ❌ "NEVER directly access post meta or taxonomies for property data"
- ❌ Common Mistake #3: "Accessing post meta directly instead of using helpers"

**Required Action:**

1. Add `getCoordinates()` method to `class-card-data-helper.php`:

```php
/**
 * Get property display coordinates
 *
 * @param int $property_id Property ID
 * @return string Coordinates string
 */
public static function getCoordinates($property_id) {
    return get_post_meta($property_id, 'display_coordinates', true);
}
```

2. Update `class-map-card-renderer.php` line 39:

```php
// CORRECT
$coordinates = KCPF_Card_Data_Helper::getCoordinates($property_id);
```

---

## 🔍 LOW PRIORITY VIOLATIONS

### 6. Single Responsibility Violations

#### `includes/class-filter-renderer.php`

**Multiple Responsibilities:**

- Rendering location filters
- Rendering type filters
- Rendering bedroom filters
- Rendering bathroom filters
- Rendering price filters
- Rendering area filters
- Rendering purpose filters
- Filtering terms by purpose
- Managing filter state
- Generating filter HTML
- Handling filter options
- Managing placeholders

**Rules Violated:**

- ⚠️ "Every file, class, and function should do one thing only"
- ⚠️ "If it has multiple responsibilities, split it immediately"
- ⚠️ "Never let one file or class hold everything"

**Impact:** God class anti-pattern with 12 public methods

**Already Flagged:** This will be resolved by splitting the 956-line file (Critical Violation #1)

---

#### `key-cy-properties-filter.php`

**Multiple Responsibilities:**

- Plugin initialization
- Dependency loading
- Asset enqueuing
- Critical CSS injection
- Shortcode registration
- AJAX handler registration
- Settings management

**Rules Violated:**

- ⚠️ "Every file, class, and function should do one thing only"
- ⚠️ "Use ViewModel, Manager, and Coordinator naming conventions for logic separation"

**Already Flagged:** This will be resolved by splitting the 482-line file (High Priority Violation #2)

---

### 7. File Organization - Missing Separation

#### JavaScript Files Lack Manager/Coordinator Pattern

**Current Structure:**

- `assets/js/filters.js` - One massive file doing everything
- `assets/js/map-view.js` - Reasonable size (423 lines) but could benefit from split

**Rules Violated:**

- ⚠️ "Use ViewModel, Manager, and Coordinator naming conventions"
- ⚠️ "UI logic → ViewModel, Business logic → Manager, Navigation/state flow → Coordinator"

**Recommendation:** When splitting `filters.js`, follow manager pattern:

- `FiltersFormManager` - Form construction
- `FiltersAjaxManager` - AJAX operations
- `FiltersStateManager` - State and URL management
- `InfiniteScrollManager` - Scroll behavior

---

### 8. Inconsistent Data Retrieval

#### `includes/class-map-shortcode.php` - Line 61

**Current Code:**

```php
// Potential direct access pattern (need to verify)
```

**Recommendation:** Audit this file to ensure all property data retrieval uses `KCPF_Card_Data_Helper` methods.

---

## 📊 COMPLIANCE SUMMARY

### Files Requiring Immediate Action

| File                                   | Lines | Violation          | Priority | Action                   |
| -------------------------------------- | ----- | ------------------ | -------- | ------------------------ |
| `assets/css/filters.css`               | 1,137 | 227% over limit    | CRITICAL | Split into 6 files       |
| `assets/js/filters.js`                 | 991   | 198% over limit    | CRITICAL | Split into 7 files       |
| `includes/class-filter-renderer.php`   | 956   | 191% over limit    | CRITICAL | Split into 8 classes     |
| `key-cy-properties-filter.php`         | 482   | 96% of limit       | HIGH     | Split into 5 files       |
| `includes/class-loop-renderer.php`     | 291   | Duplicate code     | MODERATE | Refactor to use renderer |
| `includes/class-map-card-renderer.php` | 292   | Direct meta access | MODERATE | Add helper method        |

### Files Within Compliance

✅ Files under 300 lines (well within limits):

- `class-card-data-helper.php` (278 lines)
- `class-multiunit-query-builder.php` (244 lines)
- `class-query-handler.php` (229 lines)
- `class-debug-viewer.php` (214 lines)
- `class-settings-manager.php` (180 lines)
- `class-listing-values.php` (145 lines)
- `class-url-manager.php` (142 lines)
- `class-homepage-filters.php` (116 lines)
- `class-rent-card-view.php` (107 lines)
- `class-field-config.php` (101 lines)
- `class-map-filters.php` (99 lines)
- `class-filters-ajax.php` (85 lines)
- `class-glossary-handler.php` (81 lines)

---

## 🎯 REFACTORING PRIORITY ORDER

### Phase 1: Critical File Splits (Immediate)

1. Split `class-filter-renderer.php` (956 lines → 8 files)
2. Split `filters.js` (991 lines → 7 files)
3. Split `filters.css` (1,137 lines → 6 files)

### Phase 2: High Priority (Within 1 week)

4. Split `key-cy-properties-filter.php` (482 lines → 5 files)
5. Eliminate duplicate rendering in `class-loop-renderer.php`

### Phase 3: Code Quality (Within 2 weeks)

6. Add `getCoordinates()` to `KCPF_Card_Data_Helper`
7. Remove direct meta access from `class-map-card-renderer.php`
8. Audit all files for remaining direct meta access

---

## 🔧 IMPLEMENTATION NOTES

### Key Principles to Follow During Refactoring

1. **Maintain Backward Compatibility**

   - Keep public APIs unchanged during internal refactoring
   - Existing shortcodes must continue to work

2. **Test After Each Split**

   - Split one file at a time
   - Test thoroughly before moving to next file
   - Ensure no functionality is lost

3. **Follow Existing Patterns**

   - Use `KCPF_` prefix for all classes
   - Follow established naming conventions
   - Maintain existing file locations (`includes/`, `assets/css/`, `assets/js/`)

4. **Document Dependencies**

   - Update `loadDependencies()` in main plugin file
   - Add clear docblocks to new classes
   - Document in relevant `.md` files

5. **Preserve Reusability**
   - Each new class should be focused and reusable
   - Avoid tight coupling between split components
   - Use dependency injection where appropriate

---

## 📝 CONCLUSION

**Total Violations Found:** 8 categories
**Critical Violations:** 3 files requiring immediate action
**High Priority Violations:** 2 files requiring action within 1 week
**Moderate Violations:** 3 code quality issues
**Low Priority Violations:** Various organizational improvements

**Estimated Refactoring Effort:**

- Phase 1 (Critical): 8-12 hours
- Phase 2 (High): 4-6 hours
- Phase 3 (Quality): 2-3 hours
- **Total:** 14-21 hours

**Risk Assessment:**

- **High Risk:** The duplicate rendering code could lead to maintenance issues
- **Medium Risk:** Giant files make debugging and testing difficult
- **Low Risk:** Direct meta access is isolated to one location

**Recommendation:** Prioritize Phase 1 (Critical) splits immediately. These files violate the fundamental principle of keeping files under 500 lines and represent technical debt that will compound over time.
