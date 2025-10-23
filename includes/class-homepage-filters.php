<?php
/**
 * Homepage Filters Composite Shortcode
 *
 * Renders purpose-aware homepage filters and a redirecting apply button.
 *
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Homepage_Filters
{
    /**
     * Render the homepage filters shortcode
     *
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function render($attrs)
    {
        $attrs = shortcode_atts([
            'rent_url' => '/test-rent-page',
            'sale_url' => '/test-sale-archive',
            'apply_text' => 'Filter results',
        ], $attrs);

        // Build inner filters using existing renderers
        // Try to render purpose via renderer; if taxonomy missing, fallback to static radio
        $currentPurpose = KCPF_URL_Manager::getFilterValue('purpose') ?: 'sale';
        $purposeHtml = KCPF_Filter_Renderer::renderPurpose([
            'type' => 'radio',
            'default' => $currentPurpose,
        ]);
        if (!$purposeHtml) {
            ob_start();
            ?>
            <div class="kcpf-filter kcpf-filter-purpose">
                <div class="kcpf-radio-buttons">
                    <label class="kcpf-radio-label">
                        <input type="radio" name="purpose" value="sale" <?php checked($currentPurpose, 'sale'); ?>>
                        <span><?php echo esc_html(__('Sale', 'key-cy-properties-filter')); ?></span>
                    </label>
                    <label class="kcpf-radio-label">
                        <input type="radio" name="purpose" value="rent" <?php checked($currentPurpose, 'rent'); ?>>
                        <span><?php echo esc_html(__('Rent', 'key-cy-properties-filter')); ?></span>
                    </label>
                </div>
            </div>
            <?php
            $purposeHtml = ob_get_clean();
        }

        // Multiselect dropdown for property type
        $typeHtml = KCPF_Filter_Renderer::renderType([
            'type' => 'checkbox',
        ]);

        // Multiselect dropdown for location
        $locationHtml = KCPF_Filter_Renderer::renderLocation([
            'type' => 'checkbox',
            'show_count' => true,
        ]);

        // Multiselect dropdown for bedrooms
        $bedroomsHtml = KCPF_Filter_Renderer::renderBedrooms([
            'type' => 'checkbox',
        ]);

        $priceHtml = KCPF_Filter_Renderer::renderPrice([
            'type' => 'slider',
        ]);

        ob_start();
        ?>
        <div class="kcpf-homepage-filters" 
             data-sale-url="<?php echo esc_attr($attrs['sale_url']); ?>" 
             data-rent-url="<?php echo esc_attr($attrs['rent_url']); ?>"
             data-current-purpose="<?php echo esc_attr($currentPurpose); ?>">
            <div class="kcpf-refresh-spinner" style="display: none;">
                <div class="kcpf-spinner"></div>
            </div>
            <form class="kcpf-filters-form" method="get">
                <?php echo $purposeHtml; // Purpose radio ?>
                <?php echo $typeHtml; // Property type ?>
                <?php echo $locationHtml; // Location ?>
                <?php echo $bedroomsHtml; // Bedrooms ?>
                <?php echo $priceHtml; // Price range ?>

                <div class="kcpf-filter kcpf-filter-apply">
                    <button type="submit"
                            class="kcpf-apply-button"
                            data-type="redirect"
                            data-rent-url="<?php echo esc_attr($attrs['rent_url']); ?>"
                            data-sale-url="<?php echo esc_attr($attrs['sale_url']); ?>">
                        <?php echo esc_html($attrs['apply_text']); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}


