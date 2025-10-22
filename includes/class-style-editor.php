<?php
/**
 * Style Editor Class
 * 
 * Dashboard page for customizing filter styles
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Style_Editor
{
    /**
     * Initialize style editor
     */
    public static function init()
    {
        // Only for administrators
        if (!current_user_can('manage_options')) {
            return;
        }
        
        add_action('admin_menu', [__CLASS__, 'addAdminMenu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueueAssets']);
    }
    
    /**
     * Add admin menu page
     */
    public static function addAdminMenu()
    {
        add_menu_page(
            'Filter Style Editor',
            'Filter Styles',
            'manage_options',
            'kcpf-style-editor',
            [__CLASS__, 'renderPage'],
            'dashicons-admin-appearance',
            30
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public static function enqueueAssets($hook)
    {
        if ($hook !== 'toplevel_page_kcpf-style-editor') {
            return;
        }
        
        // Enqueue main filter CSS for preview
        wp_enqueue_style(
            'kcpf-filters',
            KCPF_ASSETS_URL . 'css/filters.css',
            [],
            KCPF_VERSION
        );
        
        // Enqueue nouislider for preview
        wp_enqueue_style(
            'nouislider',
            KCPF_ASSETS_URL . 'libs/nouislider.min.css',
            [],
            '15.7.1'
        );
        
        wp_enqueue_script(
            'nouislider',
            KCPF_ASSETS_URL . 'libs/nouislider.min.js',
            [],
            '15.7.1',
            true
        );
        
        // Enqueue dynamic CSS
        wp_add_inline_style('kcpf-filters', KCPF_CSS_Generator::generate());
        
        // Admin CSS
        wp_enqueue_style(
            'kcpf-style-editor',
            KCPF_ASSETS_URL . 'css/style-editor.css',
            [],
            KCPF_VERSION
        );
        
        // Admin JS
        wp_enqueue_script(
            'kcpf-style-editor',
            KCPF_ASSETS_URL . 'js/style-editor.js',
            ['jquery', 'nouislider'],
            KCPF_VERSION,
            true
        );
        
        wp_localize_script('kcpf-style-editor', 'kcpfStyleEditor', [
            'settings' => KCPF_Style_Settings_Manager::getSettings(),
        ]);
    }
    
    /**
     * Render admin page
     */
    public static function renderPage()
    {
        // Handle form submission
        if (isset($_POST['kcpf_save_styles']) && check_admin_referer('kcpf_save_styles')) {
            self::handleSave();
        }
        
        // Handle reset
        if (isset($_POST['kcpf_reset_styles']) && check_admin_referer('kcpf_reset_styles')) {
            if (class_exists('KCPF_Style_Settings_Manager')) {
                KCPF_Style_Settings_Manager::resetToDefaults();
                echo '<div class="notice notice-success"><p>Styles reset to defaults successfully.</p></div>';
            }
        }
        
        if (!class_exists('KCPF_Style_Settings_Manager')) {
            echo '<div class="notice notice-error"><p>Style Settings Manager not found. Please check plugin installation.</p></div>';
            return;
        }
        
        $settings = KCPF_Style_Settings_Manager::getSettings();
        
        ?>
        <div class="wrap kcpf-style-editor-wrap">
            <h1>Filter Style Editor</h1>
            <p class="description">Customize the appearance of your property filters. Changes will override all default styles.</p>
            
            <form method="post" id="kcpf-style-form">
                <?php wp_nonce_field('kcpf_save_styles'); ?>
                
                <div class="kcpf-editor-layout">
                    <div class="kcpf-editor-left">
                        <div class="kcpf-editor-section">
                            <h2>Filter Container</h2>
                            <?php self::renderSection('filter_container', isset($settings['filter_container']) ? $settings['filter_container'] : []); ?>
                        </div>
                        
                        <div class="kcpf-editor-section">
                            <h2>Filter Label</h2>
                            <?php self::renderSection('filter_label', isset($settings['filter_label']) ? $settings['filter_label'] : []); ?>
                        </div>
                        
                        <div class="kcpf-editor-section">
                            <h2>Select Dropdown</h2>
                            <?php self::renderSection('select', isset($settings['select']) ? $settings['select'] : []); ?>
                        </div>
                        
                        <div class="kcpf-editor-section">
                            <h2>Multi-Select Trigger</h2>
                            <?php self::renderSection('multiselect_trigger', isset($settings['multiselect_trigger']) ? $settings['multiselect_trigger'] : []); ?>
                        </div>
                        
                        <div class="kcpf-editor-section">
                            <h2>Multi-Select Chip</h2>
                            <?php self::renderSection('multiselect_chip', isset($settings['multiselect_chip']) ? $settings['multiselect_chip'] : []); ?>
                        </div>
                        
                        <div class="kcpf-editor-section">
                            <h2>Multi-Select Dropdown Menu</h2>
                            <?php self::renderSection('multiselect_dropdown', isset($settings['multiselect_dropdown']) ? $settings['multiselect_dropdown'] : []); ?>
                        </div>
                        
                        <div class="kcpf-editor-section">
                            <h2>Multi-Select Option</h2>
                            <?php self::renderSection('multiselect_option', isset($settings['multiselect_option']) ? $settings['multiselect_option'] : []); ?>
                        </div>
                        
                        <div class="kcpf-editor-section">
                            <h2>Input Fields</h2>
                            <?php self::renderSection('input', isset($settings['input']) ? $settings['input'] : []); ?>
                        </div>
                        
                        <div class="kcpf-editor-section">
                            <h2>Apply Button</h2>
                            <?php self::renderSection('apply_button', isset($settings['apply_button']) ? $settings['apply_button'] : []); ?>
                        </div>
                        
                        <div class="kcpf-editor-section">
                            <h2>Reset Button</h2>
                            <?php self::renderSection('reset_button', isset($settings['reset_button']) ? $settings['reset_button'] : []); ?>
                        </div>
                        
                        <div class="kcpf-editor-section">
                            <h2>Toggle Buttons</h2>
                            <?php self::renderSection('toggle_button', isset($settings['toggle_button']) ? $settings['toggle_button'] : []); ?>
                        </div>
                        
                        <div class="kcpf-editor-section">
                            <h2>Range Slider</h2>
                            <?php self::renderSection('range_slider', isset($settings['range_slider']) ? $settings['range_slider'] : []); ?>
                        </div>
                        
                        <div class="kcpf-editor-actions">
                            <button type="submit" name="kcpf_save_styles" class="button button-primary">
                                Save Styles
                            </button>
                            <button type="submit" name="kcpf_reset_styles" class="button button-secondary" 
                                    onclick="return confirm('Are you sure you want to reset all styles to defaults?');">
                                Reset to Defaults
                            </button>
                        </div>
                    </div>
                    
                    <div class="kcpf-editor-right">
                        <div class="kcpf-preview-panel">
                            <?php echo KCPF_Style_Preview::render(); ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render a settings section
     */
    private static function renderSection($section_name, $settings)
    {
        if (!is_array($settings) || empty($settings)) {
            return;
        }
        ?>
        <div class="kcpf-field-group">
            <?php foreach ($settings as $key => $value) : ?>
                <div class="kcpf-field">
                    <label><?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?></label>
                    <input type="text" 
                           name="kcpf_styles[<?php echo esc_attr($section_name); ?>][<?php echo esc_attr($key); ?>]" 
                           value="<?php echo esc_attr($value); ?>"
                           class="kcpf-style-input">
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    /**
     * Handle form save
     */
    private static function handleSave()
    {
        if (!isset($_POST['kcpf_styles']) || !is_array($_POST['kcpf_styles'])) {
            echo '<div class="notice notice-error"><p>No settings provided.</p></div>';
            return;
        }
        
        $settings = map_deep($_POST['kcpf_styles'], 'sanitize_text_field');
        
        if (KCPF_Style_Settings_Manager::saveSettings($settings)) {
            echo '<div class="notice notice-success"><p>Styles saved successfully!</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Failed to save styles.</p></div>';
        }
    }
}

