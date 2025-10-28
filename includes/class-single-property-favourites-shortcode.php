<?php
/**
 * Single Property Favourites Shortcode
 *
 * Renders a favourites button for single property pages that allows users
 * to add/remove properties from their favourites list.
 *
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Single_Property_Favourites_Shortcode
{
    /**
     * Render shortcode handler
     *
     * Usage: [single_property_favourites_button]
     *
     * @param array $atts
     * @return string
     */
    public static function render($atts = [])
    {
        // Only render on single property pages
        if (!is_singular('properties')) {
            return '';
        }

        global $post;
        $property_id = $post->ID;

        // Get the purpose taxonomy for this property
        $purpose_terms = get_the_terms($property_id, 'purpose');
        $purpose = 'sale'; // default

        if ($purpose_terms && !is_wp_error($purpose_terms) && !empty($purpose_terms)) {
            $purpose = $purpose_terms[0]->slug;
        }

        // Normalize purpose
        $purpose = KCPF_Favourites_Manager::normalizePurpose($purpose);

        // Check if user is logged in
        if (!is_user_logged_in()) {
            return self::renderGuestMessage();
        }

        // Check if property is currently favourited
        $is_favourited = KCPF_Favourites_Manager::isFavourited($property_id, $purpose);

        // Generate button HTML
        return self::renderButton($property_id, $purpose, $is_favourited);
    }

    /**
     * Render the favourites button
     *
     * @param int $property_id
     * @param string $purpose
     * @param bool $is_favourited
     * @return string
     */
    private static function renderButton($property_id, $purpose, $is_favourited)
    {
        $text = $is_favourited ? 'Remove from Favourites' : 'Save to Favourites';
        $icon = $is_favourited ? KCPF_Favourites_Manager::getFilledStarIcon() : KCPF_Favourites_Manager::getOutlineStarIcon();

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
            <span class="kcpf-favourite-icon kcpf-single-property-favourite-icon" aria-hidden="true">
                <?php echo $icon; ?>
            </span>
            <span class="kcpf-single-property-favourite-text"><?php echo esc_html($text); ?></span>
        </button>
        <?php
        return ob_get_clean();
    }

    /**
     * Render guest message
     *
     * @return string
     */
    private static function renderGuestMessage()
    {
        ob_start();
        ?>
        <div class="kcpf-single-property-favourite-guest">
            <p><?php echo esc_html__('Please log in to save this property to your favourites.', 'key-cy-properties-filter'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
}
