<?php
/**
 * Favourites AJAX Handler
 *
 * Handles AJAX requests for toggling favourites
 *
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Favourites_Ajax
{
    /**
     * Register AJAX handlers
     */
    public static function register()
    {
        add_action('wp_ajax_kcpf_toggle_favourite', [__CLASS__, 'handleToggleFavourite']);
        add_action('wp_ajax_kcpf_refresh_favourites_loop', [__CLASS__, 'handleRefreshFavouritesLoop']);
        // No nopriv - guests are blocked
    }

    /**
     * Handle toggle favourite AJAX request
     */
    public static function handleToggleFavourite()
    {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error([
                'message' => 'Please log in to save favourites'
            ]);
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'kcpf_favourites')) {
            wp_send_json_error([
                'message' => 'Security check failed'
            ]);
        }

        // Get and validate parameters
        $property_id = intval($_POST['property_id'] ?? 0);
        $purpose = sanitize_text_field($_POST['purpose'] ?? 'sale');

        if ($property_id <= 0) {
            wp_send_json_error([
                'message' => 'Invalid property ID'
            ]);
        }

        // Validate property exists and is correct type
        if (get_post_type($property_id) !== 'properties') {
            wp_send_json_error([
                'message' => 'Property not found'
            ]);
        }

        // Toggle favourite
        $is_favourited = KCPF_Favourites_Manager::toggleFavourite($property_id, $purpose);

        // Generate updated button HTML
        $updated_html = KCPF_Favourites_Manager::renderIcon($property_id, $purpose);

        // Return success response
        wp_send_json_success([
            'favourited' => $is_favourited,
            'property_id' => $property_id,
            'html' => $updated_html
        ]);
    }

    /**
     * Handle refresh favourites loop AJAX request
     */
    public static function handleRefreshFavouritesLoop()
    {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error([
                'message' => 'Please log in to view favourites'
            ]);
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'kcpf_favourites')) {
            wp_send_json_error([
                'message' => 'Security check failed'
            ]);
        }

        // Get and validate purpose
        $purpose = sanitize_text_field($_POST['purpose'] ?? 'sale');
        $purpose = KCPF_Favourites_Manager::normalizePurpose($purpose);

        // Get favourites for the purpose
        $ids = KCPF_Favourites_Manager::getFavouritesByPurpose($purpose);

        if (empty($ids)) {
            // Return empty state
            $html = '<div class="kcpf-favourites-empty">';
            $html .= '<h3>' . esc_html__('No favourites yet', 'key-cy-properties-filter') . '</h3>';
            $html .= '<p>' . esc_html__('Tap the star on any property to save it here.', 'key-cy-properties-filter') . '</p>';
            $html .= '</div>';
        } else {
            // Render just the cards without the container wrapper
            $html = '';
            foreach ($ids as $property_id) {
                $html .= KCPF_Map_Card_Renderer::renderCard($property_id, $purpose, false);
            }
        }

        // Return success response
        wp_send_json_success([
            'html' => $html
        ]);
    }
}
