<?php
/**
 * Price Filter Renderer
 * 
 * Renders the price range filter with slider
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Price_Filter_Renderer extends KCPF_Filter_Renderer_Base
{
    /**
     * Render price range filter
     * 
     * Displays a range slider for filtering properties by price.
     * Automatically determines min/max values based on available listings
     * for the current purpose (sale/rent).
     * 
     * @param array $attrs Shortcode attributes
     *                     - type: 'slider' (default: 'slider')
     *                     - min: Minimum price (auto-detected if not specified)
     *                     - max: Maximum price (auto-detected if not specified)
     *                     - step: Step increment (default: 10000)
     * @return string HTML output
     */
    public static function renderPrice($attrs)
    {
        try {
            // Get purpose from URL or use default
            $purpose = KCPF_URL_Manager::getFilterValue('purpose') ?: 'sale';
            
            // Get min/max values from actual listings
            $range = KCPF_Listing_Values::getMinMax('price', $purpose);
            
            $attrs = shortcode_atts([
                'type' => 'slider',
                'min' => $range['min'],
                'max' => $range['max'],
                'step' => 10000,
            ], $attrs);
            
            // Validate attributes
            $attrs['min'] = absint($attrs['min']);
            $attrs['max'] = absint($attrs['max']);
            $attrs['step'] = absint($attrs['step']);
            
            if ($attrs['min'] >= $attrs['max']) {
                error_log('KCPF Price Filter Error: Min must be less than max');
                return '';
            }
            
            if ($attrs['step'] <= 0) {
                $attrs['step'] = 1;
            }
            
            $price_min = KCPF_URL_Manager::getFilterValue('price_min');
            $price_max = KCPF_URL_Manager::getFilterValue('price_max');
        } catch (Exception $e) {
            error_log('KCPF Price Filter Error: ' . $e->getMessage());
            return '';
        }
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-price">
            <?php echo self::renderRangeFilter(
                'price',
                __('Price Range', 'key-cy-properties-filter'),
                $attrs['min'],
                $attrs['max'],
                $attrs['step'],
                $price_min,
                $price_max,
                '€',
                '',
                'currency'
            ); ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

