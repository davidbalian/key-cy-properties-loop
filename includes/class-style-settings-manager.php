<?php
/**
 * Style Settings Manager Class
 * 
 * Manages storage and retrieval of style settings
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Style_Settings_Manager
{
    /**
     * Option name for storing style settings
     */
    const OPTION_NAME = 'kcpf_style_settings';
    
    /**
     * Get all style settings
     * 
     * @return array
     */
    public static function getSettings()
    {
        return get_option(self::OPTION_NAME, self::getDefaultSettings());
    }
    
    /**
     * Save style settings
     * 
     * @param array $settings
     * @return bool
     */
    public static function saveSettings($settings)
    {
        return update_option(self::OPTION_NAME, $settings);
    }
    
    /**
     * Get default style settings
     * 
     * @return array
     */
    public static function getDefaultSettings()
    {
        return [
            'filter_container' => [
                'margin_bottom' => '1.5rem',
                'padding' => '0',
            ],
            'filter_label' => [
                'font_size' => '1rem',
                'font_weight' => '600',
                'color' => '#333',
                'margin_bottom' => '0.5rem',
            ],
            'select' => [
                'width' => '100%',
                'padding' => '0.75rem 1rem',
                'border' => '1px solid #ddd',
                'border_radius' => '4px',
                'font_size' => '1rem',
                'background_color' => '#fff',
                'border_color_focus' => '#0073aa',
            ],
            'multiselect_trigger' => [
                'padding' => '0.75rem 1rem',
                'border' => '1px solid #ddd',
                'border_radius' => '4px',
                'background_color' => '#fff',
                'border_color_hover' => '#0073aa',
                'border_color_active' => '#0073aa',
            ],
            'multiselect_chip' => [
                'padding' => '0.375rem 0.75rem',
                'background_color' => '#0073aa',
                'color' => '#fff',
                'border_radius' => '20px',
                'font_size' => '0.875rem',
            ],
            'multiselect_dropdown' => [
                'border' => '1px solid #ddd',
                'border_radius' => '0 0 4px 4px',
                'background_color' => '#fff',
                'max_height' => '300px',
                'box_shadow' => '0 4px 12px rgba(0, 0, 0, 0.1)',
            ],
            'multiselect_option' => [
                'padding' => '0.75rem 1rem',
                'background_color_hover' => '#f8f8f8',
            ],
            'input' => [
                'padding' => '0.75rem 1rem',
                'border' => '1px solid #ddd',
                'border_radius' => '4px',
                'font_size' => '1rem',
                'border_color_focus' => '#0073aa',
            ],
            'button' => [
                'padding' => '0.75rem 2rem',
                'border' => 'none',
                'border_radius' => '4px',
                'font_size' => '1rem',
                'font_weight' => '600',
                'cursor' => 'pointer',
            ],
            'apply_button' => [
                'background_color' => '#0073aa',
                'color' => '#fff',
                'background_color_hover' => '#005a87',
            ],
            'reset_button' => [
                'background_color' => '#f0f0f0',
                'color' => '#333',
                'background_color_hover' => '#e0e0e0',
            ],
            'toggle_button' => [
                'padding' => '0.5rem 1rem',
                'border' => '1px solid #ddd',
                'border_radius' => '4px',
                'background_color' => '#fff',
                'background_color_active' => '#0073aa',
                'color_active' => '#fff',
                'border_color_active' => '#0073aa',
            ],
            'range_slider' => [
                'height' => '8px',
                'connect_color' => '#0073aa',
                'handle_border' => '2px solid #0073aa',
                'handle_background' => '#fff',
                'handle_box_shadow' => '0 2px 4px rgba(0, 0, 0, 0.15)',
            ],
        ];
    }
    
    /**
     * Reset settings to defaults
     * 
     * @return bool
     */
    public static function resetToDefaults()
    {
        return update_option(self::OPTION_NAME, self::getDefaultSettings());
    }
    
    /**
     * Get a specific setting group
     * 
     * @param string $group
     * @return array
     */
    public static function getSettingGroup($group)
    {
        $settings = self::getSettings();
        return $settings[$group] ?? [];
    }
}

