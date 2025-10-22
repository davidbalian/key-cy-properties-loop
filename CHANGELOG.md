# Changelog

All notable changes to the Key CY Properties Filter plugin will be documented in this file.

## [2.4.2] - 2025-01-03

### Added

- **Number Formatter Class** - Centralized number formatting utility for consistent display
- **Proper number formatting** - All prices, areas, and numeric values now display with commas and dots
- **Multi-unit table formatting** - Unit prices and areas in tables are now properly formatted
- **Currency slider formatting** - Price range sliders display values with thousands separators

### Changed

- **KCPF_Card_Data_Helper** - Now uses KCPF_Number_Formatter for all numeric formatting
- **KCPF_Loop_Renderer** - Multi-unit table cells now use formatted numbers
- **KCPF_Filter_Renderer** - All filter displays use consistent number formatting
- **JavaScript slider formatting** - Price sliders now display currency with commas
- **Input formatting** - Range filter inputs automatically format values with thousands separators

### Fixed

- **Inconsistent number display** - All numbers now follow the same format (commas for thousands, dots for decimals)
- **Multi-unit table readability** - Large numbers in property tables are now easier to read
- **Slider display** - Currency values in price sliders are properly formatted

### Technical Details

```php
// Number formatting methods available:
KCPF_Number_Formatter::format($value, $decimals);
KCPF_Number_Formatter::formatPrice($value, $currency);
KCPF_Number_Formatter::formatArea($value, $unit);
KCPF_Number_Formatter::formatMultiUnitPrice($value);
KCPF_Number_Formatter::formatMultiUnitArea($value);
```

---

## [2.3.0] - 2025-10-22

### Added

- **JetEngine Glossary Integration** - Filters now pull options directly from JetEngine glossaries
- **Dynamic Bedrooms filter** - Uses JetEngine glossary ID 7 (Bedrooms)
- **Dynamic Bathrooms filter** - Uses JetEngine glossary ID 8 (Bathrooms)
- **Dynamic Amenities filter** - Uses JetEngine glossary ID 24 (Amenities)
- **Customizable glossary IDs** - Each filter accepts `glossary_id` attribute for custom glossaries
- **Automatic fallbacks** - Hardcoded options if JetEngine is unavailable or glossary is empty

### Changed

- **KCPF_Glossary_Handler** - Now uses correct JetEngine API: `jet_engine()->glossaries->filters->get_glossary_options()`
- **Filter data source** - Changed from hardcoded arrays to dynamic JetEngine glossaries
- **More flexible** - Glossary IDs can be overridden via shortcode attributes

### Technical Details

```php
// Bedrooms uses glossary ID 7
[kcpf_filter_bedrooms glossary_id="7"]

// Bathrooms uses glossary ID 8
[kcpf_filter_bathrooms glossary_id="8"]

// Amenities uses glossary ID 24
[kcpf_filter_amenities glossary_id="24"]
```

---

## [2.2.0] - 2025-10-22

### Changed

- **Removed JetEngine glossary dependency** - All filters now use predefined values
- **Amenities filter** - Now uses hardcoded list of 19 amenities
- **Bedrooms filter** - Now uses predefined options (Studio, 1-6+)
- **Bathrooms filter** - Now uses predefined options (1-6+)

### Fixed

- **No more glossary errors** - Filters work without JetEngine or glossaries
- **More reliable** - No dependency on external plugin data
- **Consistent behavior** - Same values across all installations

### Amenities Included

Air Condition, Heating, Balcony, Covered veranda, Uncovered veranda, Roof garden, Elevator, Furnished, Pets Allowed, Pool, Fitness Center, Sea view, Quiet neighbourhood, Storage, Covered parking, Spa/Sauna, Security Alarm, BBQ zone, Fireplace

---

## [2.1.2] - 2025-10-22

### Fixed

- **"Array Bed" display issue** - Property cards now properly handle array values from JetEngine meta fields
- **JetEngine glossary compatibility** - Better handling of different JetEngine glossary structures
- **Fallback options** - Bedrooms/Bathrooms filters use fallback values when glossaries are empty

### Added

- **Admin Debug Viewer** - View KCPF error logs directly in WordPress admin (Tools → KCPF Debug Logs)
- **Better glossary structure detection** - Handles both ['value','label'] and ['id','name'] formats
- **Detailed debug logging** - Shows glossary structure when options can't be extracted

### Improved

- Array value handling in loop renderer
- Glossary handler now logs structure when failing to extract options
- Better error messages for troubleshooting glossary issues

---

## [2.1.1] - 2025-10-22

### Added

- **Comprehensive error handling** with try-catch blocks in all filter renderers
- **Detailed error logging** to WordPress error logs for debugging
- **Input validation** for all range filters (price, covered area, plot area)
- **Graceful degradation** - filters fail silently without breaking the page

### Fixed

- **Price filter validation** - prevents critical errors from invalid min/max values
- **Glossary handler errors** - catches and logs JetEngine glossary issues
- **AJAX error handling** - AJAX failures now return proper error responses
- **Range filter validation** - ensures min < max and step > 0

### Security

- Added `absint()` validation for all numeric filter attributes
- Better sanitization of filter values

---

## [2.1.0] - 2025-10-22

### Fixed

- **Clean URL parameters** for single-value checkbox filters (e.g., `?location=limassol` instead of `?location[0]=limassol`)
- **Pagination active page highlighting** now works correctly with AJAX requests
- Improved form data handling for better URL formatting

### Added

- Comprehensive console logging for debugging (`[KCPF]` messages)
- Better error handling and reporting in JavaScript
- Fallback logic for apply button when not directly in form
- Check for `kcpfData` availability before AJAX requests

### Changed

- Single checkbox selections now use clean parameter format
- Multiple checkbox selections still use array format (`[]`)
- Pagination now reads current page from URL parameters instead of `get_query_var()`

### Documentation

- Added `USAGE_GUIDE.md` with detailed setup instructions
- Added `DEBUGGING_GUIDE.md` with troubleshooting steps
- Updated implementation summary with AJAX details

---

## [2.0.0] - 2025-10-22

### Added

- **Full AJAX functionality** for all filtering and pagination
- AJAX endpoint `kcpf_load_properties` for loading filtered results
- Automatic URL updates using browser history API (`pushState`)
- Browser back/forward button support via `popstate` event handler
- Loading spinner animation during AJAX requests
- Smooth scroll to results after AJAX load
- New AJAX handlers in main plugin class

### Changed

- **Plugin author** changed to balian.cy
- **Plugin version** bumped to 2.0.0
- All filter form submissions now use AJAX by default
- Pagination links now trigger AJAX requests instead of page reloads
- Filter values are always reflected in the URL
- Improved user experience with instant filtering

### Technical Details

- Added `registerAjaxHandlers()` method to main plugin class
- Added `ajaxLoadProperties()` AJAX handler
- Rewrote `handleFormSubmission()` in JavaScript to always use AJAX
- Added `loadPropertiesAjax()` function for unified AJAX loading
- Enhanced CSS with loading state styles and spinner animation
- URL parameters are preserved and updated dynamically

---

## [1.0.0] - 2025-10-22

### Initial Release

- Dynamic sale/rent meta field switching
- JetEngine glossary integration for bedrooms, bathrooms, and amenities
- New filter types: amenities, covered area, plot area, property ID search
- Range sliders with noUiSlider library for price, covered area, and plot area
- Individual shortcodes for each filter type
- Modular architecture following single responsibility principle
- Responsive design for mobile and desktop
- Support for multiple filter display types (select, checkbox, buttons, toggles)
- Clean URL parameter handling
- Pagination support
