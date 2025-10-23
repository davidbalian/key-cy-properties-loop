<?php
/**
 * Simple Bedroom Filter Debug Script
 * Test bedroom filtering with detailed error messages
 *
 * Usage: ?bedroom=2&purpose=sale
 */

add_action('wp', 'debug_bedroom_filter');

function debug_bedroom_filter() {
    if (!isset($_GET['bedroom'])) {
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_die('Admin access required');
    }

    $purpose = isset($_GET['purpose']) ? sanitize_text_field($_GET['purpose']) : 'sale';

    // Handle multiple bedroom values (comma-separated)
    $bedroom_values = [];
    if (isset($_GET['bedroom']) && !empty($_GET['bedroom'])) {
        $bedroom_param = sanitize_text_field($_GET['bedroom']);
        if (strpos($bedroom_param, ',') !== false) {
            // Multiple values separated by commas
            $bedroom_values = array_map('trim', explode(',', $bedroom_param));
            $bedroom_values = array_map('sanitize_text_field', $bedroom_values);
        } else {
            // Single value
            $bedroom_values = [sanitize_text_field($bedroom_param)];
        }
    }

    $bedroom_display = implode(', ', $bedroom_values);

    echo '<h1>Bedroom Filter Debug</h1>';
    echo '<p>Testing bedroom filter: <strong>' . esc_html($bedroom_display) . '</strong> for purpose: <strong>' . esc_html($purpose) . '</strong></p>';
    echo '<p>Bedroom values array: ' . json_encode($bedroom_values) . '</p>';

    // Test 1: Simple query without any meta filters
    echo '<h2>Test 1: Basic Properties Query</h2>';
    $start_time = microtime(true);

    try {
        $args = [
            'post_type' => 'properties',
            'posts_per_page' => 5,
            'post_status' => 'publish',
        ];

        $query = new WP_Query($args);
        $end_time = microtime(true);
        $duration = $end_time - $start_time;

        echo '<p>✅ Basic query completed in ' . number_format($duration, 4) . ' seconds</p>';
        echo '<p>Found ' . $query->found_posts . ' total properties</p>';

        if ($query->have_posts()) {
            echo '<ul>';
            while ($query->have_posts()) {
                $query->the_post();
                echo '<li>' . get_the_ID() . ': ' . get_the_title() . '</li>';
            }
            echo '</ul>';
        }
        wp_reset_postdata();

    } catch (Exception $e) {
        echo '<p>❌ Basic query failed: ' . esc_html($e->getMessage()) . '</p>';
    }

    // Test 2: Query with purpose filter only
    echo '<h2>Test 2: Purpose Filter Only</h2>';
    $start_time = microtime(true);

    try {
        $args = [
            'post_type' => 'properties',
            'posts_per_page' => 5,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'purpose',
                    'field' => 'slug',
                    'terms' => $purpose,
                ]
            ]
        ];

        $query = new WP_Query($args);
        $end_time = microtime(true);
        $duration = $end_time - $start_time;

        echo '<p>✅ Purpose query completed in ' . number_format($duration, 4) . ' seconds</p>';
        echo '<p>Found ' . $query->found_posts . ' properties with purpose "' . esc_html($purpose) . '"</p>';

    } catch (Exception $e) {
        echo '<p>❌ Purpose query failed: ' . esc_html($e->getMessage()) . '</p>';
    }

    // Test 3: Single-unit properties only (no multi-unit)
    echo '<h2>Test 3: Single-Unit Properties Bedroom Filter</h2>';
    $start_time = microtime(true);

    try {
        $bedrooms_key = ($purpose === 'rent') ? 'rent_bedrooms' : 'bedrooms';

        $args = [
            'post_type' => 'properties',
            'posts_per_page' => 5,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'purpose',
                    'field' => 'slug',
                    'terms' => $purpose,
                ]
            ],
            'meta_query' => [
                'relation' => 'AND',
                // Exclude multi-unit properties
                [
                    'relation' => 'OR',
                    [
                        'key' => 'multi-unit',
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key' => 'multi-unit',
                        'value' => '1',
                        'compare' => '!=',
                    ],
                ],
                // Simple LIKE query for bedrooms (first value only for Test 3)
                [
                    'key' => $bedrooms_key,
                    'value' => 'i:' . $bedroom_values[0] . ';s:4:"true"',
                    'compare' => 'LIKE',
                ],
            ]
        ];

        echo '<p>Query args: ' . json_encode($args) . '</p>';

        $query = new WP_Query($args);
        $end_time = microtime(true);
        $duration = $end_time - $start_time;

        echo '<p>✅ Single-unit bedroom query completed in ' . number_format($duration, 4) . ' seconds</p>';
        echo '<p>Found ' . $query->found_posts . ' single-unit properties with bedroom "' . esc_html($bedroom) . '"</p>';

        if ($query->have_posts()) {
            echo '<ul>';
            while ($query->have_posts()) {
                $query->the_post();
                $bedrooms_value = get_post_meta(get_the_ID(), $bedrooms_key, true);
                $serialized = is_array($bedrooms_value) ? serialize($bedrooms_value) : $bedrooms_value;
                echo '<li>' . get_the_ID() . ': ' . get_the_title() . '<br>';
                echo '<small>JSON: ' . json_encode($bedrooms_value) . '<br>';
                echo 'SERIALIZED: ' . esc_html($serialized) . '</small></li>';
            }
            echo '</ul>';
        }
        wp_reset_postdata();

    } catch (Exception $e) {
        echo '<p>❌ Single-unit bedroom query failed: ' . esc_html($e->getMessage()) . '</p>';
    }

    // Test 4: Multi-unit properties only - COMMENTED OUT
    /*
    echo '<h2>Test 4: Multi-Unit Properties Bedroom Filter</h2>';
    $start_time = microtime(true);

    try {
        $args = [
            'post_type' => 'properties',
            'posts_per_page' => 5,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'purpose',
                    'field' => 'slug',
                    'terms' => $purpose,
                ]
            ],
            'meta_query' => [
                // Only multi-unit properties
                [
                    'key' => 'multi-unit',
                    'value' => '1',
                    'compare' => '=',
                ],
                // Check if multi-unit table contains the bedroom
                [
                    'key' => 'multi-unit_table',
                    'value' => '"unit_bedrooms":"' . $bedroom . '"',
                    'compare' => 'LIKE',
                ],
            ]
        ];

        echo '<p>Query args: ' . json_encode($args) . '</p>';

        $query = new WP_Query($args);
        $end_time = microtime(true);
        $duration = $end_time - $start_time;

        echo '<p>✅ Multi-unit bedroom query completed in ' . number_format($duration, 4) . ' seconds</p>';
        echo '<p>Found ' . $query->found_posts . ' multi-unit properties with bedroom "' . esc_html($bedroom) . '"</p>';

        if ($query->have_posts()) {
            echo '<ul>';
            while ($query->have_posts()) {
                $query->the_post();
                $multi_unit_table = get_post_meta(get_the_ID(), 'multi-unit_table', true);
                echo '<li>' . get_the_ID() . ': ' . get_the_title() . ' (multi-unit table length: ' . strlen($multi_unit_table) . ')</li>';
            }
            echo '</ul>';
        }
        wp_reset_postdata();

    } catch (Exception $e) {
        echo '<p>❌ Multi-unit bedroom query failed: ' . esc_html($e->getMessage()) . '</p>';
    }
    */

    // Test 5: Single-unit query only (multi-unit commented out)
    echo '<h2>Test 5: Single-Unit Bedroom Filter Only (Multi-Unit Commented Out)</h2>';
    $start_time = microtime(true);

    try {
        $bedrooms_key = ($purpose === 'rent') ? 'rent_bedrooms' : 'bedrooms';

        // Ultra-simplified: just check bedrooms field, works for both single and multi-unit
        $meta_query = ['relation' => 'OR'];

        foreach ($bedroom_values as $bedroom_val) {
            $meta_query[] = [
                'key' => $bedrooms_key,
                'value' => 'i:' . $bedroom_val . ';s:4:"true"',
                'compare' => 'LIKE',
            ];
        }

        $args = [
            'post_type' => 'properties',
            'posts_per_page' => 5,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'purpose',
                    'field' => 'slug',
                    'terms' => $purpose,
                ]
            ],
            'meta_query' => $meta_query
        ];

        echo '<p>Meta query: ' . json_encode($meta_query) . '</p>';

        $query = new WP_Query($args);
        $end_time = microtime(true);
        $duration = $end_time - $start_time;

        echo '<p>✅ Single-unit bedroom query completed in ' . number_format($duration, 4) . ' seconds</p>';
        echo '<p>Found ' . $query->found_posts . ' single-unit properties with bedroom "' . esc_html($bedroom) . '"</p>';

        if ($query->have_posts()) {
            echo '<ul>';
            while ($query->have_posts()) {
                $query->the_post();
                $bedrooms_value = get_post_meta(get_the_ID(), $bedrooms_key, true);
                echo '<li>' . get_the_ID() . ': ' . get_the_title() . ' (bedrooms: ' . json_encode($bedrooms_value) . ')</li>';
            }
            echo '</ul>';
        }
        wp_reset_postdata();

    } catch (Exception $e) {
        echo '<p>❌ Combined bedroom query failed: ' . esc_html($e->getMessage()) . '</p>';
    }

    echo '<hr>';
    echo '<p><strong>Usage:</strong></p>';
    echo '<ul>';
    echo '<li>Single value: <code>?bedroom=2&purpose=sale</code></li>';
    echo '<li>Multiple values: <code>?bedroom=2,3,5&purpose=sale</code></li>';
    echo '</ul>';
    echo '<p><strong>Available bedroom values:</strong> 1, 2, 3, 4, 5, 6, 7, 8, 9_plus</p>';
    echo '<p><strong>Available purposes:</strong> sale, rent</p>';

    exit;
}
