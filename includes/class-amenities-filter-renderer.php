<?php
/**
 * Amenities Filter Renderer
 * 
 * Renders the amenities filter with multiselect checkboxes
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Amenities_Filter_Renderer extends KCPF_Filter_Renderer_Base
{
    /**
     * Render amenities filter
     * 
     * Displays a multi-select checkbox list for filtering properties by amenities.
     * Options are loaded from JetEngine glossary with fallback to hardcoded values.
     * 
     * @param array $attrs Shortcode attributes
     *                     - type: 'checkbox' (default: 'checkbox')
     *                     - glossary_id: JetEngine glossary ID (default: '24')
     * @return string HTML output
     */
    public static function renderAmenities($attrs)
    {
        try {
            $attrs = shortcode_atts([
                'type' => 'checkbox',
                'glossary_id' => '24', // JetEngine Amenities glossary ID
            ], $attrs);
            
            // Get amenities options from JetEngine glossary
            $amenitiesOptions = KCPF_Glossary_Handler::getOptionsForRendering($attrs['glossary_id']);
            
            // Fallback if glossary is empty or JetEngine is not available
            if (empty($amenitiesOptions)) {
                $amenitiesOptions = [
                    ['value' => 'air-condition', 'label' => 'Air Condition'],
                    ['value' => 'heating', 'label' => 'Heating'],
                    ['value' => 'balcony', 'label' => 'Balcony'],
                    ['value' => 'covered-veranda', 'label' => 'Covered veranda'],
                    ['value' => 'uncovered-veranda', 'label' => 'Uncovered veranda'],
                    ['value' => 'roof-garden', 'label' => 'Roof garden'],
                    ['value' => 'elevator', 'label' => 'Elevator'],
                    ['value' => 'furnished', 'label' => 'Furnished'],
                    ['value' => 'pets-allowed', 'label' => 'Pets Allowed'],
                    ['value' => 'pool', 'label' => 'Pool'],
                    ['value' => 'fitness-center', 'label' => 'Fitness Center'],
                    ['value' => 'sea-view', 'label' => 'Sea view'],
                    ['value' => 'quiet-neighbourhood', 'label' => 'Quiet neighbourhood'],
                    ['value' => 'storage', 'label' => 'Storage'],
                    ['value' => 'covered-parking', 'label' => 'Covered parking'],
                    ['value' => 'spa-sauna', 'label' => 'Spa / Sauna'],
                    ['value' => 'security-alarm', 'label' => 'Security Alarm'],
                    ['value' => 'bbq-zone', 'label' => 'BBQ zone'],
                    ['value' => 'fireplace', 'label' => 'Fireplace'],
                ];
            }
        } catch (Exception $e) {
            error_log('KCPF Amenities Filter Error: ' . $e->getMessage());
            return '';
        }
        
        $current_value = KCPF_URL_Manager::getFilterValue('amenities');
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-amenities">
            <?php echo self::renderMultiselectDropdown(
                'amenities',
                __('Amenities', 'key-cy-properties-filter'),
                $amenitiesOptions,
                $current_value,
                false
            ); ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

