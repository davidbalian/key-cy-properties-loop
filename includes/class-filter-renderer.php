<?php
/**
 * Filter Renderer Class (Facade)
 * 
 * Provides backward compatibility by delegating to specific filter renderer classes.
 * This class acts as a facade, maintaining the same public API while distributing
 * responsibilities to focused, single-purpose renderer classes.
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
        return KCPF_Location_Filter_Renderer::renderLocation($attrs);
    }
    
    /**
     * Render purpose filter (Sale/Rent)
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderPurpose($attrs)
    {
        return KCPF_Purpose_Filter_Renderer::renderPurpose($attrs);
    }
    
    /**
     * Render price range filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderPrice($attrs)
    {
        return KCPF_Price_Filter_Renderer::renderPrice($attrs);
    }
    
    /**
     * Render bedrooms filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderBedrooms($attrs)
    {
        return KCPF_Bedrooms_Filter_Renderer::renderBedrooms($attrs);
    }
    
    /**
     * Render bathrooms filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderBathrooms($attrs)
    {
        return KCPF_Bathrooms_Filter_Renderer::renderBathrooms($attrs);
    }
    
    /**
     * Render property type filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderType($attrs)
    {
        return KCPF_Type_Filter_Renderer::renderType($attrs);
    }
    
    /**
     * Render apply/submit button
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderApplyButton($attrs)
    {
        return KCPF_Misc_Filter_Renderer::renderApplyButton($attrs);
    }
    
    /**
     * Render reset/clear button
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderResetButton($attrs)
    {
        return KCPF_Misc_Filter_Renderer::renderResetButton($attrs);
    }
    
    /**
     * Render amenities filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderAmenities($attrs)
    {
        return KCPF_Amenities_Filter_Renderer::renderAmenities($attrs);
    }
    
    /**
     * Render covered area filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderCoveredArea($attrs)
    {
        return KCPF_Area_Filter_Renderer::renderCoveredArea($attrs);
    }
    
    /**
     * Render plot area filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderPlotArea($attrs)
    {
        return KCPF_Area_Filter_Renderer::renderPlotArea($attrs);
    }
    
    /**
     * Render property ID search filter
     * 
     * @param array $attrs Shortcode attributes
     * @return string HTML output
     */
    public static function renderPropertyId($attrs)
    {
        return KCPF_Misc_Filter_Renderer::renderPropertyId($attrs);
    }
}
