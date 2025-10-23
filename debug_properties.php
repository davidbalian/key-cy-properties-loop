<?php
/**
 * Debug snippet to list all properties with detailed bedrooms and bathrooms meta.
 * Access via ?debug_properties=1 (only for admins).
 */

add_action('wp', 'debug_properties_meta');

function debug_properties_meta() {
    if (!isset($_GET['debug_properties']) || !current_user_can('manage_options')) {
        return;
    }

    $args = [
        'post_type' => 'properties',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ];

    $properties = new WP_Query($args);

    echo '<h1>Properties Bedrooms and Bathrooms Meta Details</h1>';
    echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
    echo '<thead><tr>';
    echo '<th>ID</th><th>Title</th><th>Purpose</th><th>bedrooms (raw)</th><th>bedrooms (processed)</th><th>bathrooms (raw)</th><th>bathrooms (processed)</th><th>Multi-Unit</th><th>Multi-Unit Table</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    if ($properties->have_posts()) {
        while ($properties->have_posts()) {
            $properties->the_post();
            $id = get_the_ID();
            $title = get_the_title();
            $purposes = wp_get_post_terms($id, 'purpose', ['fields' => 'names']);
            $purpose_str = implode(', ', $purposes);

            // Get raw meta values
            $bedrooms_raw = get_post_meta($id, 'bedrooms', true);
            $rent_bedrooms_raw = get_post_meta($id, 'rent_bedrooms', true);
            $bathrooms_raw = get_post_meta($id, 'bathrooms', true);
            $rent_bathrooms_raw = get_post_meta($id, 'rent_bathrooms', true);
            $multi_unit = get_post_meta($id, 'multi-unit', true);
            $multi_unit_table = get_post_meta($id, 'multi-unit_table', true);

            // Determine purpose and get the appropriate raw value
            $is_rent = in_array('Rent', $purposes);
            $current_bedrooms_raw = $is_rent ? $rent_bedrooms_raw : $bedrooms_raw;
            $current_bathrooms_raw = $is_rent ? $rent_bathrooms_raw : $bathrooms_raw;

            // Format for display (mimic card data helper)
            $bedrooms_processed = format_value($current_bedrooms_raw);
            $bathrooms_processed = format_value($current_bathrooms_raw);

            echo '<tr>';
            echo '<td>' . esc_html($id) . '</td>';
            echo '<td>' . esc_html($title) . '</td>';
            echo '<td>' . esc_html($purpose_str) . '</td>';
            echo '<td>' . display_raw_value($current_bedrooms_raw) . '</td>';
            echo '<td>' . esc_html($bedrooms_processed) . '</td>';
            echo '<td>' . display_raw_value($current_bathrooms_raw) . '</td>';
            echo '<td>' . esc_html($bathrooms_processed) . '</td>';
            echo '<td>' . esc_html($multi_unit ? 'Yes' : 'No') . '</td>';
            echo '<td>' . display_raw_value($multi_unit_table) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="9">No properties found.</td></tr>';
    }

    echo '</tbody></table>';

    wp_reset_postdata();
    exit; // Prevent loading the rest of the page
}

function format_value($value) {
    // Handle empty values
    if (empty($value) && $value !== '0' && $value !== 0) {
        return '';
    }

    // Handle array values (with "Save as array" enabled)
    if (is_array($value)) {
        // Get all keys with true values
        $selectedValues = [];
        foreach ($value as $key => $val) {
            // Check if value is boolean true
            if ($val === true || $val === 'true' || $val === 1) {
                $selectedValues[] = $key;
            }
        }

        // If no true values, return empty
        if (empty($selectedValues)) {
            return '';
        }

        // Convert "9_plus" to "9+" for display
        $formatted = array_map(function($val) {
            return str_replace('_plus', '+', $val);
        }, $selectedValues);

        // Return first value only
        return reset($formatted);
    }

    // Handle serialized strings (when "Save as array" is NOT enabled)
    if (is_string($value) && is_serialized($value)) {
        $value = maybe_unserialize($value);
        if (is_array($value)) {
            $value = !empty($value) ? reset($value) : '';
        }
    }

    // If value is still empty after handling
    if (empty($value) && $value !== '0' && $value !== 0) {
        return '';
    }

    // Convert to string for consistency
    $value = (string) $value;

    // Convert "9_plus" to "9+" for display
    if (preg_match('/^\d+(_plus)?$/', $value)) {
        return str_replace('_plus', '+', $value);
    }

    // Return the value as-is
    return $value;
}

function display_raw_value($value) {
    if (is_array($value)) {
        return 'ARRAY: ' . json_encode($value);
    } elseif (is_serialized($value)) {
        return 'SERIALIZED: ' . $value;
    } else {
        return 'VALUE: ' . $value;
    }
}
