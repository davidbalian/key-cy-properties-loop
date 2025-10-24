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
     * @param string|null $bedroomsRange Bedrooms range for multi-unit
     * @param string|null $bathroomsRange Bathrooms range for multi-unit
     * @param string|null $coveredAreaRange Covered area range for multi-unit
     * @param string|null $plotArea Plot area for land properties
     * @param bool $isLand Whether property is land type
     */
    public static function render($property_id, $location, $purpose, $price, $isMultiUnit, $multiUnitCount, $bedrooms, $bathrooms, $purposeSlug, $bedroomsRange = null, $bathroomsRange = null, $coveredAreaRange = null, $plotArea = null, $isLand = false)
    {
        // Get additional data for rent properties
        $cityArea = KCPF_Card_Data_Helper::getCityArea($property_id);
        $propertyType = KCPF_Card_Data_Helper::getPropertyType($property_id);
        $rentArea = KCPF_Card_Data_Helper::getTotalCoveredArea($property_id, $purposeSlug);
        
        ?>
        <article class="kcpf-property-card kcpf-property-card-rent" data-property-id="<?php echo esc_attr($property_id); ?>">
            <a href="<?php echo get_permalink($property_id); ?>" class="kcpf-property-card-link">
                <?php if (has_post_thumbnail($property_id)) : 
                    $image_url = get_the_post_thumbnail_url($property_id, 'full');
                ?>
                    <div class="kcpf-property-image-rent" style="background-image: url('<?php echo esc_url($image_url); ?>');">
                    </div>
                <?php endif; ?>
                
                <div class="kcpf-property-content-rent">
                    <h2 class="kcpf-property-title-rent">
                        <?php echo get_the_title($property_id); ?>
                    </h2>
                
                <div class="kcpf-property-meta-row-rent">
                    <?php if ($location && !is_wp_error($location)) : ?>
                        <span class="kcpf-location-rent"><?php echo esc_html($location[0]->name); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($cityArea) : ?>
                        <span class="kcpf-separator-rent">,</span>
                        <span class="kcpf-city-area-rent"><?php echo esc_html($cityArea); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($propertyType) : ?>
                        <span class="kcpf-separator-rent">|</span>
                        <span class="kcpf-property-type-rent"><?php echo esc_html($propertyType); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="kcpf-property-specs-rent">
                    <?php if ($isLand && $plotArea) : ?>
                        <span class="kcpf-plot-area">
                            <span class="kcpf-property-specs-rent-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.5 5.7V1.5H5.7V5.7H1.5ZM0 1C0 0.447715 0.447715 0 1 0H6.2C6.75228 0 7.2 0.447715 7.2 1V6.2C7.2 6.75228 6.75228 7.2 6.2 7.2H4.35V16.8H6.2C6.75228 16.8 7.2 17.2477 7.2 17.8V23C7.2 23.5523 6.75228 24 6.2 24H1C0.447715 24 0 23.5523 0 23V17.8C0 17.2477 0.447715 16.8 1 16.8H2.85V7.2H1C0.447715 7.2 0 6.75228 0 6.2V1ZM18.3 1.5H22.5V5.7H18.3V1.5ZM16.8 1C16.8 0.447715 17.2477 0 17.8 0H23C23.5523 0 24 0.447715 24 1V6.2C24 6.75228 23.5523 7.2 23 7.2H21.15V16.8H23C23.5523 16.8 24 17.2477 24 17.8V23C24 23.5523 23.5523 24 23 24H17.8C17.2477 24 16.8 23.5523 16.8 23V21.15H7.2V19.65H16.8V17.8C16.8 17.2477 17.2477 16.8 17.8 16.8H19.65V7.2H17.8C17.2477 7.2 16.8 6.75228 16.8 6.2V4.35L7.2 4.35V2.85L16.8 2.85V1ZM22.5 18.3H18.3V22.5H22.5V18.3ZM1.5 22.5V18.3H5.7V22.5H1.5Z"></path></svg>
                            </span>
                            <?php echo esc_html($plotArea); ?>
                        </span>
                    <?php elseif ($isMultiUnit) : ?>
                        <?php if ($bedroomsRange) : ?>
                            <span class="kcpf-bedrooms-rent">
                                <span class="kcpf-property-specs-rent-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M23.961 16.171C23.998 16.072 24.011 15.963 23.977 15.853L22 9.428V3.50005C22 2.12206 20.879 1.00006 19.5 1.00006H4.5C3.12098 1.00006 2.00002 2.12206 2.00002 3.50005V9.42804L0.0230156 15.853C-0.0109688 15.962 0.00201563 16.071 0.039 16.171C0.015 16.277 0 16.386 0 16.5V20.5V22.5C0 22.776 0.224016 23 0.500016 23H2.50003C2.77598 23 3 22.776 3 22.5V21H21V22.5C21 22.776 21.224 23 21.5 23H23.5C23.776 23 24 22.776 24 22.5V20.5V16.5C24 16.386 23.985 16.277 23.961 16.171ZM3 3.50005C3 2.67303 3.67298 2.00005 4.5 2.00005H19.5C20.327 2.00005 21 2.67303 21 3.50005V9.00003H19.641L19.175 7.13604C19.007 6.46704 18.408 6.00003 17.719 6.00003H14.5C13.673 6.00003 13 6.67301 13 7.50003V9.00003H11V7.50101C11 6.674 10.327 6.00101 9.49997 6.00101H6.28097C5.59195 6.00101 4.99298 6.46901 4.82498 7.13703L4.359 9.00003H3V3.50005ZM18.614 9.80801C18.518 9.93003 18.374 10 18.219 10H14.5C14.225 10 14 9.776 14 9.5V7.50003C14 7.22403 14.225 7.00001 14.5 7.00001H17.72C17.95 7.00001 18.149 7.15503 18.205 7.37801L18.705 9.37803C18.743 9.52901 18.71 9.68501 18.614 9.80801ZM10.001 7.50003V9.49503C10.001 9.49704 10 9.49803 10 9.50004C10 9.50103 10 9.50206 10 9.50206C9.99905 9.77708 9.77503 10.0001 9.50105 10.0001H5.78203C5.62603 10.0001 5.48302 9.93008 5.38702 9.80806C5.29102 9.68506 5.25802 9.52808 5.29603 9.37808L5.79605 7.37806C5.85206 7.15606 6.05105 7.00006 6.28205 7.00006H9.50105C9.77602 7.00001 10.001 7.22403 10.001 7.50003ZM2.86898 10H4.374C4.42702 10.15 4.49602 10.294 4.59698 10.424C4.88498 10.79 5.316 11 5.781 11H9.50002C10.151 11 10.701 10.58 10.908 10H13.092C13.299 10.581 13.849 11 14.5 11H18.219C18.684 11 19.114 10.79 19.402 10.424C19.503 10.295 19.572 10.15 19.625 10H21.131L22.675 15.018C22.617 15.011 22.56 15 22.5 15H1.5C1.44 15 1.383 15.011 1.326 15.018L2.86898 10ZM2.00002 22H0.999984V21H1.99997V22H2.00002ZM23 22H22V21H23V22ZM23 20H0.999984V16.5C0.999984 16.224 1.22498 16 1.5 16H22.5C22.775 16 23 16.224 23 16.5V20Z"></path></svg>
                                </span>
                                <?php echo esc_html($bedroomsRange); ?> Bed
                            </span>
                        <?php endif; ?>

                        <?php if ($bathroomsRange) : ?>
                            <span class="kcpf-bathrooms-rent">
                                <span class="kcpf-property-specs-rent-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M22.5 11H22V3.00002C22 1.897 21.103 1 20 1C18.8969 1 18 1.89695 18 2.99903L17.999 3.50003C17.9985 3.77641 18.2217 4.00052 18.498 4.00103C18.7744 4.00103 18.9985 3.77791 18.999 3.502L19 3.00006C19 2.44881 19.4487 2.00008 20 2.00008C20.5512 2.00008 21 2.44872 21 3.00002V11H1.5C0.672844 11 0 11.6729 0 12.5C0 13.151 0.41925 13.7008 0.999984 13.9079V15.5C0.999984 17.7951 2.19877 19.8115 3.99998 20.9685V23.5C3.99998 23.7764 4.22363 24 4.5 24H5.49998C5.68945 24 5.86228 23.8931 5.94727 23.7236L6.82683 21.9649C7.04822 21.9878 7.2727 22 7.5 22H16.5C16.7273 22 16.9518 21.9878 17.1732 21.9649L18.0527 23.7236C18.1377 23.8931 18.3105 24 18.5 24H19.5C19.7764 24 20 23.7764 20 23.5V20.9684C21.8012 19.8115 23 17.795 23 15.5V13.9079C23.5807 13.7008 24 13.151 24 12.5C24 11.6729 23.3272 11 22.5 11ZM6 12H11V16.9097L6 16.0767V12ZM0.999984 12.5C0.999984 12.2241 1.22414 12 1.5 12H5.00002V13H1.5C1.22414 13 0.999984 12.7759 0.999984 12.5ZM5.19094 23H5.00002V21.4985C5.26013 21.6073 5.53097 21.6938 5.80683 21.7685L5.19094 23ZM19 23H18.8091L18.1932 21.7685C18.469 21.6939 18.7399 21.6073 19 21.4985V23ZM22 15.5C22 18.5327 19.5327 21 16.5 21H7.5C4.46728 21 2.00002 18.5327 2.00002 15.5V14H5.00002V16.5C5.00002 16.7446 5.17678 16.9531 5.418 16.9932L11.418 17.9932C11.4453 17.9976 11.4727 18 11.5 18C11.6177 18 11.7324 17.9585 11.8233 17.8814C11.9356 17.7866 12 17.647 12 17.5V14H22L22 15.5ZM22.5 13H12V12H22.5C22.7759 12 23 12.2241 23 12.5C23 12.7759 22.7759 13 22.5 13Z"></path></svg>
                                </span>
                                <?php echo esc_html($bathroomsRange); ?> Bath
                            </span>
                        <?php endif; ?>

                        <?php if ($coveredAreaRange) : ?>
                            <span class="kcpf-area-rent">
                                <span class="kcpf-property-specs-rent-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.5 5.7V1.5H5.7V5.7H1.5ZM0 1C0 0.447715 0.447715 0 1 0H6.2C6.75228 0 7.2 0.447715 7.2 1V6.2C7.2 6.75228 6.75228 7.2 6.2 7.2H4.35V16.8H6.2C6.75228 16.8 7.2 17.2477 7.2 17.8V23C7.2 23.5523 6.75228 24 6.2 24H1C0.447715 24 0 23.5523 0 23V17.8C0 17.2477 0.447715 16.8 1 16.8H2.85V7.2H1C0.447715 7.2 0 6.75228 0 6.2V1ZM18.3 1.5H22.5V5.7H18.3V1.5ZM16.8 1C16.8 0.447715 17.2477 0 17.8 0H23C23.5523 0 24 0.447715 24 1V6.2C24 6.75228 23.5523 7.2 23 7.2H21.15V16.8H23C23.5523 16.8 24 17.2477 24 17.8V23C24 23.5523 23.5523 24 23 24H17.8C17.2477 24 16.8 23.5523 16.8 23V21.15H7.2V19.65H16.8V17.8C16.8 17.2477 17.2477 16.8 17.8 16.8H19.65V7.2H17.8C17.2477 7.2 16.8 6.75228 16.8 6.2V4.35L7.2 4.35V2.85L16.8 2.85V1ZM22.5 18.3H18.3V22.5H22.5V18.3ZM1.5 22.5V18.3H5.7V22.5H1.5Z"></path></svg>
                                </span>
                                <?php echo esc_html($coveredAreaRange); ?>
                            </span>
                        <?php endif; ?>
                    <?php else : ?>
                        <?php if ($bedrooms) : ?>
                            <span class="kcpf-bedrooms-rent">
                                <span class="kcpf-property-specs-rent-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M23.961 16.171C23.998 16.072 24.011 15.963 23.977 15.853L22 9.428V3.50005C22 2.12206 20.879 1.00006 19.5 1.00006H4.5C3.12098 1.00006 2.00002 2.12206 2.00002 3.50005V9.42804L0.0230156 15.853C-0.0109688 15.962 0.00201563 16.071 0.039 16.171C0.015 16.277 0 16.386 0 16.5V20.5V22.5C0 22.776 0.224016 23 0.500016 23H2.50003C2.77598 23 3 22.776 3 22.5V21H21V22.5C21 22.776 21.224 23 21.5 23H23.5C23.776 23 24 22.776 24 22.5V20.5V16.5C24 16.386 23.985 16.277 23.961 16.171ZM3 3.50005C3 2.67303 3.67298 2.00005 4.5 2.00005H19.5C20.327 2.00005 21 2.67303 21 3.50005V9.00003H19.641L19.175 7.13604C19.007 6.46704 18.408 6.00003 17.719 6.00003H14.5C13.673 6.00003 13 6.67301 13 7.50003V9.00003H11V7.50101C11 6.674 10.327 6.00101 9.49997 6.00101H6.28097C5.59195 6.00101 4.99298 6.46901 4.82498 7.13703L4.359 9.00003H3V3.50005ZM18.614 9.80801C18.518 9.93003 18.374 10 18.219 10H14.5C14.225 10 14 9.776 14 9.5V7.50003C14 7.22403 14.225 7.00001 14.5 7.00001H17.72C17.95 7.00001 18.149 7.15503 18.205 7.37801L18.705 9.37803C18.743 9.52901 18.71 9.68501 18.614 9.80801ZM10.001 7.50003V9.49503C10.001 9.49704 10 9.49803 10 9.50004C10 9.50103 10 9.50206 10 9.50206C9.99905 9.77708 9.77503 10.0001 9.50105 10.0001H5.78203C5.62603 10.0001 5.48302 9.93008 5.38702 9.80806C5.29102 9.68506 5.25802 9.52808 5.29603 9.37808L5.79605 7.37806C5.85206 7.15606 6.05105 7.00006 6.28205 7.00006H9.50105C9.77602 7.00001 10.001 7.22403 10.001 7.50003ZM2.86898 10H4.374C4.42702 10.15 4.49602 10.294 4.59698 10.424C4.88498 10.79 5.316 11 5.781 11H9.50002C10.151 11 10.701 10.58 10.908 10H13.092C13.299 10.581 13.849 11 14.5 11H18.219C18.684 11 19.114 10.79 19.402 10.424C19.503 10.295 19.572 10.15 19.625 10H21.131L22.675 15.018C22.617 15.011 22.56 15 22.5 15H1.5C1.44 15 1.383 15.011 1.326 15.018L2.86898 10ZM2.00002 22H0.999984V21H1.99997V22H2.00002ZM23 22H22V21H23V22ZM23 20H0.999984V16.5C0.999984 16.224 1.22498 16 1.5 16H22.5C22.775 16 23 16.224 23 16.5V20Z"></path></svg>
                                </span>
                                <?php echo esc_html($bedrooms); ?> Bed
                            </span>
                        <?php endif; ?>

                        <?php if ($bathrooms) : ?>
                            <span class="kcpf-bathrooms-rent">
                                <span class="kcpf-property-specs-rent-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M22.5 11H22V3.00002C22 1.897 21.103 1 20 1C18.8969 1 18 1.89695 18 2.99903L17.999 3.50003C17.9985 3.77641 18.2217 4.00052 18.498 4.00103C18.7744 4.00103 18.9985 3.77791 18.999 3.502L19 3.00006C19 2.44881 19.4487 2.00008 20 2.00008C20.5512 2.00008 21 2.44872 21 3.00002V11H1.5C0.672844 11 0 11.6729 0 12.5C0 13.151 0.41925 13.7008 0.999984 13.9079V15.5C0.999984 17.7951 2.19877 19.8115 3.99998 20.9685V23.5C3.99998 23.7764 4.22363 24 4.5 24H5.49998C5.68945 24 5.86228 23.8931 5.94727 23.7236L6.82683 21.9649C7.04822 21.9878 7.2727 22 7.5 22H16.5C16.7273 22 16.9518 21.9878 17.1732 21.9649L18.0527 23.7236C18.1377 23.8931 18.3105 24 18.5 24H19.5C19.7764 24 20 23.7764 20 23.5V20.9684C21.8012 19.8115 23 17.795 23 15.5V13.9079C23.5807 13.7008 24 13.151 24 12.5C24 11.6729 23.3272 11 22.5 11ZM6 12H11V16.9097L6 16.0767V12ZM0.999984 12.5C0.999984 12.2241 1.22414 12 1.5 12H5.00002V13H1.5C1.22414 13 0.999984 12.7759 0.999984 12.5ZM5.19094 23H5.00002V21.4985C5.26013 21.6073 5.53097 21.6938 5.80683 21.7685L5.19094 23ZM19 23H18.8091L18.1932 21.7685C18.469 21.6939 18.7399 21.6073 19 21.4985V23ZM22 15.5C22 18.5327 19.5327 21 16.5 21H7.5C4.46728 21 2.00002 18.5327 2.00002 15.5V14H5.00002V16.5C5.00002 16.7446 5.17678 16.9531 5.418 16.9932L11.418 17.9932C11.4453 17.9976 11.4727 18 11.5 18C11.6177 18 11.7324 17.9585 11.8233 17.8814C11.9356 17.7866 12 17.647 12 17.5V14H22L22 15.5ZM22.5 13H12V12H22.5C22.7759 12 23 12.2241 23 12.5C23 12.7759 22.7759 13 22.5 13Z"></path></svg>
                                </span>
                                <?php echo esc_html($bathrooms); ?> Bath
                            </span>
                        <?php endif; ?>

                        <?php if ($rentArea) : ?>
                            <span class="kcpf-area-rent">
                                <span class="kcpf-property-specs-rent-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.5 5.7V1.5H5.7V5.7H1.5ZM0 1C0 0.447715 0.447715 0 1 0H6.2C6.75228 0 7.2 0.447715 7.2 1V6.2C7.2 6.75228 6.75228 7.2 6.2 7.2H4.35V16.8H6.2C6.75228 16.8 7.2 17.2477 7.2 17.8V23C7.2 23.5523 6.75228 24 6.2 24H1C0.447715 24 0 23.5523 0 23/V17.8C0 17.2477 0.447715 16.8 1 16.8H2.85V7.2H1C0.447715 7.2 0 6.75228 0 6.2V1ZM18.3 1.5H22.5V5.7H18.3V1.5ZM16.8 1C16.8 0.447715 17.2477 0 17.8 0H23C23.5523 0 24 0.447715 24 1V6.2C24 6.75228 23.5523 7.2 23 7.2H21.15V16.8H23C23.5523 16.8 24 17.2477 24 17.8/V23C24 23.5523 23.5523 24 23 24H17.8C17.2477 24 16.8 23.5523 16.8 23V21.15H7.2V19.65H16.8V17.8C16.8 17.2477 17.2477 16.8 17.8 16.8H19.65V7.2H17.8C17.2477 7.2 16.8 6.75228 16.8 6.2V4.35L7.2 4.35V2.85L16.8 2.85V1ZM22.5 18.3H18.3V22.5H22.5V18.3ZM1.5 22.5V18.3H5.7V22.5H1.5Z"></path></svg>
                                </span>
                                <?php echo esc_html($rentArea); ?> m²
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <?php if ($price) : ?>
                    <div class="kcpf-property-price-rent">
                        €<?php echo esc_html($price); ?>/mo
                    </div>
                <?php endif; ?>
            </div>
            </a>
        </article>
        <?php
    }
}

