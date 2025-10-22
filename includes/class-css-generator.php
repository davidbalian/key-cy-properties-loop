<?php
/**
 * CSS Generator Class
 * 
 * Generates dynamic CSS from style settings
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_CSS_Generator
{
    /**
     * Generate CSS from settings
     * 
     * @return string
     */
    public static function generate()
    {
        // Safety check
        if (!class_exists('KCPF_Style_Settings_Manager')) {
            return '';
        }
        
        try {
            $settings = KCPF_Style_Settings_Manager::getSettings();
        } catch (Exception $e) {
            error_log('KCPF CSS Generator Error: ' . $e->getMessage());
            return '';
        }
        
        $css = '';
        
        // Filter Container
        if (isset($settings['filter_container'])) {
            $css .= self::generateSectionCSS('.kcpf-filter', $settings['filter_container']);
        }
        
        // Filter Label
        if (isset($settings['filter_label'])) {
            $css .= self::generateSectionCSS('.kcpf-filter label', $settings['filter_label']);
        }
        
        // Select Filters
        if (isset($settings['select'])) {
            $css .= self::generateSectionCSS('.kcpf-filter-select', $settings['select']);
            $css .= self::generateFocusCSS('.kcpf-filter-select', $settings['select']);
        }
        
        // Multi-select Trigger
        if (isset($settings['multiselect_trigger'])) {
            $css .= self::generateSectionCSS('.kcpf-multiselect-trigger', $settings['multiselect_trigger']);
            $css .= self::generateHoverCSS('.kcpf-multiselect-trigger:hover', $settings['multiselect_trigger']);
            $css .= self::generateActiveCSS('.kcpf-multiselect-trigger.active', $settings['multiselect_trigger']);
        }
        
        // Multi-select Chip
        if (isset($settings['multiselect_chip'])) {
            $css .= self::generateSectionCSS('.kcpf-chip', $settings['multiselect_chip']);
        }
        
        // Multi-select Dropdown Menu
        if (isset($settings['multiselect_dropdown'])) {
            $css .= self::generateSectionCSS('.kcpf-multiselect-dropdown-menu', $settings['multiselect_dropdown']);
        }
        
        // Multi-select Option
        if (isset($settings['multiselect_option'])) {
            $css .= self::generateSectionCSS('.kcpf-multiselect-option', $settings['multiselect_option']);
            $css .= self::generateHoverCSS('.kcpf-multiselect-option:hover', $settings['multiselect_option']);
        }
        
        // Input Fields
        if (isset($settings['input'])) {
            $css .= self::generateSectionCSS('.kcpf-input', $settings['input']);
            $css .= self::generateFocusCSS('.kcpf-input:focus', $settings['input']);
        }
        
        // Apply Button
        if (isset($settings['button']) && isset($settings['apply_button'])) {
            $applyButtonSettings = array_merge($settings['button'], $settings['apply_button']);
            $css .= self::generateSectionCSS('.kcpf-apply-button', $applyButtonSettings);
            $css .= self::generateHoverCSS('.kcpf-apply-button:hover', $settings['apply_button']);
        }
        
        // Reset Button
        if (isset($settings['button']) && isset($settings['reset_button'])) {
            $resetButtonSettings = array_merge($settings['button'], $settings['reset_button']);
            $css .= self::generateSectionCSS('.kcpf-reset-button', $resetButtonSettings);
            $css .= self::generateHoverCSS('.kcpf-reset-button:hover', $settings['reset_button']);
        }
        
        // Toggle Buttons
        if (isset($settings['toggle_button'])) {
            $css .= self::generateSectionCSS('.kcpf-toggle-label span, .kcpf-radio-label span, .kcpf-button-label span', $settings['toggle_button']);
            $css .= self::generateActiveCSS('.kcpf-toggle-label.active span, .kcpf-radio-label input:checked + span, .kcpf-button-label.active span, .kcpf-button-label input:checked + span', $settings['toggle_button']);
        }
        
        // Range Slider
        if (isset($settings['range_slider'])) {
            $css .= self::generateSliderCSS($settings['range_slider']);
        }
        
        return $css;
    }
    
    /**
     * Generate CSS for a section
     * 
     * @param string $selector
     * @param array $settings
     * @return string
     */
    private static function generateSectionCSS($selector, $settings)
    {
        $css = $selector . ' {';
        
        foreach ($settings as $key => $value) {
            if (empty($value)) {
                continue;
            }
            
            $property = self::convertKeyToProperty($key);
            if ($property) {
                $css .= "\n    " . $property . ': ' . $value . ' !important;';
            }
        }
        
        $css .= "\n}\n\n";
        return $css;
    }
    
    /**
     * Generate hover CSS
     * 
     * @param string $selector
     * @param array $settings
     * @return string
     */
    private static function generateHoverCSS($selector, $settings)
    {
        $css = '';
        
        if (isset($settings['background_color_hover'])) {
            $css .= $selector . ' { background-color: ' . $settings['background_color_hover'] . " !important; }\n";
        }
        
        if (isset($settings['border_color_hover'])) {
            $css .= $selector . ' { border-color: ' . $settings['border_color_hover'] . " !important; }\n";
        }
        
        return $css ? $css . "\n" : '';
    }
    
    /**
     * Generate active CSS
     * 
     * @param string $selector
     * @param array $settings
     * @return string
     */
    private static function generateActiveCSS($selector, $settings)
    {
        $css = '';
        
        if (isset($settings['background_color_active'])) {
            $css .= $selector . ' { background-color: ' . $settings['background_color_active'] . " !important; }\n";
        }
        
        if (isset($settings['color_active'])) {
            $css .= $selector . ' { color: ' . $settings['color_active'] . " !important; }\n";
        }
        
        if (isset($settings['border_color_active'])) {
            $css .= $selector . ' { border-color: ' . $settings['border_color_active'] . " !important; }\n";
        }
        
        return $css ? $css . "\n" : '';
    }
    
    /**
     * Generate focus CSS
     * 
     * @param string $selector
     * @param array $settings
     * @return string
     */
    private static function generateFocusCSS($selector, $settings)
    {
        $css = '';
        
        if (isset($settings['border_color_focus'])) {
            $css .= $selector . ' { border-color: ' . $settings['border_color_focus'] . " !important; }\n";
        }
        
        return $css ? $css . "\n" : '';
    }
    
    /**
     * Generate slider CSS
     * 
     * @param array $settings
     * @return string
     */
    private static function generateSliderCSS($settings)
    {
        $css = '';
        
        if (isset($settings['height'])) {
            $css .= '.kcpf-range-slider { height: ' . $settings['height'] . " !important; }\n";
        }
        
        if (isset($settings['connect_color'])) {
            $css .= '.kcpf-range-slider .noUi-connect { background: ' . $settings['connect_color'] . " !important; }\n";
        }
        
        // Handle properties
        $handleProps = [];
        if (isset($settings['handle_border'])) {
            $handleProps[] = 'border: ' . $settings['handle_border'] . ' !important';
        }
        if (isset($settings['handle_background'])) {
            $handleProps[] = 'background: ' . $settings['handle_background'] . ' !important';
        }
        if (isset($settings['handle_box_shadow'])) {
            $handleProps[] = 'box-shadow: ' . $settings['handle_box_shadow'] . ' !important';
        }
        if (isset($settings['handle_border_radius'])) {
            $handleProps[] = 'border-radius: ' . $settings['handle_border_radius'] . ' !important';
        }
        if (isset($settings['handle_width'])) {
            $handleProps[] = 'width: ' . $settings['handle_width'] . ' !important';
        }
        if (isset($settings['handle_height'])) {
            $handleProps[] = 'height: ' . $settings['handle_height'] . ' !important';
        }
        
        if (!empty($handleProps)) {
            $css .= '.kcpf-range-slider .noUi-handle { ' . implode('; ', $handleProps) . "; }\n";
        }
        
        // Handle hover effects
        if (isset($settings['handle_hover_scale']) || isset($settings['handle_hover_border_width']) || isset($settings['handle_hover_box_shadow'])) {
            $hoverProps = [];
            if (isset($settings['handle_hover_scale'])) {
                $hoverProps[] = 'transform: scale(' . $settings['handle_hover_scale'] . ') !important';
            }
            if (isset($settings['handle_hover_border_width'])) {
                $hoverProps[] = 'border-width: ' . $settings['handle_hover_border_width'] . ' !important';
            }
            if (isset($settings['handle_hover_box_shadow'])) {
                $hoverProps[] = 'box-shadow: ' . $settings['handle_hover_box_shadow'] . ' !important';
            }
            
            if (!empty($hoverProps)) {
                $css .= '.kcpf-range-slider .noUi-handle:hover { ' . implode('; ', $hoverProps) . "; }\n";
            }
        }
        
        return $css . "\n";
    }
    
    /**
     * Convert setting key to CSS property
     * 
     * @param string $key
     * @return string|null
     */
    private static function convertKeyToProperty($key)
    {
        $mapping = [
            'margin_bottom' => 'margin-bottom',
            'padding' => 'padding',
            'font_size' => 'font-size',
            'font_weight' => 'font-weight',
            'color' => 'color',
            'width' => 'width',
            'border' => 'border',
            'border_radius' => 'border-radius',
            'background_color' => 'background-color',
            'border_color' => 'border-color',
            'max_height' => 'max-height',
            'box_shadow' => 'box-shadow',
            'cursor' => 'cursor',
            'height' => 'height',
        ];
        
        return $mapping[$key] ?? null;
    }
}

