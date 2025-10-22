<?php
/**
 * Rent Card View Class
 * 
 * Handles rendering of rent property cards with single column layout
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Rent_Card_View
{
    /**
     * Render rent property card with single column layout
     * 
     * @param int $property_id Property ID
     * @param array $location Location terms
     * @param array $purpose Purpose terms
     * @param string|null $price Formatted price
     * @param bool $isMultiUnit Whether property is multi-unit
     * @param int|null $multiUnitCount Number of units
     * @param string $bedrooms Bedrooms value
     * @param string $bathrooms Bathrooms value
     * @param string $purposeSlug Purpose slug
     */
    public static function render($property_id, $location, $purpose, $price, $isMultiUnit, $multiUnitCount, $bedrooms, $bathrooms, $purposeSlug)
    {
        // Get additional data for rent properties
        $cityArea = KCPF_Card_Data_Helper::getCityArea($property_id);
        $propertyType = KCPF_Card_Data_Helper::getPropertyType($property_id);
        $rentArea = KCPF_Card_Data_Helper::getTotalCoveredArea($property_id, $purposeSlug);
        
        ?>
        <article class="kcpf-property-card kcpf-property-card-rent">
            <?php if (has_post_thumbnail()) : ?>
                <div class="kcpf-property-image-rent">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail('medium'); ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="kcpf-property-content-rent">
                <h2 class="kcpf-property-title-rent">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                </h2>
                
                <div class="kcpf-property-meta-row-rent">
                    <?php if ($location && !is_wp_error($location)) : ?>
                        <span class="kcpf-location-rent"><?php echo esc_html($location[0]->name); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($cityArea) : ?>
                        <span class="kcpf-separator-rent">|</span>
                        <span class="kcpf-city-area-rent"><?php echo esc_html($cityArea); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($propertyType) : ?>
                        <span class="kcpf-separator-rent">|</span>
                        <span class="kcpf-property-type-rent"><?php echo esc_html($propertyType); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="kcpf-property-specs-rent">
                    <?php if ($bedrooms) : ?>
                        <span class="kcpf-bedrooms-rent">
                            <?php echo esc_html($bedrooms); ?> Bed
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($bathrooms) : ?>
                        <span class="kcpf-bathrooms-rent">
                            <?php echo esc_html($bathrooms); ?> Bath
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($rentArea) : ?>
                        <span class="kcpf-area-rent">
                            <?php echo esc_html($rentArea); ?> m²
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if ($price) : ?>
                    <div class="kcpf-property-price-rent">
                        €<?php echo esc_html($price); ?>/mo
                    </div>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }
}

