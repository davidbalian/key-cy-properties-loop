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
        $repeaterValue = get_post_meta($property_id, 'multi-unit_table', true);
        
        // If it's a serialized array, check if it has items
        if (is_serialized($repeaterValue)) {
            $repeaterValue = maybe_unserialize($repeaterValue);
        }
        
        // If it's an array with items, it's multi-unit
        if (is_array($repeaterValue) && !empty($repeaterValue)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get multi-unit repeater count
     * 
     * @param int $property_id Property ID
     * @return int|null Number of units or null
     */
    public static function getMultiUnitCount($property_id)
    {
        if (!self::isMultiUnit($property_id)) {
            return null;
        }
        
        $repeaterValue = get_post_meta($property_id, 'multi-unit_table', true);
        
        // If it's a serialized array, unserialize it
        if (is_serialized($repeaterValue)) {
            $repeaterValue = maybe_unserialize($repeaterValue);
        }
        
        if (is_array($repeaterValue)) {
            return count($repeaterValue);
        }
        
        return null;
    }
    
    /**
     * Get multi-unit price for display
     * 
     * @param int $property_id Property ID
     * @param string $purpose Property purpose (sale/rent)
     * @return string|null Formatted price or null
     */
    public static function getMultiUnitPrice($property_id, $purpose = 'sale')
    {
        if (!self::isMultiUnit($property_id)) {
            return null;
        }
        
        $minPrice = get_post_meta($property_id, 'minimum_buy_price', true);
        
        if (empty($minPrice) || !is_numeric($minPrice)) {
            return null;
        }
        
        return 'From €' . number_format($minPrice);
    }
    
    /**
     * Get multi-unit table data
     * 
     * @param int $property_id Property ID
     * @return array|null Array of unit data or null
     */
    public static function getMultiUnitTable($property_id)
    {
        if (!self::isMultiUnit($property_id)) {
            return null;
        }
        
        $repeaterValue = get_post_meta($property_id, 'multi-unit_table', true);
        
        // If it's a serialized array, unserialize it
        if (is_serialized($repeaterValue)) {
            $repeaterValue = maybe_unserialize($repeaterValue);
        }
        
        if (is_array($repeaterValue)) {
            return $repeaterValue;
        }
        
        return null;
    }
    
    /**
     * Get city_area meta field
     * 
     * @param int $property_id Property ID
     * @return string|null City area or null
     */
    public static function getCityArea($property_id)
    {
        $cityArea = get_post_meta($property_id, 'city_area', true);
        
        if (empty($cityArea)) {
            return null;
        }
        
        return $cityArea;
    }
    
    /**
     * Get property type taxonomy
     * 
     * @param int $property_id Property ID
     * @return string|null Property type or null
     */
    public static function getPropertyType($property_id)
    {
        $propertyType = get_the_terms($property_id, 'property-type');
        
        if ($propertyType && !is_wp_error($propertyType) && !empty($propertyType)) {
            return $propertyType[0]->name;
        }
        
        return null;
    }
    
    /**
     * Get total covered area
     * 
     * @param int $property_id Property ID
     * @param string $purpose Property purpose (sale/rent)
     * @return string|null Total covered area or null
     */
    public static function getTotalCoveredArea($property_id, $purpose = 'sale')
    {
        $coveredAreaKey = KCPF_Field_Config::getMetaKey('covered_area', $purpose);
        $value = get_post_meta($property_id, $coveredAreaKey, true);
        
        if (empty($value) || !is_numeric($value)) {
            return null;
        }
        
        return number_format($value);
    }
    
    /**
     * Get property display coordinates
     * 
     * @param int $property_id Property ID
     * @return string Coordinates string
     */
    public static function getCoordinates($property_id)
    {
        return get_post_meta($property_id, 'display_coordinates', true);
    }
}

