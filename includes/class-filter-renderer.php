<?php
/**
 * Filter Renderer Class
 * 
 * Renders individual filter shortcodes
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Filter_Renderer
{
    /**
     * Render location filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderLocation($attrs)
    {
        try {
            $attrs = shortcode_atts([
                'type' => 'select',
                'placeholder' => 'Select Location',
                'show_count' => false,
            ], $attrs);
            
            $locations = get_terms([
                'taxonomy' => 'location',
                'hide_empty' => true,
            ]);
            
            if (empty($locations) || is_wp_error($locations)) {
                return '';
            }
        } catch (Exception $e) {
            error_log('KCPF Location Filter Error: ' . $e->getMessage());
            return '';
        }
        
        $current_value = KCPF_URL_Manager::getFilterValue('location');
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-location">
            <label for="kcpf-location"><?php esc_html_e('Location', 'key-cy-properties-filter'); ?></label>
            
            <?php if ($attrs['type'] === 'select') : ?>
                <select id="kcpf-location" name="location" class="kcpf-filter-select">
                    <option value=""><?php echo esc_html($attrs['placeholder']); ?></option>
                    <?php foreach ($locations as $location) : ?>
                        <option value="<?php echo esc_attr($location->slug); ?>" <?php selected($current_value, $location->slug); ?>>
                            <?php echo esc_html($location->name); ?>
                            <?php if ($attrs['show_count']) : ?>
                                (<?php echo $location->count; ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <div class="kcpf-filter-checkboxes">
                    <?php foreach ($locations as $location) : ?>
                        <label class="kcpf-checkbox-label">
                            <input type="checkbox" 
                                   name="location[]" 
                                   value="<?php echo esc_attr($location->slug); ?>"
                                   <?php checked($current_value, $location->slug); ?>>
                            <span><?php echo esc_html($location->name); ?></span>
                            <?php if ($attrs['show_count']) : ?>
                                <span class="kcpf-count">(<?php echo $location->count; ?>)</span>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render purpose filter (Sale/Rent)
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderPurpose($attrs)
    {
        $attrs = shortcode_atts([
            'type' => 'select',
            'default' => 'sale',
        ], $attrs);
        
        $purposes = get_terms([
            'taxonomy' => 'purpose',
            'hide_empty' => true,
        ]);
        
        if (empty($purposes) || is_wp_error($purposes)) {
            return '';
        }
        
        $current_value = KCPF_URL_Manager::getFilterValue('purpose') ?: $attrs['default'];
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-purpose">
            <label><?php esc_html_e('Purpose', 'key-cy-properties-filter'); ?></label>
            
            <?php if ($attrs['type'] === 'select') : ?>
                <select name="purpose" class="kcpf-filter-select">
                    <?php foreach ($purposes as $purpose) : ?>
                        <option value="<?php echo esc_attr($purpose->slug); ?>" <?php selected($current_value, $purpose->slug); ?>>
                            <?php echo esc_html($purpose->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($attrs['type'] === 'toggle') : ?>
                <div class="kcpf-toggle-buttons">
                    <?php foreach ($purposes as $purpose) : ?>
                        <label class="kcpf-toggle-label <?php echo ($current_value === $purpose->slug) ? 'active' : ''; ?>">
                            <input type="radio" 
                                   name="purpose" 
                                   value="<?php echo esc_attr($purpose->slug); ?>"
                                   <?php checked($current_value, $purpose->slug); ?>>
                            <span><?php echo esc_html($purpose->name); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="kcpf-radio-buttons">
                    <?php foreach ($purposes as $purpose) : ?>
                        <label class="kcpf-radio-label">
                            <input type="radio" 
                                   name="purpose" 
                                   value="<?php echo esc_attr($purpose->slug); ?>"
                                   <?php checked($current_value, $purpose->slug); ?>>
                            <span><?php echo esc_html($purpose->name); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render price range filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderPrice($attrs)
    {
        try {
            $attrs = shortcode_atts([
                'type' => 'slider',
                'min' => 0,
                'max' => 10000000,
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
            <label><?php esc_html_e('Price Range', 'key-cy-properties-filter'); ?></label>
            
            <div class="kcpf-range-slider-container">
                <div class="kcpf-range-slider" 
                     data-min="<?php echo esc_attr($attrs['min']); ?>"
                     data-max="<?php echo esc_attr($attrs['max']); ?>"
                     data-step="<?php echo esc_attr($attrs['step']); ?>"
                     data-value-min="<?php echo esc_attr($price_min ?: $attrs['min']); ?>"
                     data-value-max="<?php echo esc_attr($price_max ?: $attrs['max']); ?>"
                     data-format="currency">
                </div>
                
                <div class="kcpf-range-inputs">
                    <input type="number" 
                           name="price_min" 
                           placeholder="Min Price"
                           value="<?php echo esc_attr($price_min); ?>"
                           min="<?php echo esc_attr($attrs['min']); ?>"
                           max="<?php echo esc_attr($attrs['max']); ?>"
                           step="<?php echo esc_attr($attrs['step']); ?>"
                           class="kcpf-input kcpf-range-min">
                    
                    <span class="kcpf-range-separator">-</span>
                    
                    <input type="number" 
                           name="price_max" 
                           placeholder="Max Price"
                           value="<?php echo esc_attr($price_max); ?>"
                           min="<?php echo esc_attr($attrs['min']); ?>"
                           max="<?php echo esc_attr($attrs['max']); ?>"
                           step="<?php echo esc_attr($attrs['step']); ?>"
                           class="kcpf-input kcpf-range-max">
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render bedrooms filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderBedrooms($attrs)
    {
        $attrs = shortcode_atts([
            'type' => 'checkbox',
            'glossary_id' => '7', // JetEngine Bedrooms glossary ID
        ], $attrs);
        
        $current_value = KCPF_URL_Manager::getFilterValue('bedrooms');
        
        // Get bedroom options from JetEngine glossary
        $glossaryOptions = KCPF_Glossary_Handler::getOptionsForRendering($attrs['glossary_id']);
        
        // Fallback if glossary is empty or JetEngine is not available
        if (empty($glossaryOptions)) {
            $glossaryOptions = [
                ['value' => '1', 'label' => '1'],
                ['value' => '2', 'label' => '2'],
                ['value' => '3', 'label' => '3'],
                ['value' => '4', 'label' => '4'],
                ['value' => '5', 'label' => '5'],
                ['value' => '6', 'label' => '6'],
                ['value' => '7', 'label' => '7'],
                ['value' => '8', 'label' => '8'],
                ['value' => '9_plus', 'label' => '9+'],
            ];
        }
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-bedrooms">
            <label><?php esc_html_e('Bedrooms', 'key-cy-properties-filter'); ?></label>
            
            <?php if ($attrs['type'] === 'select') : ?>
                <select name="bedrooms" class="kcpf-filter-select">
                    <option value="">Any</option>
                    <?php foreach ($glossaryOptions as $option) : ?>
                        <option value="<?php echo esc_attr($option['value']); ?>" <?php selected($current_value, $option['value']); ?>>
                            <?php echo esc_html($option['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($attrs['type'] === 'checkbox') : ?>
                <div class="kcpf-filter-checkboxes">
                    <?php 
                    $current_values = is_array($current_value) ? $current_value : [$current_value];
                    foreach ($glossaryOptions as $option) : 
                    ?>
                        <label class="kcpf-checkbox-label">
                            <input type="checkbox" 
                                   name="bedrooms[]" 
                                   value="<?php echo esc_attr($option['value']); ?>"
                                   <?php checked(in_array($option['value'], $current_values)); ?>>
                            <span><?php echo esc_html($option['label']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="kcpf-button-group">
                    <?php foreach ($glossaryOptions as $option) : ?>
                        <label class="kcpf-button-label <?php echo ($current_value == $option['value']) ? 'active' : ''; ?>">
                            <input type="radio" 
                                   name="bedrooms" 
                                   value="<?php echo esc_attr($option['value']); ?>"
                                   <?php checked($current_value, $option['value']); ?>>
                            <span><?php echo esc_html($option['label']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render bathrooms filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderBathrooms($attrs)
    {
        $attrs = shortcode_atts([
            'type' => 'checkbox',
            'glossary_id' => '8', // JetEngine Bathrooms glossary ID
        ], $attrs);
        
        $current_value = KCPF_URL_Manager::getFilterValue('bathrooms');
        
        // Get bathroom options from JetEngine glossary
        $glossaryOptions = KCPF_Glossary_Handler::getOptionsForRendering($attrs['glossary_id']);
        
        // Fallback if glossary is empty or JetEngine is not available
        if (empty($glossaryOptions)) {
            $glossaryOptions = [
                ['value' => '1', 'label' => '1'],
                ['value' => '2', 'label' => '2'],
                ['value' => '3', 'label' => '3'],
                ['value' => '4', 'label' => '4'],
                ['value' => '5', 'label' => '5'],
                ['value' => '6', 'label' => '6'],
                ['value' => '7', 'label' => '7'],
                ['value' => '8_plus', 'label' => '8+'],
            ];
        }
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-bathrooms">
            <label><?php esc_html_e('Bathrooms', 'key-cy-properties-filter'); ?></label>
            
            <?php if ($attrs['type'] === 'select') : ?>
                <select name="bathrooms" class="kcpf-filter-select">
                    <option value="">Any</option>
                    <?php foreach ($glossaryOptions as $option) : ?>
                        <option value="<?php echo esc_attr($option['value']); ?>" <?php selected($current_value, $option['value']); ?>>
                            <?php echo esc_html($option['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($attrs['type'] === 'checkbox') : ?>
                <div class="kcpf-filter-checkboxes">
                    <?php 
                    $current_values = is_array($current_value) ? $current_value : [$current_value];
                    foreach ($glossaryOptions as $option) : 
                    ?>
                        <label class="kcpf-checkbox-label">
                            <input type="checkbox" 
                                   name="bathrooms[]" 
                                   value="<?php echo esc_attr($option['value']); ?>"
                                   <?php checked(in_array($option['value'], $current_values)); ?>>
                            <span><?php echo esc_html($option['label']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="kcpf-button-group">
                    <?php foreach ($glossaryOptions as $option) : ?>
                        <label class="kcpf-button-label <?php echo ($current_value == $option['value']) ? 'active' : ''; ?>">
                            <input type="radio" 
                                   name="bathrooms" 
                                   value="<?php echo esc_attr($option['value']); ?>"
                                   <?php checked($current_value, $option['value']); ?>>
                            <span><?php echo esc_html($option['label']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render property type filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderType($attrs)
    {
        $attrs = shortcode_atts([
            'type' => 'select',
        ], $attrs);
        
        $types = get_terms([
            'taxonomy' => 'property-type',
            'hide_empty' => true,
        ]);
        
        if (empty($types) || is_wp_error($types)) {
            return '';
        }
        
        $current_value = KCPF_URL_Manager::getFilterValue('property_type');
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-type">
            <label><?php esc_html_e('Property Type', 'key-cy-properties-filter'); ?></label>
            
            <?php if ($attrs['type'] === 'select') : ?>
                <select name="property_type" class="kcpf-filter-select">
                    <option value="">All Types</option>
                    <?php foreach ($types as $type) : ?>
                        <option value="<?php echo esc_attr($type->slug); ?>" <?php selected($current_value, $type->slug); ?>>
                            <?php echo esc_html($type->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <div class="kcpf-filter-checkboxes">
                    <?php foreach ($types as $type) : ?>
                        <label class="kcpf-checkbox-label">
                            <input type="checkbox" 
                                   name="property_type[]" 
                                   value="<?php echo esc_attr($type->slug); ?>"
                                   <?php checked($current_value, $type->slug); ?>>
                            <span><?php echo esc_html($type->name); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render apply/submit button
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderApplyButton($attrs)
    {
        $attrs = shortcode_atts([
            'text' => 'Apply Filters',
            'type' => 'reload',
        ], $attrs);
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-apply">
            <button type="submit" class="kcpf-apply-button" data-type="<?php echo esc_attr($attrs['type']); ?>">
                <?php echo esc_html($attrs['text']); ?>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render reset/clear button
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderResetButton($attrs)
    {
        $attrs = shortcode_atts([
            'text' => 'Reset Filters',
        ], $attrs);
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-reset">
            <a href="<?php echo esc_url(KCPF_URL_Manager::getResetUrl()); ?>" class="kcpf-reset-button">
                <?php echo esc_html($attrs['text']); ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render amenities filter
     * 
     * @param array $attrs Shortcode attributes
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
        $current_values = is_array($current_value) ? $current_value : ($current_value ? [$current_value] : []);
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-amenities">
            <label><?php esc_html_e('Amenities', 'key-cy-properties-filter'); ?></label>
            
            <div class="kcpf-filter-checkboxes">
                <?php foreach ($amenitiesOptions as $option) : ?>
                    <label class="kcpf-checkbox-label">
                        <input type="checkbox" 
                               name="amenities[]" 
                               value="<?php echo esc_attr($option['value']); ?>"
                               <?php checked(in_array($option['value'], $current_values)); ?>>
                        <span><?php echo esc_html($option['label']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render covered area filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderCoveredArea($attrs)
    {
        try {
            $attrs = shortcode_atts([
                'min' => 0,
                'max' => 10000,
                'step' => 10,
            ], $attrs);
            
            // Validate attributes
            $attrs['min'] = absint($attrs['min']);
            $attrs['max'] = absint($attrs['max']);
            $attrs['step'] = absint($attrs['step']);
            
            if ($attrs['min'] >= $attrs['max']) {
                error_log('KCPF Covered Area Filter Error: Min must be less than max');
                return '';
            }
            
            if ($attrs['step'] <= 0) {
                $attrs['step'] = 1;
            }
            
            $area_min = KCPF_URL_Manager::getFilterValue('covered_area_min');
            $area_max = KCPF_URL_Manager::getFilterValue('covered_area_max');
        } catch (Exception $e) {
            error_log('KCPF Covered Area Filter Error: ' . $e->getMessage());
            return '';
        }
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-covered-area">
            <label><?php esc_html_e('Covered Area (m²)', 'key-cy-properties-filter'); ?></label>
            
            <div class="kcpf-range-slider-container">
                <div class="kcpf-range-slider" 
                     data-min="<?php echo esc_attr($attrs['min']); ?>"
                     data-max="<?php echo esc_attr($attrs['max']); ?>"
                     data-step="<?php echo esc_attr($attrs['step']); ?>"
                     data-value-min="<?php echo esc_attr($area_min ?: $attrs['min']); ?>"
                     data-value-max="<?php echo esc_attr($area_max ?: $attrs['max']); ?>">
                </div>
                
                <div class="kcpf-range-inputs">
                    <input type="number" 
                           name="covered_area_min" 
                           placeholder="Min"
                           value="<?php echo esc_attr($area_min); ?>"
                           min="<?php echo esc_attr($attrs['min']); ?>"
                           max="<?php echo esc_attr($attrs['max']); ?>"
                           step="<?php echo esc_attr($attrs['step']); ?>"
                           class="kcpf-input kcpf-range-min">
                    
                    <span class="kcpf-range-separator">-</span>
                    
                    <input type="number" 
                           name="covered_area_max" 
                           placeholder="Max"
                           value="<?php echo esc_attr($area_max); ?>"
                           min="<?php echo esc_attr($attrs['min']); ?>"
                           max="<?php echo esc_attr($attrs['max']); ?>"
                           step="<?php echo esc_attr($attrs['step']); ?>"
                           class="kcpf-input kcpf-range-max">
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render plot area filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderPlotArea($attrs)
    {
        try {
            $attrs = shortcode_atts([
                'min' => 0,
                'max' => 50000,
                'step' => 50,
            ], $attrs);
            
            // Validate attributes
            $attrs['min'] = absint($attrs['min']);
            $attrs['max'] = absint($attrs['max']);
            $attrs['step'] = absint($attrs['step']);
            
            if ($attrs['min'] >= $attrs['max']) {
                error_log('KCPF Plot Area Filter Error: Min must be less than max');
                return '';
            }
            
            if ($attrs['step'] <= 0) {
                $attrs['step'] = 1;
            }
            
            $plot_min = KCPF_URL_Manager::getFilterValue('plot_area_min');
            $plot_max = KCPF_URL_Manager::getFilterValue('plot_area_max');
        } catch (Exception $e) {
            error_log('KCPF Plot Area Filter Error: ' . $e->getMessage());
            return '';
        }
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-plot-area">
            <label><?php esc_html_e('Plot Area (m²)', 'key-cy-properties-filter'); ?></label>
            
            <div class="kcpf-range-slider-container">
                <div class="kcpf-range-slider" 
                     data-min="<?php echo esc_attr($attrs['min']); ?>"
                     data-max="<?php echo esc_attr($attrs['max']); ?>"
                     data-step="<?php echo esc_attr($attrs['step']); ?>"
                     data-value-min="<?php echo esc_attr($plot_min ?: $attrs['min']); ?>"
                     data-value-max="<?php echo esc_attr($plot_max ?: $attrs['max']); ?>">
                </div>
                
                <div class="kcpf-range-inputs">
                    <input type="number" 
                           name="plot_area_min" 
                           placeholder="Min"
                           value="<?php echo esc_attr($plot_min); ?>"
                           min="<?php echo esc_attr($attrs['min']); ?>"
                           max="<?php echo esc_attr($attrs['max']); ?>"
                           step="<?php echo esc_attr($attrs['step']); ?>"
                           class="kcpf-input kcpf-range-min">
                    
                    <span class="kcpf-range-separator">-</span>
                    
                    <input type="number" 
                           name="plot_area_max" 
                           placeholder="Max"
                           value="<?php echo esc_attr($plot_max); ?>"
                           min="<?php echo esc_attr($attrs['min']); ?>"
                           max="<?php echo esc_attr($attrs['max']); ?>"
                           step="<?php echo esc_attr($attrs['step']); ?>"
                           class="kcpf-input kcpf-range-max">
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render property ID search filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderPropertyId($attrs)
    {
        $attrs = shortcode_atts([
            'placeholder' => 'Search by Property ID',
        ], $attrs);
        
        $property_id = KCPF_URL_Manager::getFilterValue('property_id');
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-property-id">
            <label for="kcpf-property-id"><?php esc_html_e('Property ID', 'key-cy-properties-filter'); ?></label>
            <input type="text" 
                   id="kcpf-property-id"
                   name="property_id" 
                   placeholder="<?php echo esc_attr($attrs['placeholder']); ?>"
                   value="<?php echo esc_attr($property_id); ?>"
                   class="kcpf-input kcpf-property-id-input">
        </div>
        <?php
        return ob_get_clean();
    }
}

