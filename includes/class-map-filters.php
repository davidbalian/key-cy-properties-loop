<?php
/**
 * Map Filters Class
 * 
 * Renders filter form for map view with purpose-aware filters
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Map_Filters
{
    /**
     * Render map filters form
     * 
     * @param string $purpose Property purpose (sale/rent)
     * @return string HTML output
     */
    public static function render($purpose = 'sale')
    {
        // Set purpose for filter rendering
        $_GET['purpose'] = $purpose;
        
        // Build all filter components
        $typeHtml = KCPF_Filter_Renderer::renderType([
            'type' => 'checkbox',
        ]);
        
        $bedroomsHtml = KCPF_Filter_Renderer::renderBedrooms([
            'type' => 'checkbox',
        ]);
        
        $bathroomsHtml = KCPF_Filter_Renderer::renderBathrooms([
            'type' => 'checkbox',
        ]);
        
        $locationHtml = KCPF_Filter_Renderer::renderLocation([
            'type' => 'checkbox',
            'show_count' => true,
        ]);
        
        $amenitiesHtml = KCPF_Filter_Renderer::renderAmenities([
            'type' => 'checkbox',
        ]);
        
        $priceHtml = KCPF_Filter_Renderer::renderPrice([
            'type' => 'slider',
        ]);
        
        $coveredAreaHtml = KCPF_Filter_Renderer::renderCoveredArea([
            'type' => 'slider',
        ]);
        
        $plotAreaHtml = KCPF_Filter_Renderer::renderPlotArea([
            'type' => 'slider',
        ]);
        
        $propertyIdHtml = KCPF_Filter_Renderer::renderPropertyId([]);
        
        $applyHtml = KCPF_Filter_Renderer::renderApplyButton([
            'text' => __('Apply Filters', 'key-cy-properties-filter'),
        ]);
        
        $resetHtml = KCPF_Filter_Renderer::renderResetButton([
            'text' => __('Reset', 'key-cy-properties-filter'),
        ]);
        
        ob_start();
        ?>
        <div class="kcpf-map-filters" data-purpose="<?php echo esc_attr($purpose); ?>">
            <form class="kcpf-map-filters-form" method="get">
                <input type="hidden" name="purpose" value="<?php echo esc_attr($purpose); ?>">
                
                <div class="kcpf-map-filters-grid">
                    <?php echo $typeHtml; ?>
                    <?php echo $bedroomsHtml; ?>
                    <?php echo $bathroomsHtml; ?>
                    <?php echo $locationHtml; ?>
                    <?php echo $amenitiesHtml; ?>
                    <?php echo $priceHtml; ?>
                    <?php echo $coveredAreaHtml; ?>
                    <?php echo $plotAreaHtml; ?>
                    <?php echo $propertyIdHtml; ?>
                </div>
                
                <div class="kcpf-map-filters-actions">
                    <?php echo $applyHtml; ?>
                    <?php echo $resetHtml; ?>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}

