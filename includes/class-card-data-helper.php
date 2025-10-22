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
        if (empty($value) && $value !== '0' && $value !== 0) {
            return '';
        }
        
        // Handle array values (with "Save as array" enabled)
        if (is_array($value)) {
            // Get all keys with true values
            $selectedValues = [];
            foreach ($value as $key => $val) {
                // Check if value is boolean true
                if ($val === true || $val === 'true' || $val === 1) {
                    $selectedValues[] = $key;
                }
            }
            
            // If no true values, return empty
            if (empty($selectedValues)) {
                return '';
            }
            
            // Convert "9_plus" to "9+" for display
            $formatted = array_map(function($val) {
                return str_replace('_plus', '+', $val);
            }, $selectedValues);
            
            // Return first value only
            return reset($formatted);
        }
        
        // Handle serialized strings (when "Save as array" is NOT enabled)
        if (is_string($value) && is_serialized($value)) {
            $value = maybe_unserialize($value);
            if (is_array($value)) {
                $value = !empty($value) ? reset($value) : '';
            }
        }
        
        // If value is still empty after handling
        if (empty($value) && $value !== '0' && $value !== 0) {
            return '';
        }
        
        // Convert to string for consistency
        $value = (string) $value;
        
        // Convert "9_plus" to "9+" for display
        if (preg_match('/^\d+(_plus)?$/', $value)) {
            return str_replace('_plus', '+', $value);
        }
        
        // Return the value as-is
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
        
        // Debug: check what we're getting
        $result = $multiUnit === '1' || $multiUnit === 1 || $multiUnit === true;
        
        return $result;
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

