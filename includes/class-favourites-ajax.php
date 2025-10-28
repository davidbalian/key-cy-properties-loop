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

        // Check if this is a single property page button
        $is_single_property = isset($_POST['is_single_property']) && $_POST['is_single_property'] === 'true';
        
        // Generate appropriate button HTML
        if ($is_single_property) {
            $updated_html = self::renderSinglePropertyButton($property_id, $purpose, $is_favourited);
        } else {
            $updated_html = KCPF_Favourites_Manager::renderIcon($property_id, $purpose);
        }

        // Return success response
        wp_send_json_success([
            'favourited' => $is_favourited,
            'property_id' => $property_id,
            'html' => $updated_html
        ]);
    }

    /**
     * Render single property button HTML
     *
     * @param int $property_id
     * @param string $purpose
     * @param bool $is_favourited
     * @return string
     */
    private static function renderSinglePropertyButton($property_id, $purpose, $is_favourited)
    {
        $text = $is_favourited ? 'Remove from Favourites' : 'Save to Favourites';
        $icon_class = $is_favourited ? 'fas fa-star' : 'far fa-star';

        $classes = 'kcpf-favourite-btn kcpf-single-property-favourite-btn';
        if ($is_favourited) {
            $classes .= ' is-active';
        }

        $attrs = sprintf(
            'class="%s" data-property-id="%d" data-purpose="%s" aria-pressed="%s"',
            esc_attr($classes),
            $property_id,
            esc_attr($purpose),
            $is_favourited ? 'true' : 'false'
        );

        ob_start();
        ?>
        <button type="button" <?php echo $attrs; ?>>
            <i class="kcpf-single-property-favourite-icon <?php echo esc_attr($icon_class); ?>" aria-hidden="true"></i>
            <span class="kcpf-single-property-favourite-text"><?php echo esc_html($text); ?></span>
        </button>
        <?php
        return ob_get_clean();
    }

}
