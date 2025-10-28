<?php
/**
 * Favourites Manager
 *
 * Stores and retrieves user favourites in user meta, and renders the
 * favourites toggle icon for property cards.
 *
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Favourites_Manager
{
    /**
     * User meta key where favourites are stored
     * Structure: [ 'sale' => [ids...], 'rent' => [ids...] ]
     *
     * @var string
     */
    private static $metaKey = 'kcpf_favourites';

    /**
     * Validate purpose value
     *
     * @param string $purpose
     * @return string 'sale' or 'rent'
     */
    public static function normalizePurpose($purpose)
    {
        $purpose = strtolower(trim((string)$purpose));
        return in_array($purpose, ['sale', 'rent'], true) ? $purpose : 'sale';
    }

    /**
     * Get all favourites for current user
     *
     * @return array
     */
    public static function getCurrentUserFavourites()
    {
        if (!is_user_logged_in()) {
            return ['sale' => [], 'rent' => []];
        }

        $userId = get_current_user_id();
        $stored = get_user_meta($userId, self::$metaKey, true);

        if (!is_array($stored)) {
            return ['sale' => [], 'rent' => []];
        }

        $sale = isset($stored['sale']) && is_array($stored['sale']) ? array_values(array_unique(array_map('intval', $stored['sale']))) : [];
        $rent = isset($stored['rent']) && is_array($stored['rent']) ? array_values(array_unique(array_map('intval', $stored['rent']))) : [];

        return [
            'sale' => $sale,
            'rent' => $rent,
        ];
    }

    /**
     * Get favourites for purpose
     *
     * @param string $purpose
     * @return int[]
     */
    public static function getFavouritesByPurpose($purpose)
    {
        $purpose = self::normalizePurpose($purpose);
        $all = self::getCurrentUserFavourites();
        return $all[$purpose] ?? [];
    }

    /**
     * Check if property is favourited
     *
     * @param int $propertyId
     * @param string $purpose
     * @return bool
     */
    public static function isFavourited($propertyId, $purpose)
    {
        if (!is_user_logged_in()) {
            return false;
        }
        $propertyId = intval($propertyId);
        $purpose = self::normalizePurpose($purpose);
        $list = self::getFavouritesByPurpose($purpose);
        return in_array($propertyId, $list, true);
    }

    /**
     * Toggle favourite for current user
     *
     * @param int $propertyId
     * @param string $purpose
     * @return bool True if now favourited, false if removed
     */
    public static function toggleFavourite($propertyId, $purpose)
    {
        if (!is_user_logged_in()) {
            return false;
        }
        $propertyId = intval($propertyId);
        if ($propertyId <= 0 || get_post_type($propertyId) !== 'properties') {
            return false;
        }

        $purpose = self::normalizePurpose($purpose);
        $userId = get_current_user_id();
        $current = self::getCurrentUserFavourites();
        $list = $current[$purpose] ?? [];

        if (in_array($propertyId, $list, true)) {
            // Remove
            $list = array_values(array_diff($list, [$propertyId]));
            $current[$purpose] = $list;
            update_user_meta($userId, self::$metaKey, $current);
            return false;
        }

        // Add
        $list[] = $propertyId;
        $current[$purpose] = array_values(array_unique($list));
        update_user_meta($userId, self::$metaKey, $current);
        return true;
    }

    /**
     * Render favourites toggle button HTML
     *
     * @param int $propertyId
     * @param string $purpose
     * @return string
     */
    public static function renderIcon($propertyId, $purpose = 'sale')
    {
        $propertyId = intval($propertyId);
        $purpose = self::normalizePurpose($purpose);

        $isLoggedIn = is_user_logged_in();
        $active = $isLoggedIn && self::isFavourited($propertyId, $purpose);

        $classes = 'kcpf-favourite-btn';
        if ($active) {
            $classes .= ' is-active';
        }
        if (!$isLoggedIn) {
            $classes .= ' is-disabled';
        }

        $attrs = sprintf(
            'class="%s" data-property-id="%d" data-purpose="%s" aria-pressed="%s"%s',
            esc_attr($classes),
            $propertyId,
            esc_attr($purpose),
            $active ? 'true' : 'false',
            $isLoggedIn ? '' : ' title="Login to save favourites"'
        );

        $icon_class = $active ? 'fas fa-star' : 'far fa-star';

        return '<span ' . $attrs . '><i class="kcpf-favourite-icon ' . esc_attr($icon_class) . '" aria-hidden="true"></i></span>';
    }

}


