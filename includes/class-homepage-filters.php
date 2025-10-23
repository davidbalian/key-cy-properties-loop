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
        $purposeHtml = KCPF_Filter_Renderer::renderPurpose([
            'type' => 'radio',
            'default' => KCPF_URL_Manager::getFilterValue('purpose') ?: 'sale',
        ]);

        $typeHtml = KCPF_Filter_Renderer::renderType([
            'type' => 'select',
        ]);

        $locationHtml = KCPF_Filter_Renderer::renderLocation([
            'type' => 'select',
            'show_count' => true,
        ]);

        $bedroomsHtml = KCPF_Filter_Renderer::renderBedrooms([
            'type' => 'select',
        ]);

        $priceHtml = KCPF_Filter_Renderer::renderPrice([
            'type' => 'slider',
        ]);

        ob_start();
        ?>
        <div class="kcpf-homepage-filters" data-sale-url="<?php echo esc_attr($attrs['sale_url']); ?>" data-rent-url="<?php echo esc_attr($attrs['rent_url']); ?>">
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


