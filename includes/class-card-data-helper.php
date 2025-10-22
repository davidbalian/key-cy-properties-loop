<?php
/**
 * Card Data Helper Class
 * 
 * Retrieves and formats property data for card display
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Card_Data_Helper
{
    /**
     * Get formatted bedrooms value for display
     * 
     * @param int $property_id Property ID
     * @param string $purpose Property purpose (sale/rent)
     * @return string Formatted bedrooms value
     */
    public static function getBedrooms($property_id, $purpose = 'sale')
    {
        $bedroomsKey = KCPF_Field_Config::getMetaKey('bedrooms', $purpose);
        $value = get_post_meta($property_id, $bedroomsKey, true);
        
        // Debug: log what we're getting
        error_log("KCPF Bedrooms Debug - Property ID: $property_id, Key: $bedroomsKey, Value: " . print_r($value, true));
        
        // Just return the value as-is with simple formatting
        return self::formatSimpleValue($value);
    }
    
    /**
     * Get formatted bathrooms value for display
     * 
     * @param int $property_id Property ID
     * @param string $purpose Property purpose (sale/rent)
     * @return string Formatted bathrooms value
     */
    public static function getBathrooms($property_id, $purpose = 'sale')
    {
        $bathroomsKey = KCPF_Field_Config::getMetaKey('bathrooms', $purpose);
        $value = get_post_meta($property_id, $bathroomsKey, true);
        
        // Debug: log what we're getting
        error_log("KCPF Bathrooms Debug - Property ID: $property_id, Key: $bathroomsKey, Value: " . print_r($value, true));
        
        // Just return the value as-is with simple formatting
        return self::formatSimpleValue($value);
    }
    
    /**
     * Format value for display - simple version
     * 
     * @param mixed $value Raw value from post meta
     * @return string Formatted value
     */
    private static function formatSimpleValue($value)
    {
        // Handle empty values
        if (empty($value)) {
            return '';
        }
        
        // Handle array values (JetEngine might store as array)
        if (is_array($value)) {
            $value = !empty($value) ? reset($value) : '';
        }
        
        // If value is still empty after array handling
        if (empty($value)) {
            return '';
        }
        
        // Convert "9_plus" to "9+" for display
        if (preg_match('/^\d+(_plus)?$/', $value)) {
            return str_replace('_plus', '+', $value);
        }
        
        // If value is "true" or "1" (boolean stored as string), ignore it
        if (in_array(strtolower($value), ['true', 'false', '1', '0'], true)) {
            return '';
        }
        
        // Return the value as-is (could be numeric string like "3" or label like "3 Bedrooms")
        return $value;
    }
    
    /**
     * Get formatted price for display
     * 
     * @param int $property_id Property ID
     * @param string $purpose Property purpose (sale/rent)
     * @return string|null Formatted price or null
     */
    public static function getPrice($property_id, $purpose = 'sale')
    {
        $priceKey = KCPF_Field_Config::getMetaKey('price', $purpose);
        $price = get_post_meta($property_id, $priceKey, true);
        
        if (empty($price) || !is_numeric($price)) {
            return null;
        }
        
        return number_format($price);
    }
    
    /**
     * Check if property is multi-unit
     * 
     * @param int $property_id Property ID
     * @return bool
     */
    public static function isMultiUnit($property_id)
    {
        $multiUnit = get_post_meta($property_id, 'multi-unit', true);
        return $multiUnit === '1' || $multiUnit === 1 || $multiUnit === true;
    }
    
    /**
     * Get multi-unit price range for display
     * 
     * @param int $property_id Property ID
     * @return string|null Formatted price range or null
     */
    public static function getMultiUnitPriceRange($property_id)
    {
        if (!self::isMultiUnit($property_id)) {
            return null;
        }
        
        $minPrice = get_post_meta($property_id, 'minimum_buy_price', true);
        $maxPrice = get_post_meta($property_id, 'maximum_buy_price', true);
        
        if (empty($minPrice) && empty($maxPrice)) {
            return null;
        }
        
        if (!empty($minPrice) && !empty($maxPrice)) {
            return '€' . number_format($minPrice) . ' - €' . number_format($maxPrice);
        } elseif (!empty($minPrice)) {
            return 'From €' . number_format($minPrice);
        } else {
            return 'Up to €' . number_format($maxPrice);
        }
    }
}

