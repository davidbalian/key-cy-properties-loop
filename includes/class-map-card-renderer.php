<?php
/**
 * Map Card Renderer Class
 * 
 * Renders property cards for map view with data attributes for JavaScript interactivity
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Map_Card_Renderer
{
    /**
     * Render a property card for map view
     * 
     * @param int $property_id Property ID
     * @param string $purpose Property purpose (sale/rent)
     * @return string HTML output
     */
    public static function renderCard($property_id, $purpose = 'sale')
    {
        $location = get_the_terms($property_id, 'location');
        $purposeTerms = get_the_terms($property_id, 'purpose');
        
        // Determine actual purpose from property
        $purposeSlug = 'sale';
        if ($purposeTerms && !is_wp_error($purposeTerms) && !empty($purposeTerms)) {
            $purposeSlug = $purposeTerms[0]->slug;
        }
        
        // Get coordinates
        $coordinates = get_post_meta($property_id, 'display_coordinates', true);
        
        // Get formatted values
        $bedrooms = KCPF_Card_Data_Helper::getBedrooms($property_id, $purposeSlug);
        $bathrooms = KCPF_Card_Data_Helper::getBathrooms($property_id, $purposeSlug);
        $price = KCPF_Card_Data_Helper::getPrice($property_id, $purposeSlug);
        
        // Multi-unit check
        $isMultiUnit = KCPF_Card_Data_Helper::isMultiUnit($property_id);
        $multiUnitPrice = $isMultiUnit ? KCPF_Card_Data_Helper::getMultiUnitPrice($property_id, $purposeSlug) : null;
        
        // Get additional data
        $cityArea = KCPF_Card_Data_Helper::getCityArea($property_id);
        $propertyType = KCPF_Card_Data_Helper::getPropertyType($property_id);
        
        ob_start();
        ?>
        <article class="kcpf-map-card" 
                 data-property-id="<?php echo esc_attr($property_id); ?>"
                 data-coordinates="<?php echo esc_attr($coordinates); ?>">
            <a href="<?php echo get_permalink($property_id); ?>" class="kcpf-map-card-link">
                <?php if (has_post_thumbnail($property_id)) : 
                    $image_url = get_the_post_thumbnail_url($property_id, 'medium');
                ?>
                    <div class="kcpf-map-card-image" style="background-image: url('<?php echo esc_url($image_url); ?>');"></div>
                <?php endif; ?>
                
                <div class="kcpf-map-card-content">
                    <h3 class="kcpf-map-card-title"><?php echo get_the_title($property_id); ?></h3>
                    
                    <div class="kcpf-map-card-meta">
                        <?php if ($cityArea) : ?>
                            <span class="kcpf-map-card-area"><?php echo esc_html($cityArea); ?></span>
                        <?php endif; ?>
                        
                        <?php if ($location && !is_wp_error($location) && !empty($location)) : ?>
                            <?php if ($cityArea) : ?>
                                <span class="kcpf-separator">, </span>
                            <?php endif; ?>
                            <span class="kcpf-map-card-location"><?php echo esc_html($location[0]->name); ?></span>
                        <?php endif; ?>
                        
                        <?php if ($propertyType) : ?>
                            <span class="kcpf-separator"> | </span>
                            <span class="kcpf-map-card-type"><?php echo esc_html($propertyType); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($multiUnitPrice) : ?>
                        <div class="kcpf-map-card-price kcpf-map-card-price-multi">
                            <?php echo esc_html($multiUnitPrice); ?>
                        </div>
                    <?php elseif ($price) : ?>
                        <div class="kcpf-map-card-price">
                            €<?php echo esc_html($price); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="kcpf-map-card-specs">
                        <?php if ($bedrooms) : ?>
                            <span class="kcpf-map-card-spec">
                                <span class="kcpf-spec-icon">🛏️</span>
                                <span class="kcpf-spec-value"><?php echo esc_html($bedrooms); ?></span>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($bathrooms) : ?>
                            <span class="kcpf-map-card-spec">
                                <span class="kcpf-spec-icon">🚿</span>
                                <span class="kcpf-spec-value"><?php echo esc_html($bathrooms); ?></span>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        </article>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render multiple property cards
     * 
     * @param array $property_ids Array of property IDs
     * @param string $purpose Property purpose (sale/rent)
     * @return string HTML output
     */
    public static function renderCards($property_ids, $purpose = 'sale')
    {
        if (empty($property_ids)) {
            return self::renderNoResults();
        }
        
        ob_start();
        echo '<div class="kcpf-map-cards-list">';
        
        foreach ($property_ids as $property_id) {
            echo self::renderCard($property_id, $purpose);
        }
        
        echo '</div>';
        return ob_get_clean();
    }
    
    /**
     * Render no results message
     * 
     * @return string HTML output
     */
    private static function renderNoResults()
    {
        ob_start();
        ?>
        <div class="kcpf-map-no-results">
            <p><?php _e('No properties found matching your filters.', 'key-cy-properties-filter'); ?></p>
            <p><?php _e('Try adjusting your search criteria.', 'key-cy-properties-filter'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
}

