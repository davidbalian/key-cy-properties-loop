<?php
/**
 * Bathroom Filter Debug Script
 * Test bathroom filtering with detailed error messages
 *
 * Usage: ?bathroom=2&purpose=sale
 */

add_action('wp', 'debug_bathroom_filter');

function debug_bathroom_filter() {
    if (!isset($_GET['bathroom'])) {
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_die('Admin access required');
    }

    $purpose = isset($_GET['purpose']) ? sanitize_text_field($_GET['purpose']) : 'sale';

    // Handle multiple bathroom values (comma-separated)
    $bathroom_values = [];
    if (isset($_GET['bathroom']) && !empty($_GET['bathroom'])) {
        $bathroom_param = sanitize_text_field($_GET['bathroom']);
        if (strpos($bathroom_param, ',') !== false) {
            // Multiple values separated by commas
            $bathroom_values = array_map('trim', explode(',', $bathroom_param));
            $bathroom_values = array_map('sanitize_text_field', $bathroom_values);
        } else {
            // Single value
            $bathroom_values = [sanitize_text_field($bathroom_param)];
        }
    }

    $bathroom_display = implode(', ', $bathroom_values);

    echo '<h1>Bathroom Filter Debug</h1>';
    echo '<p>Testing bathroom filter: <strong>' . esc_html($bathroom_display) . '</strong> for purpose: <strong>' . esc_html($purpose) . '</strong></p>';
    echo '<p>Bathroom values array: ' . json_encode($bathroom_values) . '</p>';

    // Test 1: Basic Properties Query
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

    // Test 2: Purpose Filter Only
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

    // Test 3: Bathroom Filter
    echo '<h2>Test 3: Bathroom Filter</h2>';
    $start_time = microtime(true);

    try {
        $bathrooms_key = ($purpose === 'rent') ? 'rent_bathrooms' : 'bathrooms';

        // Ultra-simplified: just check bathrooms field, works for both single and multi-unit
        $meta_query = ['relation' => 'OR'];

        foreach ($bathroom_values as $bathroom_val) {
            $meta_query[] = [
                'key' => $bathrooms_key,
                'value' => ':"' . $bathroom_val . '";s:4:"true"',
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

        echo '<p>✅ Bathroom query completed in ' . number_format($duration, 4) . ' seconds</p>';
        echo '<p>Found ' . $query->found_posts . ' properties with bathroom "' . esc_html($bathroom_display) . '"</p>';

        if ($query->have_posts()) {
            echo '<ul>';
            while ($query->have_posts()) {
                $query->the_post();
                $bathrooms_value = get_post_meta(get_the_ID(), $bathrooms_key, true);
                $serialized = is_array($bathrooms_value) ? serialize($bathrooms_value) : $bathrooms_value;
                echo '<li>' . get_the_ID() . ': ' . get_the_title() . '<br>';
                echo '<small>JSON: ' . json_encode($bathrooms_value) . '<br>';
                echo 'SERIALIZED: ' . esc_html($serialized) . '</small></li>';
            }
            echo '</ul>';
        }
        wp_reset_postdata();

    } catch (Exception $e) {
        echo '<p>❌ Bathroom query failed: ' . esc_html($e->getMessage()) . '</p>';
    }

    echo '<hr>';
    echo '<p><strong>Usage:</strong></p>';
    echo '<ul>';
    echo '<li>Single value: <code>?bathroom=2&purpose=sale</code></li>';
    echo '<li>Multiple values: <code>?bathroom=2,3,5&purpose=sale</code></li>';
    echo '</ul>';
    echo '<p><strong>Available bathroom values:</strong> 1, 2, 3, 4, 5, 6, 7, 8+</p>';
    echo '<p><strong>Available purposes:</strong> sale, rent</p>';

    exit;
}
