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
     * Get taxonomy terms filtered by purpose
     * 
     * @param string $taxonomy Taxonomy name
     * @param string $purpose Property purpose (sale or rent)
     * @return array Filtered terms
     */
    private static function getTermsByPurpose($taxonomy, $purpose = 'sale')
    {
        // Get all terms
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
        ]);
        
        if (empty($terms) || is_wp_error($terms)) {
            return [];
        }
        
        // Filter terms to only include those with properties for the given purpose
        $filtered_terms = [];
        
        foreach ($terms as $term) {
            // Check if this term has any properties with the given purpose
            $args = [
                'post_type' => 'properties',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'tax_query' => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'terms' => $term->term_id,
                    ],
                    [
                        'taxonomy' => 'purpose',
                        'field' => 'slug',
                        'terms' => $purpose,
                    ],
                ],
            ];
            
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                // Update the count to reflect only properties with this purpose
                $term->count = $query->found_posts;
                $filtered_terms[] = $term;
            }
            
            wp_reset_postdata();
        }
        
        return $filtered_terms;
    }
    
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
            
            // Get purpose from URL or use default
            $purpose = KCPF_URL_Manager::getFilterValue('purpose') ?: 'sale';
            
            // Get locations filtered by purpose
            $locations = self::getTermsByPurpose('location', $purpose);
            
            if (empty($locations)) {
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
            <?php if ($attrs['type'] === 'select') : ?>
                <select id="kcpf-location" name="location" class="kcpf-filter-select">
                    <option value=""><?php echo esc_html($attrs['placeholder'] ?: __('Location', 'key-cy-properties-filter')); ?></option>
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
                <div class="kcpf-multiselect-dropdown" data-filter-name="location">
                    <div class="kcpf-multiselect-trigger">
                        <div class="kcpf-multiselect-selected">
                            <?php 
                            $current_values = is_array($current_value) ? $current_value : ($current_value ? [$current_value] : []);
                            if (empty($current_values)) : ?>
                                <span class="kcpf-placeholder"><?php echo esc_html(__('Location', 'key-cy-properties-filter')); ?></span>
                            <?php else: ?>
                                <?php foreach ($current_values as $val) : 
                                    $location_obj = array_filter($locations, function($loc) use ($val) { return $loc->slug === $val; });
                                    $location_obj = !empty($location_obj) ? reset($location_obj) : null;
                                ?>
                                    <span class="kcpf-chip">
                                        <?php echo esc_html($location_obj ? $location_obj->name : $val); ?>
                                        <span class="kcpf-chip-remove" data-value="<?php echo esc_attr($val); ?>">&times;</span>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <span class="kcpf-multiselect-arrow">▼</span>
                    </div>
                    <div class="kcpf-multiselect-dropdown-menu">
                        <?php foreach ($locations as $location) : ?>
                            <label class="kcpf-multiselect-option">
                                <input type="checkbox" 
                                       name="location[]" 
                                       value="<?php echo esc_attr($location->slug); ?>"
                                       <?php checked(in_array($location->slug, $current_values)); ?>>
                                <span><?php echo esc_html($location->name); ?></span>
                                <?php if ($attrs['show_count']) : ?>
                                    <span class="kcpf-count">(<?php echo $location->count; ?>)</span>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
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
            
            // Calculate display value
            $display_value = '';
            if ($price_min || $price_max) {
                $min_display = $price_min ? number_format($price_min) : '';
                $max_display = $price_max ? number_format($price_max) : '';
                if ($min_display && $max_display) {
                    $display_value = $min_display . ' - ' . $max_display;
                } elseif ($min_display) {
                    $display_value = 'From ' . $min_display;
                } elseif ($max_display) {
                    $display_value = 'Up to ' . $max_display;
                }
            }
        } catch (Exception $e) {
            error_log('KCPF Price Filter Error: ' . $e->getMessage());
            return '';
        }
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-price">
            <div class="kcpf-range-dropdown">
                <div class="kcpf-range-trigger">
                    <div class="kcpf-range-display">
                        <?php if ($display_value) : ?>
                            <span><?php echo esc_html($display_value); ?></span>
                        <?php else : ?>
                            <span class="kcpf-placeholder"><?php esc_html_e('Price Range', 'key-cy-properties-filter'); ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="kcpf-multiselect-arrow">▼</span>
                </div>
                
                <div class="kcpf-range-dropdown-menu">
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
            <?php if ($attrs['type'] === 'select') : ?>
                <select name="bedrooms" class="kcpf-filter-select">
                    <option value=""><?php echo esc_html(__('Bedrooms', 'key-cy-properties-filter')); ?></option>
                    <?php foreach ($glossaryOptions as $option) : ?>
                        <option value="<?php echo esc_attr($option['value']); ?>" <?php selected($current_value, $option['value']); ?>>
                            <?php echo esc_html($option['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($attrs['type'] === 'checkbox') : ?>
                <div class="kcpf-multiselect-dropdown" data-filter-name="bedrooms">
                    <div class="kcpf-multiselect-trigger">
                        <div class="kcpf-multiselect-selected">
                            <?php 
                            $current_values = is_array($current_value) ? $current_value : ($current_value ? [$current_value] : []);
                            if (empty($current_values)) : ?>
                                <span class="kcpf-placeholder"><?php echo esc_html(__('Bedrooms', 'key-cy-properties-filter')); ?></span>
                            <?php else: ?>
                                <?php foreach ($current_values as $val) : 
                                    $option = array_filter($glossaryOptions, function($opt) use ($val) { return $opt['value'] === $val; });
                                    $option = !empty($option) ? reset($option) : ['label' => $val];
                                ?>
                                    <span class="kcpf-chip">
                                        <?php echo esc_html($option['label']); ?>
                                        <span class="kcpf-chip-remove" data-value="<?php echo esc_attr($val); ?>">&times;</span>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <span class="kcpf-multiselect-arrow">▼</span>
                    </div>
                    <div class="kcpf-multiselect-dropdown-menu">
                        <?php foreach ($glossaryOptions as $option) : ?>
                            <label class="kcpf-multiselect-option">
                                <input type="checkbox" 
                                       name="bedrooms[]" 
                                       value="<?php echo esc_attr($option['value']); ?>"
                                       <?php checked(in_array($option['value'], $current_values)); ?>>
                                <span><?php echo esc_html($option['label']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
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
            <?php if ($attrs['type'] === 'select') : ?>
                <select name="bathrooms" class="kcpf-filter-select">
                    <option value=""><?php echo esc_html(__('Bathrooms', 'key-cy-properties-filter')); ?></option>
                    <?php foreach ($glossaryOptions as $option) : ?>
                        <option value="<?php echo esc_attr($option['value']); ?>" <?php selected($current_value, $option['value']); ?>>
                            <?php echo esc_html($option['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($attrs['type'] === 'checkbox') : ?>
                <div class="kcpf-multiselect-dropdown" data-filter-name="bathrooms">
                    <div class="kcpf-multiselect-trigger">
                        <div class="kcpf-multiselect-selected">
                            <?php 
                            $current_values = is_array($current_value) ? $current_value : ($current_value ? [$current_value] : []);
                            if (empty($current_values)) : ?>
                                <span class="kcpf-placeholder"><?php echo esc_html(__('Bathrooms', 'key-cy-properties-filter')); ?></span>
                            <?php else: ?>
                                <?php foreach ($current_values as $val) : 
                                    $option = array_filter($glossaryOptions, function($opt) use ($val) { return $opt['value'] === $val; });
                                    $option = !empty($option) ? reset($option) : ['label' => $val];
                                ?>
                                    <span class="kcpf-chip">
                                        <?php echo esc_html($option['label']); ?>
                                        <span class="kcpf-chip-remove" data-value="<?php echo esc_attr($val); ?>">&times;</span>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <span class="kcpf-multiselect-arrow">▼</span>
                    </div>
                    <div class="kcpf-multiselect-dropdown-menu">
                        <?php foreach ($glossaryOptions as $option) : ?>
                            <label class="kcpf-multiselect-option">
                                <input type="checkbox" 
                                       name="bathrooms[]" 
                                       value="<?php echo esc_attr($option['value']); ?>"
                                       <?php checked(in_array($option['value'], $current_values)); ?>>
                                <span><?php echo esc_html($option['label']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
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
        
        // Get purpose from URL or use default
        $purpose = KCPF_URL_Manager::getFilterValue('purpose') ?: 'sale';
        
        // Get property types filtered by purpose
        $types = self::getTermsByPurpose('property-type', $purpose);
        
        if (empty($types)) {
            return '';
        }
        
        $current_value = KCPF_URL_Manager::getFilterValue('property_type');
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-type">
            <?php if ($attrs['type'] === 'select') : ?>
                <select name="property_type" class="kcpf-filter-select">
                    <option value=""><?php echo esc_html(__('Property Type', 'key-cy-properties-filter')); ?></option>
                    <?php foreach ($types as $type) : ?>
                        <option value="<?php echo esc_attr($type->slug); ?>" <?php selected($current_value, $type->slug); ?>>
                            <?php echo esc_html($type->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <div class="kcpf-multiselect-dropdown" data-filter-name="property_type">
                    <div class="kcpf-multiselect-trigger">
                        <div class="kcpf-multiselect-selected">
                            <?php 
                            $current_values = is_array($current_value) ? $current_value : ($current_value ? [$current_value] : []);
                            if (empty($current_values)) : ?>
                                <span class="kcpf-placeholder"><?php echo esc_html(__('Property Type', 'key-cy-properties-filter')); ?></span>
                            <?php else: ?>
                                <?php foreach ($current_values as $val) : 
                                    $type_obj = array_filter($types, function($t) use ($val) { return $t->slug === $val; });
                                    $type_obj = !empty($type_obj) ? reset($type_obj) : null;
                                ?>
                                    <span class="kcpf-chip">
                                        <?php echo esc_html($type_obj ? $type_obj->name : $val); ?>
                                        <span class="kcpf-chip-remove" data-value="<?php echo esc_attr($val); ?>">&times;</span>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <span class="kcpf-multiselect-arrow">▼</span>
                    </div>
                    <div class="kcpf-multiselect-dropdown-menu">
                        <?php foreach ($types as $type) : ?>
                            <label class="kcpf-multiselect-option">
                                <input type="checkbox" 
                                       name="property_type[]" 
                                       value="<?php echo esc_attr($type->slug); ?>"
                                       <?php checked(in_array($type->slug, $current_values)); ?>>
                                <span><?php echo esc_html($type->name); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
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
            <button type="button" class="kcpf-reset-button">
                <?php echo esc_html($attrs['text']); ?>
            </button>
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
            <div class="kcpf-multiselect-dropdown" data-filter-name="amenities">
                <div class="kcpf-multiselect-trigger">
                    <div class="kcpf-multiselect-selected">
                        <?php if (empty($current_values)) : ?>
                            <span class="kcpf-placeholder"><?php echo esc_html(__('Amenities', 'key-cy-properties-filter')); ?></span>
                        <?php else: ?>
                            <?php foreach ($current_values as $val) : 
                                $option = array_filter($amenitiesOptions, function($opt) use ($val) { return $opt['value'] === $val; });
                                $option = !empty($option) ? reset($option) : ['label' => $val];
                            ?>
                                <span class="kcpf-chip">
                                    <?php echo esc_html($option['label']); ?>
                                    <span class="kcpf-chip-remove" data-value="<?php echo esc_attr($val); ?>">&times;</span>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <span class="kcpf-multiselect-arrow">▼</span>
                </div>
                <div class="kcpf-multiselect-dropdown-menu">
                    <?php foreach ($amenitiesOptions as $option) : ?>
                        <label class="kcpf-multiselect-option">
                            <input type="checkbox" 
                                   name="amenities[]" 
                                   value="<?php echo esc_attr($option['value']); ?>"
                                   <?php checked(in_array($option['value'], $current_values)); ?>>
                            <span><?php echo esc_html($option['label']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
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
            // Get purpose from URL or use default
            $purpose = KCPF_URL_Manager::getFilterValue('purpose') ?: 'sale';
            
            // Get min/max values from actual listings
            $range = KCPF_Listing_Values::getMinMax('covered_area', $purpose);
            
            $attrs = shortcode_atts([
                'min' => $range['min'],
                'max' => $range['max'],
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
            
            // Calculate display value
            $display_value = '';
            if ($area_min || $area_max) {
                $min_display = $area_min ? number_format($area_min) : '';
                $max_display = $area_max ? number_format($area_max) : '';
                if ($min_display && $max_display) {
                    $display_value = $min_display . ' - ' . $max_display . ' m²';
                } elseif ($min_display) {
                    $display_value = 'From ' . $min_display . ' m²';
                } elseif ($max_display) {
                    $display_value = 'Up to ' . $max_display . ' m²';
                }
            }
        } catch (Exception $e) {
            error_log('KCPF Covered Area Filter Error: ' . $e->getMessage());
            return '';
        }
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-covered-area">
            <div class="kcpf-range-dropdown">
                <div class="kcpf-range-trigger">
                    <div class="kcpf-range-display">
                        <?php if ($display_value) : ?>
                            <span><?php echo esc_html($display_value); ?></span>
                        <?php else : ?>
                            <span class="kcpf-placeholder"><?php esc_html_e('Covered Area, m²', 'key-cy-properties-filter'); ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="kcpf-multiselect-arrow">▼</span>
                </div>
                
                <div class="kcpf-range-dropdown-menu">
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
            // Get purpose from URL or use default
            $purpose = KCPF_URL_Manager::getFilterValue('purpose') ?: 'sale';
            
            // Get min/max values from actual listings
            $range = KCPF_Listing_Values::getMinMax('plot_area', $purpose);
            
            $attrs = shortcode_atts([
                'min' => $range['min'],
                'max' => $range['max'],
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
            
            // Calculate display value
            $display_value = '';
            if ($plot_min || $plot_max) {
                $min_display = $plot_min ? number_format($plot_min) : '';
                $max_display = $plot_max ? number_format($plot_max) : '';
                if ($min_display && $max_display) {
                    $display_value = $min_display . ' - ' . $max_display . ' m²';
                } elseif ($min_display) {
                    $display_value = 'From ' . $min_display . ' m²';
                } elseif ($max_display) {
                    $display_value = 'Up to ' . $max_display . ' m²';
                }
            }
        } catch (Exception $e) {
            error_log('KCPF Plot Area Filter Error: ' . $e->getMessage());
            return '';
        }
        
        ob_start();
        ?>
        <div class="kcpf-filter kcpf-filter-plot-area">
            <div class="kcpf-range-dropdown">
                <div class="kcpf-range-trigger">
                    <div class="kcpf-range-display">
                        <?php if ($display_value) : ?>
                            <span><?php echo esc_html($display_value); ?></span>
                        <?php else : ?>
                            <span class="kcpf-placeholder"><?php esc_html_e('Plot Area, m²', 'key-cy-properties-filter'); ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="kcpf-multiselect-arrow">▼</span>
                </div>
                
                <div class="kcpf-range-dropdown-menu">
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
            <input type="text" 
                   id="kcpf-property-id"
                   name="property_id" 
                   placeholder="<?php echo esc_attr($attrs['placeholder'] ?: __('Property ID', 'key-cy-properties-filter')); ?>"
                   value="<?php echo esc_attr($property_id); ?>"
                   class="kcpf-input kcpf-property-id-input">
        </div>
        <?php
        return ob_get_clean();
    }
}

