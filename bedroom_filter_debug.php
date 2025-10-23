<?php
/**
 * Simple Bedroom Filter Debug Script
 * Displays bedroom filter and shows matching properties for rent purpose
 *
 * Usage: Visit the page directly to see the filter form
 */

add_action('wp', 'simple_bedroom_filter_debug');

function simple_bedroom_filter_debug() {
    // Only run with debug parameter
    if (!isset($_GET['kcpf_debug']) || $_GET['kcpf_debug'] !== 'bedrooms') {
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_die('Admin access required');
    }

    echo '<h1>Simple Bedroom Filter Debug</h1>';
    echo '<p>This tool lets you test the bedroom filter with sale properties.</p>';

    // Handle "Show All Properties" button
    if (isset($_POST['show_all_properties'])) {
        echo '<div style="background: #fffacd; padding: 15px; border: 1px solid #ffd700; margin: 20px 0;">';
        echo '<h2>All Sale Properties and Their Bedroom Values</h2>';

        $bedrooms_key = 'bedrooms';

        $args = [
            'post_type' => 'properties',
            'posts_per_page' => -1, // Get all properties
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'purpose',
                    'field' => 'slug',
                    'terms' => 'sale',
                ]
            ]
        ];

        $query = new WP_Query($args);

        echo '<p><strong>Found ' . $query->found_posts . ' total sale properties</strong></p>';

        if ($query->have_posts()) {
            echo '<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">';
            echo '<thead>';
            echo '<tr style="background: #e0e0e0;">';
            echo '<th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Property ID</th>';
            echo '<th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Property Title</th>';
            echo '<th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Raw Bedrooms Meta Value</th>';
            echo '<th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Formatted Bedrooms</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while ($query->have_posts()) {
                $query->the_post();
                $property_id = get_the_ID();
                $title = get_the_title();
                $bedrooms_value = get_post_meta($property_id, $bedrooms_key, true);
                $formatted_bedrooms = KCPF_Card_Data_Helper::getBedrooms($property_id, 'sale');

                // Get raw database value to see actual storage format
                global $wpdb;
                $raw_db_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s",
                    $property_id, $bedrooms_key
                ));

                // Show data format info
                $data_type = 'unknown';
                $serialized_check = '';
                $storage_format = 'unknown';

                if ($raw_db_value !== null) {
                    if (is_serialized($raw_db_value)) {
                        $storage_format = 'PHP Serialized';
                        $unserialized = maybe_unserialize($raw_db_value);
                        $serialized_check = is_array($unserialized) ? 'Valid serialized array' : 'Invalid serialized data';
                    } elseif (json_decode($raw_db_value, true) !== null) {
                        $storage_format = 'JSON String';
                        $json_decoded = json_decode($raw_db_value, true);
                        $serialized_check = is_array($json_decoded) ? 'Valid JSON array' : 'Invalid JSON data';
                    } else {
                        $storage_format = 'Plain string/other';
                        $serialized_check = 'Not serialized or JSON';
                    }
                }

                if (is_array($bedrooms_value)) {
                    $data_type = 'PHP Array (unserialized)';
                } elseif (is_string($bedrooms_value)) {
                    $data_type = 'String';
                } else {
                    $data_type = gettype($bedrooms_value);
                }

                echo '<tr>';
                echo '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($property_id) . '</td>';
                echo '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($title) . '</td>';
                echo '<td style="border: 1px solid #ccc; padding: 8px;"><code>' . esc_html(json_encode($bedrooms_value)) . '</code><br><small>Data type: ' . esc_html($data_type) . '<br>Storage: ' . esc_html($storage_format) . '<br>Raw DB: <code>' . esc_html(substr($raw_db_value, 0, 100)) . (strlen($raw_db_value) > 100 ? '...' : '') . '</code></small></td>';
                echo '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($formatted_bedrooms) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p><em>No rent properties found.</em></p>';
        }

        wp_reset_postdata();

        echo '</div>';
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bedrooms']) && !empty($_POST['bedrooms'])) {
        $selected_bedroom = sanitize_text_field($_POST['bedrooms']);

        echo '<div style="background: #f0f8ff; padding: 15px; border: 1px solid #add8e6; margin: 20px 0;">';
        echo '<h2>Filter Results</h2>';
        echo '<p><strong>Selected bedroom value:</strong> ' . esc_html($selected_bedroom) . '</p>';

        // Query properties with this bedroom value
        $bedrooms_key = 'bedrooms'; // Always sale for this debug

        // Build query to match bedroom value in JSON array
        $meta_query = [
            'key' => $bedrooms_key,
            'value' => '"' . $selected_bedroom . '":"true"',  // Match if this bedroom number is set to true
            'compare' => 'LIKE'
        ];

        // Fallback: try different serialization lengths for 9_plus
        if ($selected_bedroom === '9_plus') {
            $meta_query[] = [
                'key' => $bedrooms_key,
                'value' => 's:6:"9_plus";s:4:"true"',
                'compare' => 'LIKE',
            ];
            $meta_query[] = [
                'key' => $bedrooms_key,
                'value' => 's:6:"9_plus";b:1',
                'compare' => 'LIKE',
            ];
        }

        $args = [
            'post_type' => 'properties',
            'posts_per_page' => -1, // Get all matching properties
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'purpose',
                    'field' => 'slug',
                    'terms' => 'sale',
                ]
            ],
            'meta_query' => $meta_query
        ];

        $query = new WP_Query($args);

        echo '<p><strong>Found ' . $query->found_posts . ' properties</strong></p>';
        echo '<p><strong>Meta query used:</strong> <code>' . json_encode($meta_query) . '</code></p>';

        // Also show which properties have the selected bedroom value set to true
        echo '<h3>Properties with bedroom "' . esc_html($selected_bedroom) . '" set to "true":</h3>';
        $check_query = new WP_Query([
            'post_type' => 'properties',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'purpose',
                    'field' => 'slug',
                    'terms' => 'sale',
                ]
            ]
        ]);

        $properties_with_true = [];
        if ($check_query->have_posts()) {
            while ($check_query->have_posts()) {
                $check_query->the_post();
                $property_id = get_the_ID();
                $bedrooms_value = get_post_meta($property_id, $bedrooms_key, true);

                // Check if this property has the selected bedroom set to true
                $has_bedroom_true = false;

                if (is_array($bedrooms_value) && isset($bedrooms_value[$selected_bedroom])) {
                    // For PHP arrays, check if value is true (boolean) or "true" (string)
                    $value = $bedrooms_value[$selected_bedroom];
                    if ($value === true || $value === 'true' || $value === 1 || $value === '1') {
                        $has_bedroom_true = true;
                    }
                } elseif (is_string($bedrooms_value)) {
                    if (is_serialized($bedrooms_value)) {
                        $unserialized = maybe_unserialize($bedrooms_value);
                        if (is_array($unserialized) && isset($unserialized[$selected_bedroom])) {
                            $value = $unserialized[$selected_bedroom];
                            if ($value === true || $value === 'true' || $value === 1 || $value === '1') {
                                $has_bedroom_true = true;
                            }
                        }
                    } elseif (json_decode($bedrooms_value, true) !== null) {
                        $decoded = json_decode($bedrooms_value, true);
                        if (is_array($decoded) && isset($decoded[$selected_bedroom])) {
                            $value = $decoded[$selected_bedroom];
                            if ($value === true || $value === 'true' || $value === 1 || $value === '1') {
                                $has_bedroom_true = true;
                            }
                        }
                    }
                }

                if ($has_bedroom_true) {
                    $properties_with_true[] = [
                        'id' => $property_id,
                        'title' => get_the_title(),
                        'bedrooms_data' => $bedrooms_value
                    ];
                }
            }
        }
        wp_reset_postdata();

        if (!empty($properties_with_true)) {
            echo '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">';
            echo '<thead><tr style="background: #ffe0e0;"><th style="border: 1px solid #ccc; padding: 8px;">ID</th><th style="border: 1px solid #ccc; padding: 8px;">Title</th><th style="border: 1px solid #ccc; padding: 8px;">Bedrooms Data</th></tr></thead>';
            echo '<tbody>';
            foreach ($properties_with_true as $prop) {
                echo '<tr>';
                echo '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($prop['id']) . '</td>';
                echo '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($prop['title']) . '</td>';
                echo '<td style="border: 1px solid #ccc; padding: 8px;"><code>' . esc_html(json_encode($prop['bedrooms_data'])) . '</code></td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p style="color: #d9534f;"><strong>No properties found with bedroom "' . esc_html($selected_bedroom) . '" set to "true"</strong></p>';
            echo '<p>This means the filtering query is working correctly - there are no properties that should match this bedroom value.</p>';
        }

        if (!empty($properties_with_true)) {
            echo '<h3>Properties Found:</h3>';
            echo '<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">';
            echo '<thead>';
            echo '<tr style="background: #e0e0e0;">';
            echo '<th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Property ID</th>';
            echo '<th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Property Title</th>';
            echo '<th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Raw Bedrooms Meta Value</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($properties_with_true as $prop) {
                echo '<tr>';
                echo '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($prop['id']) . '</td>';
                echo '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($prop['title']) . '</td>';
                echo '<td style="border: 1px solid #ccc; padding: 8px;"><code>' . esc_html(json_encode($prop['bedrooms_data'])) . '</code></td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p><em>No properties found with this bedroom value.</em></p>';
        }

        wp_reset_postdata();

        echo '</div>';
    }

    // Display the "Show All Properties" button
    echo '<div style="background: #f0fff0; padding: 15px; border: 1px solid #90ee90; margin: 20px 0;">';
    echo '<h2>View All Sale Properties</h2>';
    echo '<form method="post" action="" style="margin-bottom: 0;">';
    echo '<button type="submit" name="show_all_properties" value="1" style="background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">';
    echo 'Show All Properties and Their Bedroom Values';
    echo '</button>';
    echo '</form>';
    echo '</div>';

    // Display the filter form
    echo '<div style="background: #f9f9f9; padding: 20px; border: 1px solid #ddd; margin: 20px 0;">';
    echo '<h2>Bedroom Filter</h2>';
    echo '<form method="post" action="">';

    // Render the bedroom filter shortcode
    echo do_shortcode('[property_filter_bedrooms type="select"]');

    // Render the apply button shortcode
    echo do_shortcode('[property_filters_apply text="Apply Filter"]');

    echo '</form>';
    echo '</div>';

    echo '<hr>';
    echo '<p><strong>Usage:</strong> Visit any page with <code>?kcpf_debug=bedrooms</code> to access this debug tool.</p>';
    echo '<ul>';
    echo '<li><strong>Show All Properties:</strong> Click the green "Show All Properties and Their Bedroom Values" button to see all sale properties and their bedroom data.</li>';
    echo '<li><strong>Test Filter:</strong> Select a bedroom value from the dropdown and click "Apply Filter" to see only matching sale properties.</li>';
    echo '</ul>';
    echo '<p><strong>Technical Details:</strong></p>';
    echo '<ul>';
    echo '<li>Always searches sale properties (purpose = "sale")</li>';
    echo '<li>Uses meta key "bedrooms"</li>';
    echo '<li>Shows raw meta field values as stored in database</li>';
    echo '<li>Uses LIKE query for serialized array values</li>';
    echo '</ul>';

    exit;
}
