<?php
/**
 * Field Configuration Class
 * 
 * Manages meta field mappings for sale/rent purposes
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Field_Config
{
    /**
     * Meta field mappings for sale properties
     * 
     * @var array
     */
    private static $saleFields = [
        'price' => 'price',
        'bedrooms' => 'bedrooms',
        'bathrooms' => 'bathrooms',
        'covered_area' => 'total_covered_area',
    ];
    
    /**
     * Meta field mappings for rent properties
     * 
     * @var array
     */
    private static $rentFields = [
        'price' => 'rent_price',
        'bedrooms' => 'rent_bedrooms',
        'bathrooms' => 'rent_bathrooms',
        'covered_area' => 'rent_area',
    ];
    
    /**
     * Glossary names for JetEngine
     * 
     * @var array
     */
    private static $glossaries = [
        'amenities' => 'Amenities',
        'bedrooms' => 'Bedrooms',
        'bathrooms' => 'Bathrooms',
    ];
    
    /**
     * Get the correct meta key based on purpose
     * 
     * @param string $baseField Base field name (price, bedrooms, bathrooms, covered_area)
     * @param string $purpose Property purpose (sale or rent)
     * @return string Meta key
     */
    public static function getMetaKey($baseField, $purpose = 'sale')
    {
        $purpose = strtolower($purpose);
        
        if ($purpose === 'rent' && isset(self::$rentFields[$baseField])) {
            return self::$rentFields[$baseField];
        }
        
        return self::$saleFields[$baseField] ?? $baseField;
    }
    
    /**
     * Get glossary name
     * 
     * @param string $field Field name
     * @return string Glossary name
     */
    public static function getGlossaryName($field)
    {
        return self::$glossaries[$field] ?? '';
    }
    
    /**
     * Get all supported base fields
     * 
     * @return array
     */
    public static function getSupportedFields()
    {
        return array_keys(self::$saleFields);
    }
    
    /**
     * Check if field is purpose-dependent
     * 
     * @param string $baseField
     * @return bool
     */
    public static function isPurposeDependent($baseField)
    {
        return isset(self::$rentFields[$baseField]);
    }
}

