<?php
// Load WordPress environment
require_once('wp-load.php');

// Set header to text/plain
header('Content-Type: text/plain');

echo "=== Debugging Language and Terms ===\n";
echo "Current Locale: " . get_locale() . "\n";
echo "Is Russian (KCPF): " . (KCPF_Language_Detector::isRussian() ? 'Yes' : 'No') . "\n";

echo "\n=== Purpose Terms ===\n";
$terms = get_terms([
    'taxonomy' => 'purpose',
    'hide_empty' => false,
]);

if (is_wp_error($terms)) {
    echo "Error fetching terms: " . $terms->get_error_message() . "\n";
} else {
    foreach ($terms as $term) {
        echo "ID: " . $term->term_id . "\n";
        echo "Name: " . $term->name . "\n";
        echo "Slug: " . $term->slug . "\n";
        
        // Check translations if Polylang is active
        if (function_exists('pll_get_term')) {
            $tr_id = pll_get_term($term->term_id, 'ru');
            if ($tr_id) {
                $tr_term = get_term($tr_id, 'purpose');
                if ($tr_term && !is_wp_error($tr_term)) {
                    echo "  Russian Translation ID: " . $tr_id . "\n";
                    echo "  Russian Name: " . $tr_term->name . "\n";
                    echo "  Russian Slug: " . $tr_term->slug . "\n";
                }
            } else {
                echo "  No Russian translation found via pll_get_term\n";
            }
        }
        echo "----------------\n";
    }
}

echo "\n=== Simulating Russian Request ===\n";
// Mocking a Russian locale switch if possible (might not work fully in CLI/script context without full WP reload)
// But we can check if we can retrieve terms in Russian if we query them explicitly

if (function_exists('pll_current_language')) {
    echo "Current Polylang Language: " . pll_current_language() . "\n";
}

echo "\n=== KCPF_Homepage_Filters Output ===\n";
// Render shortcode to see URLs
echo KCPF_Homepage_Filters::render([]);

