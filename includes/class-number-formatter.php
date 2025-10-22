<?php
/**
 * Number Formatter Class
 * 
 * Provides consistent number formatting for prices, areas, and other numeric values
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Number_Formatter
{
    /**
     * Format a number with thousands separator
     * 
     * @param mixed $value The value to format
     * @param int $decimals Number of decimal places (default: 0)
     * @return string Formatted number
     */
    public static function format($value, $decimals = 0)
    {
        // Handle empty or non-numeric values
        if (empty($value) && $value !== '0' && $value !== 0) {
            return '';
        }
        
        // Convert to float for formatting
        $number = floatval($value);
        
        // Format with thousands separator (comma) and decimal point
        return number_format($number, $decimals, '.', ',');
    }
    
    /**
     * Format price with currency symbol
     * 
     * @param mixed $value The price value
     * @param string $currency Currency symbol (default: '€')
     * @return string Formatted price
     */
    public static function formatPrice($value, $currency = '€')
    {
        $formatted = self::format($value, 0);
        
        if (empty($formatted)) {
            return '';
        }
        
        return $currency . $formatted;
    }
    
    /**
     * Format area with unit
     * 
     * @param mixed $value The area value
     * @param string $unit Unit symbol (default: 'm²')
     * @return string Formatted area
     */
    public static function formatArea($value, $unit = 'm²')
    {
        $formatted = self::format($value, 0);
        
        if (empty($formatted)) {
            return '';
        }
        
        return $formatted . ' ' . $unit;
    }
    
    /**
     * Format a number for multi-unit table display
     * 
     * @param mixed $value The value to format
     * @return string Formatted number
     */
    public static function formatMultiUnitValue($value)
    {
        // Handle empty values
        if (empty($value) && $value !== '0' && $value !== 0) {
            return '';
        }
        
        // If it's numeric, format it
        if (is_numeric($value)) {
            return self::format($value, 0);
        }
        
        // Otherwise return as-is
        return $value;
    }
    
    /**
     * Format multi-unit table price
     * 
     * @param mixed $value The price value
     * @return string Formatted price
     */
    public static function formatMultiUnitPrice($value)
    {
        $formatted = self::formatMultiUnitValue($value);
        
        if (empty($formatted)) {
            return '';
        }
        
        return '€' . $formatted;
    }
    
    /**
     * Format multi-unit table area
     * 
     * @param mixed $value The area value
     * @return string Formatted area
     */
    public static function formatMultiUnitArea($value)
    {
        $formatted = self::formatMultiUnitValue($value);
        
        if (empty($formatted)) {
            return '';
        }
        
        return $formatted . ' m²';
    }
}

