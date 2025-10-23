<?php
/**
 * Settings Manager Class
 * 
 * Manages plugin settings including Google Maps API key
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Settings_Manager
{
    /**
     * Settings page slug
     */
    const PAGE_SLUG = 'kcpf-settings';
    
    /**
     * Option name for Google Maps API key
     */
    const MAPS_API_KEY_OPTION = 'kcpf_google_maps_api_key';
    
    /**
     * Initialize settings
     */
    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'addSettingsPage']);
        add_action('admin_init', [__CLASS__, 'registerSettings']);
    }
    
    /**
     * Add settings page to WordPress admin menu
     */
    public static function addSettingsPage()
    {
        add_options_page(
            __('Properties Map Settings', 'key-cy-properties-filter'),
            __('Properties Map', 'key-cy-properties-filter'),
            'manage_options',
            self::PAGE_SLUG,
            [__CLASS__, 'renderSettingsPage']
        );
    }
    
    /**
     * Register plugin settings
     */
    public static function registerSettings()
    {
        register_setting(
            'kcpf_settings_group',
            self::MAPS_API_KEY_OPTION,
            [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '',
            ]
        );
        
        add_settings_section(
            'kcpf_maps_section',
            __('Google Maps Configuration', 'key-cy-properties-filter'),
            [__CLASS__, 'renderMapsSection'],
            self::PAGE_SLUG
        );
        
        add_settings_field(
            'kcpf_maps_api_key',
            __('Google Maps API Key', 'key-cy-properties-filter'),
            [__CLASS__, 'renderApiKeyField'],
            self::PAGE_SLUG,
            'kcpf_maps_section'
        );
    }
    
    /**
     * Render settings page
     */
    public static function renderSettingsPage()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Show success message if settings were saved
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'kcpf_messages',
                'kcpf_message',
                __('Settings saved successfully.', 'key-cy-properties-filter'),
                'updated'
            );
        }
        
        settings_errors('kcpf_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('kcpf_settings_group');
                do_settings_sections(self::PAGE_SLUG);
                submit_button(__('Save Settings', 'key-cy-properties-filter'));
                ?>
            </form>
            
            <div class="kcpf-settings-help" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-left: 4px solid #2271b1;">
                <h2><?php _e('How to Get a Google Maps API Key', 'key-cy-properties-filter'); ?></h2>
                <ol>
                    <li><?php _e('Go to the <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>', 'key-cy-properties-filter'); ?></li>
                    <li><?php _e('Create a new project or select an existing one', 'key-cy-properties-filter'); ?></li>
                    <li><?php _e('Enable the "Maps JavaScript API"', 'key-cy-properties-filter'); ?></li>
                    <li><?php _e('Go to "Credentials" and create an API key', 'key-cy-properties-filter'); ?></li>
                    <li><?php _e('Restrict the API key to your domain for security', 'key-cy-properties-filter'); ?></li>
                    <li><?php _e('Copy the API key and paste it above', 'key-cy-properties-filter'); ?></li>
                </ol>
                
                <h3><?php _e('Shortcode Usage', 'key-cy-properties-filter'); ?></h3>
                <p><?php _e('Use the following shortcodes to display the properties map:', 'key-cy-properties-filter'); ?></p>
                <code>[properties_map purpose="sale"]</code><br>
                <code>[properties_map purpose="rent"]</code>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render maps section description
     */
    public static function renderMapsSection()
    {
        echo '<p>' . __('Configure Google Maps integration for the properties map view.', 'key-cy-properties-filter') . '</p>';
    }
    
    /**
     * Render API key field
     */
    public static function renderApiKeyField()
    {
        $value = get_option(self::MAPS_API_KEY_OPTION, '');
        ?>
        <input 
            type="text" 
            name="<?php echo esc_attr(self::MAPS_API_KEY_OPTION); ?>" 
            value="<?php echo esc_attr($value); ?>" 
            class="regular-text"
            placeholder="AIzaSy..."
        />
        <p class="description">
            <?php _e('Enter your Google Maps API key. This is required for the map view to work.', 'key-cy-properties-filter'); ?>
        </p>
        <?php
    }
    
    /**
     * Get Google Maps API key
     * 
     * @return string API key or empty string
     */
    public static function getApiKey()
    {
        return get_option(self::MAPS_API_KEY_OPTION, '');
    }
    
    /**
     * Check if API key is configured
     * 
     * @return bool
     */
    public static function hasApiKey()
    {
        $key = self::getApiKey();
        return !empty($key);
    }
}

