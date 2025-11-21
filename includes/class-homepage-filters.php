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
        // Detect if Russian language is active using the language detector
        $is_russian = KCPF_Language_Detector::isRussian();
        
        // Ensure text domain is loaded (in case it wasn't loaded yet)
        if (!is_textdomain_loaded('key-cy-properties-filter')) {
            // Use the same path pattern as the main plugin file
            $plugin_file = defined('KCPF_PLUGIN_DIR') 
                ? KCPF_PLUGIN_DIR . 'key-cy-properties-filter.php'
                : dirname(dirname(__FILE__)) . '/key-cy-properties-filter.php';
            
            load_plugin_textdomain(
                'key-cy-properties-filter',
                false,
                dirname(plugin_basename($plugin_file)) . '/languages'
            );
        }
        
        // Set default URLs based on language
        $default_rent_url = $is_russian 
            ? 'https://key-cy.com/ru/%D0%BD%D0%B0%D0%B7%D0%BD%D0%B0%D1%87%D0%B5%D0%BD%D0%B8%D0%B5/%D0%B0%D1%80%D0%B5%D0%BD%D0%B4%D0%B0/'
            : '/purpose/rent';
        $default_sale_url = $is_russian
            ? 'https://key-cy.com/ru/%D0%BD%D0%B0%D0%B7%D0%BD%D0%B0%D1%87%D0%B5%D0%BD%D0%B8%D0%B5/%D0%BF%D1%80%D0%BE%D0%B4%D0%B0%D0%B6%D0%B0/'
            : '/purpose/sale';
        
        // Get translated button text
        $apply_text = __('Filter results', 'key-cy-properties-filter');
        
        $attrs = shortcode_atts([
            'rent_url' => $default_rent_url,
            'sale_url' => $default_sale_url,
            'apply_text' => $apply_text,
        ], $attrs);
        
        // Robust Fallback: if translation didn't work or user passed English text and we're in Russian
        if ($is_russian) {
            $english_variants = ['Filter results', 'Filter Results', 'FILTER RESULTS'];
            // Check if the current text (trimmed) is one of the English variants
            // This fixes cases where translation fails OR user override was "Filter Results"
            if (in_array(trim($attrs['apply_text']), $english_variants)) {
                $attrs['apply_text'] = 'Фильтровать результаты';
            }
            
            // HARDCODE Russian URLs to ensure they are always correct regardless of caching or shortcode attributes
            $attrs['sale_url'] = 'https://key-cy.com/ru/%D0%BD%D0%B0%D0%B7%D0%BD%D0%B0%D1%87%D0%B5%D0%BD%D0%B8%D0%B5/%D0%BF%D1%80%D0%BE%D0%B4%D0%B0%D0%B6%D0%B0/';
            $attrs['rent_url'] = 'https://key-cy.com/ru/%D0%BD%D0%B0%D0%B7%D0%BD%D0%B0%D1%87%D0%B5%D0%BD%D0%B8%D0%B5/%D0%B0%D1%80%D0%B5%D0%BD%D0%B4%D0%B0/';
        }

        // Build inner filters using existing renderers
        // Always default to 'sale' on homepage, ignoring URL parameters
        $currentPurpose = 'sale';
        
        // Force purpose to 'sale' using context override (ignores URL parameters)
        KCPF_URL_Manager::setContextPurpose('sale');
        
        // Clear URL parameters for homepage filters
        $_GET['purpose'] = 'sale';
        $_GET['location'] = [];
        $_GET['property_type'] = [];
        $_GET['bedrooms'] = [];
        $_GET['price_min'] = '';
        $_GET['price_max'] = '';
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


