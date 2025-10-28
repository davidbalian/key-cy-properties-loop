<?php
/**
 * Favourites Shortcode
 *
 * Renders a non-filterable loop of the user's favourite properties
 * for the specified purpose (sale|rent).
 *
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Favourites_Shortcode
{
    /**
     * Render shortcode handler
     *
     * Usage: [kcpf_favourites purpose="sale|rent"]
     *
     * @param array $atts
     * @return string
     */
    public static function render($atts = [])
    {
        $atts = shortcode_atts([
            'purpose' => 'sale'
        ], $atts, 'kcpf_favourites');

        $purpose = KCPF_Favourites_Manager::normalizePurpose($atts['purpose']);

        // Always create the same container structure
        $modifierClass = $purpose === 'rent' ? ' kcpf-favourites-loop-rent' : ' kcpf-favourites-loop-sale';
        $html = '<div class="kcpf-favourites-loop' . $modifierClass . '">';

        if (!is_user_logged_in()) {
            $html .= self::renderGuestMessage();
        } else {
            $ids = KCPF_Favourites_Manager::getFavouritesByPurpose($purpose);

            if (empty($ids)) {
                $html .= self::renderEmptyState($purpose);
            } else {
                // Render cards individually to avoid the kcpf-properties-grid wrapper
                foreach ($ids as $property_id) {
                    $html .= KCPF_Map_Card_Renderer::renderCard($property_id, $purpose, false);
                }
            }
        }
        
        $html .= '</div>';

        return $html;
    }

    /**
     * Render empty state
     *
     * @param string $purpose
     * @return string
     */
    private static function renderEmptyState($purpose)
    {
        ob_start();
        ?>
        <div class="kcpf-favourites-empty">
            <h3><?php echo esc_html__('No favourites yet', 'key-cy-properties-filter'); ?></h3>
            <p><?php echo esc_html__('Tap the star on any property to save it here.', 'key-cy-properties-filter'); ?></p>
        </div>
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
        <div class="kcpf-favourites-empty">
            <h3><?php echo esc_html__('Sign in to view favourites', 'key-cy-properties-filter'); ?></h3>
            <p><?php echo esc_html__('You need to be logged in to save and view favourites.', 'key-cy-properties-filter'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
}


