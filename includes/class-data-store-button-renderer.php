<?php
/**
 * Data Store Button Renderer Class
 *
 * Renders JetEngine data store buttons for adding/removing properties
 * Integrates with existing JetEngine data store JavaScript functionality
 *
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Data_Store_Button_Renderer
{
    /**
     * Default store configuration
     *
     * @var array
     */
    private static $defaultStoreConfig = [
        'slug' => 'favorite_properties_store',
        'type' => 'user-meta',
        'is_front' => false,
        'size' => 0
    ];

    /**
     * Default action configuration
     *
     * @var array
     */
    private static $defaultActionConfig = [
        'action_after_added' => 'remove_from_store',
        'label' => '',
        'added_url' => '',
        'added_label' => '',
        'synch_id' => 'favourites-for-sale'
    ];

    /**
     * Render data store button for a property
     *
     * @param int $property_id Property post ID
     * @param string $purpose Property purpose (sale/rent)
     * @param array $options Additional options
     * @return string HTML output
     */
    public static function renderButton($property_id, $purpose = 'sale', $options = [])
    {
        $options = wp_parse_args($options, [
            'store_config' => self::$defaultStoreConfig,
            'action_config' => self::$defaultActionConfig,
            'icon_add' => self::getDefaultAddIcon(),
            'icon_remove' => self::getDefaultRemoveIcon(),
            'classes' => '',
            'position' => 'absolute', // absolute, relative, etc.
            'check_if_in_store' => true
        ]);

        // Check if property is already in user's favorites
        $is_in_store = $options['check_if_in_store'] ? self::isPropertyInStore($property_id) : false;

        $store_config = wp_parse_args($options['store_config'], self::$defaultStoreConfig);
        $action_config = wp_parse_args($options['action_config'], self::$defaultActionConfig);

        // Build data-args attribute
        $data_args = [
            'store' => $store_config,
            'post_id' => $property_id,
            'action_after_added' => $action_config['action_after_added'],
            'label' => $action_config['label'],
            'icon' => $is_in_store ? $options['icon_remove'] : $options['icon_add'],
            'added_url' => $action_config['added_url'],
            'added_label' => $action_config['added_label'],
            'added_icon' => $options['icon_remove'],
            'synch_id' => $action_config['synch_id'] . '-' . $property_id
        ];

        $data_args_json = wp_json_encode($data_args);

        // Build CSS classes
        $classes = 'elementor-element elementor-absolute elementor-widget elementor-widget-jet-engine-data-store-button';
        if ($options['classes']) {
            $classes .= ' ' . $options['classes'];
        }

        // Build link classes and data attributes
        $link_classes = 'jet-data-store-link';
        $link_classes .= $is_in_store ? ' jet-remove-from-store in-store' : ' jet-add-to-store';

        $inline_style = $is_in_store ? 'opacity: 1;' : '';

        // Build the HTML structure
        $html = '<div class="' . esc_attr($classes) . '" data-id="' . esc_attr(uniqid()) . '" data-element_type="widget" data-settings="{"_position":"' . esc_attr($options['position']) . '"}" data-widget_type="jet-engine-data-store-button.default">';
        $html .= '<div class="elementor-widget-container">';
        $html .= '<div class="jet-data-store-link-wrapper">';
        $html .= '<a href="#" class="' . esc_attr($link_classes) . '" ';
        $html .= 'data-args="' . esc_attr($data_args_json) . '" ';
        $html .= 'data-post="' . esc_attr($property_id) . '" ';
        $html .= 'data-store="' . esc_attr($store_config['slug']) . '"';
        if ($inline_style) {
            $html .= ' style="' . esc_attr($inline_style) . '"';
        }
        $html .= '>';

        // Add the appropriate icon
        if ($is_in_store) {
            $html .= $options['icon_remove'];
        } else {
            $html .= $options['icon_add'];
        }

        $html .= '</a>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Check if property is in user's favorite store
     *
     * @param int $property_id Property post ID
     * @return bool
     */
    public static function isPropertyInStore($property_id)
    {
        if (!is_user_logged_in()) {
            return false;
        }

        $user_id = get_current_user_id();
        $store_slug = self::$defaultStoreConfig['slug'];

        // Get user's favorites from user meta
        $favorites = get_user_meta($user_id, $store_slug, true);

        if (!is_array($favorites)) {
            return false;
        }

        return in_array($property_id, $favorites);
    }

    /**
     * Get default add icon (outline star)
     *
     * @return string HTML
     */
    public static function getDefaultAddIcon()
    {
        return '<div class="jet-data-store-link__icon is-svg-icon">' .
               '<svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">' .
               '<path d="M480 208H308L256 48l-52 160H32l140 96-54 160 138-100 138 100-54-160z" ' .
               'fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"></path>' .
               '</svg></div>';
    }

    /**
     * Get default remove icon (filled star)
     *
     * @return string HTML
     */
    public static function getDefaultRemoveIcon()
    {
        return '<div class="jet-data-store-link__icon is-svg-icon">' .
               '<svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">' .
               '<path d="M394 480a16 16 0 01-9.39-3L256 383.76 127.39 477a16 16 0 01-24.55-18.08L153 310.35 23 221.2a16 16 0 019-29.2h160.38l48.4-148.95a16 16 0 0130.44 0l48.4 149H480a16 16 0 019.05 29.2L359 310.35l50.13 148.53A16 16 0 01394 480z"></path>' .
               '</svg></div>';
    }

    /**
     * Render button with custom store configuration
     *
     * @param int $property_id Property post ID
     * @param array $store_config Custom store configuration
     * @param array $options Additional options
     * @return string HTML output
     */
    public static function renderWithCustomStore($property_id, $store_config, $options = [])
    {
        $options['store_config'] = wp_parse_args($store_config, self::$defaultStoreConfig);
        return self::renderButton($property_id, 'sale', $options);
    }

    /**
     * Get button HTML for use in templates
     *
     * @param int $property_id Property post ID
     * @param array $options Options array
     * @return string HTML
     */
    public static function getButtonHtml($property_id, $options = [])
    {
        return self::renderButton($property_id, 'sale', $options);
    }

    /**
     * Render button for rent properties
     *
     * @param int $property_id Property post ID
     * @param array $options Additional options
     * @return string HTML output
     */
    public static function renderRentButton($property_id, $options = [])
    {
        $options = wp_parse_args($options, [
            'action_config' => array_merge(self::$defaultActionConfig, [
                'synch_id' => 'favourites-for-rent'
            ])
        ]);

        return self::renderButton($property_id, 'rent', $options);
    }
}
