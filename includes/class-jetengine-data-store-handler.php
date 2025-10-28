<?php
/**
 * JetEngine Data Store Handler Class
 *
 * Provides programmatic access to JetEngine data stores
 * Allows adding, removing, and managing dynamic data
 *
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_JetEngine_Data_Store_Handler
{
    /**
     * Check if JetEngine and data stores are available
     *
     * @return bool
     */
    public static function isAvailable()
    {
        return function_exists('jet_engine') &&
               isset(jet_engine()->data_stores) &&
               method_exists(jet_engine()->data_stores, 'get_stores');
    }

    /**
     * Get all available data stores
     *
     * @return array Array of data stores [id => store_data]
     */
    public static function getAllStores()
    {
        if (!self::isAvailable()) {
            return [];
        }

        try {
            $data_stores = jet_engine()->data_stores;

            // Try different methods to get stores
            if (method_exists($data_stores, 'get_stores')) {
                return $data_stores->get_stores();
            } elseif (method_exists($data_stores, 'get_items')) {
                return $data_stores->get_items();
            }

            return [];
        } catch (Exception $e) {
            error_log('KCPF Data Store Handler Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get items from a specific data store
     *
     * @param string $store_id Data store ID
     * @return array Array of items
     */
    public static function getStoreItems($store_id)
    {
        if (!self::isAvailable() || empty($store_id)) {
            return [];
        }

        try {
            $data_stores = jet_engine()->data_stores;

            if (method_exists($data_stores, 'get_store_items')) {
                return $data_stores->get_store_items($store_id);
            }

            return [];
        } catch (Exception $e) {
            error_log('KCPF Data Store Handler Error getting items: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Add item to data store
     *
     * @param string $store_id Data store ID
     * @param array $item_data Item data to add
     * @return bool Success status
     */
    public static function addToStore($store_id, $item_data)
    {
        if (!self::isAvailable() || empty($store_id) || empty($item_data)) {
            return false;
        }

        try {
            $data_stores = jet_engine()->data_stores;

            if (method_exists($data_stores, 'add_to_store')) {
                return $data_stores->add_to_store($store_id, $item_data);
            } elseif (method_exists($data_stores, 'add_item')) {
                return $data_stores->add_item($store_id, $item_data);
            }

            error_log('KCPF Data Store Handler: No add method available');
            return false;
        } catch (Exception $e) {
            error_log('KCPF Data Store Handler Error adding item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove item from data store
     *
     * @param string $store_id Data store ID
     * @param string|int $item_id Item ID to remove
     * @return bool Success status
     */
    public static function removeFromStore($store_id, $item_id)
    {
        if (!self::isAvailable() || empty($store_id) || empty($item_id)) {
            return false;
        }

        try {
            $data_stores = jet_engine()->data_stores;

            if (method_exists($data_stores, 'remove_from_store')) {
                return $data_stores->remove_from_store($store_id, $item_id);
            } elseif (method_exists($data_stores, 'delete_item')) {
                return $data_stores->delete_item($store_id, $item_id);
            }

            error_log('KCPF Data Store Handler: No remove method available');
            return false;
        } catch (Exception $e) {
            error_log('KCPF Data Store Handler Error removing item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update item in data store
     *
     * @param string $store_id Data store ID
     * @param string|int $item_id Item ID to update
     * @param array $item_data Updated item data
     * @return bool Success status
     */
    public static function updateStoreItem($store_id, $item_id, $item_data)
    {
        if (!self::isAvailable() || empty($store_id) || empty($item_id)) {
            return false;
        }

        try {
            $data_stores = jet_engine()->data_stores;

            if (method_exists($data_stores, 'update_store_item')) {
                return $data_stores->update_store_item($store_id, $item_id, $item_data);
            } elseif (method_exists($data_stores, 'update_item')) {
                return $data_stores->update_item($store_id, $item_id, $item_data);
            }

            error_log('KCPF Data Store Handler: No update method available');
            return false;
        } catch (Exception $e) {
            error_log('KCPF Data Store Handler Error updating item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get data store by ID
     *
     * @param string $store_id Data store ID
     * @return array|null Store data or null if not found
     */
    public static function getStore($store_id)
    {
        if (!self::isAvailable() || empty($store_id)) {
            return null;
        }

        try {
            $data_stores = jet_engine()->data_stores;

            if (method_exists($data_stores, 'get_store')) {
                return $data_stores->get_store($store_id);
            }

            return null;
        } catch (Exception $e) {
            error_log('KCPF Data Store Handler Error getting store: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if data store exists
     *
     * @param string $store_id Data store ID
     * @return bool
     */
    public static function storeExists($store_id)
    {
        $stores = self::getAllStores();
        return isset($stores[$store_id]);
    }

    /**
     * Get store item count
     *
     * @param string $store_id Data store ID
     * @return int Number of items in store
     */
    public static function getStoreItemCount($store_id)
    {
        $items = self::getStoreItems($store_id);
        return count($items);
    }

    /**
     * Search items in data store
     *
     * @param string $store_id Data store ID
     * @param string $search_term Search term
     * @param string $field Field to search in (optional)
     * @return array Matching items
     */
    public static function searchStoreItems($store_id, $search_term, $field = null)
    {
        $items = self::getStoreItems($store_id);

        if (empty($items) || empty($search_term)) {
            return [];
        }

        $results = [];
        $search_term = strtolower($search_term);

        foreach ($items as $item_id => $item) {
            $match = false;

            if ($field && isset($item[$field])) {
                // Search in specific field
                if (is_string($item[$field]) &&
                    strpos(strtolower($item[$field]), $search_term) !== false) {
                    $match = true;
                }
            } else {
                // Search in all fields
                foreach ($item as $value) {
                    if (is_string($value) &&
                        strpos(strtolower($value), $search_term) !== false) {
                        $match = true;
                        break;
                    }
                }
            }

            if ($match) {
                $results[$item_id] = $item;
            }
        }

        return $results;
    }

    /**
     * Debug method to inspect data stores
     *
     * @param string $store_id Optional specific store ID to inspect
     * @return array Debug information
     */
    public static function debug($store_id = null)
    {
        $debug = [
            'available' => self::isAvailable(),
            'jetengine_version' => defined('JET_ENGINE_VERSION') ? JET_ENGINE_VERSION : 'unknown',
        ];

        if (!self::isAvailable()) {
            $debug['error'] = 'JetEngine data stores not available';
            return $debug;
        }

        $debug['all_stores'] = self::getAllStores();

        if ($store_id && self::storeExists($store_id)) {
            $debug['store_data'] = self::getStore($store_id);
            $debug['store_items_count'] = self::getStoreItemCount($store_id);
            $debug['store_items_sample'] = array_slice(self::getStoreItems($store_id), 0, 3);
        }

        return $debug;
    }
}
