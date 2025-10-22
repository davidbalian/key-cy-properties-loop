<?php
/**
 * Debug Viewer Class
 * 
 * Simple admin page to view KCPF error logs
 * 
 * @package Key_CY_Properties_Filter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KCPF_Debug_Viewer
{
    /**
     * Initialize debug viewer
     */
    public static function init()
    {
        // Only for administrators
        if (!current_user_can('manage_options')) {
            return;
        }
        
        add_action('admin_menu', [__CLASS__, 'addAdminMenu']);
    }
    
    /**
     * Add admin menu page
     */
    public static function addAdminMenu()
    {
        add_submenu_page(
            'tools.php',
            'KCPF Debug Logs',
            'KCPF Debug Logs',
            'manage_options',
            'kcpf-debug-logs',
            [__CLASS__, 'renderPage']
        );
    }
    
    /**
     * Render debug logs page
     */
    public static function renderPage()
    {
        // Handle clear log action
        if (isset($_POST['kcpf_clear_log']) && check_admin_referer('kcpf_clear_log')) {
            self::clearLog();
            echo '<div class="notice notice-success"><p>Debug log cleared successfully.</p></div>';
        }
        
        $log_file = WP_CONTENT_DIR . '/debug.log';
        $log_exists = file_exists($log_file);
        
        ?>
        <div class="wrap">
            <h1>KCPF Debug Logs</h1>
            
            <?php if (!$log_exists) : ?>
                <div class="notice notice-warning">
                    <p><strong>Debug log file not found.</strong></p>
                    <p>Location: <code><?php echo esc_html($log_file); ?></code></p>
                    <p>The file will be created when the first error is logged.</p>
                </div>
            <?php else : ?>
                <div class="notice notice-info">
                    <p><strong>Log file location:</strong> <code><?php echo esc_html($log_file); ?></code></p>
                    <p><strong>File size:</strong> <?php echo size_format(filesize($log_file)); ?></p>
                    <p><strong>Last modified:</strong> <?php echo date('Y-m-d H:i:s', filemtime($log_file)); ?></p>
                </div>
                
                <form method="post" style="margin-bottom: 20px;">
                    <?php wp_nonce_field('kcpf_clear_log'); ?>
                    <button type="submit" name="kcpf_clear_log" class="button button-secondary" 
                            onclick="return confirm('Are you sure you want to clear the debug log?');">
                        Clear Debug Log
                    </button>
                    <a href="<?php echo admin_url('tools.php?page=kcpf-debug-logs'); ?>" class="button button-secondary">
                        Refresh
                    </a>
                </form>
                
                <h2>KCPF Errors (Last 100 lines)</h2>
                <div style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; border-radius: 4px; max-height: 600px; overflow-y: auto;">
                    <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-family: monospace; font-size: 12px;"><?php
                        echo esc_html(self::getKCPFLogs());
                    ?></pre>
                </div>
                
                <h2>Full Debug Log (Last 200 lines)</h2>
                <div style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; border-radius: 4px; max-height: 600px; overflow-y: auto;">
                    <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-family: monospace; font-size: 12px;"><?php
                        echo esc_html(self::getFullLog());
                    ?></pre>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #ddd;">
                <h3>Debug Settings</h3>
                <p>Your wp-config.php has the following debug settings:</p>
                <ul>
                    <li><strong>WP_DEBUG:</strong> <?php echo defined('WP_DEBUG') && WP_DEBUG ? '✅ Enabled' : '❌ Disabled'; ?></li>
                    <li><strong>WP_DEBUG_LOG:</strong> <?php echo defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? '✅ Enabled' : '❌ Disabled'; ?></li>
                    <li><strong>WP_DEBUG_DISPLAY:</strong> <?php echo defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? '⚠️ Enabled (should be false for production)' : '✅ Disabled (correct)'; ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get KCPF-specific log entries
     */
    private static function getKCPFLogs()
    {
        $log_file = WP_CONTENT_DIR . '/debug.log';
        
        if (!file_exists($log_file)) {
            return 'No log file found.';
        }
        
        // Read last 1000 lines and filter for KCPF
        $lines = self::tailFile($log_file, 1000);
        $kcpf_lines = array_filter($lines, function($line) {
            return stripos($line, 'KCPF') !== false;
        });
        
        if (empty($kcpf_lines)) {
            return 'No KCPF errors found in the log.';
        }
        
        // Return last 100 KCPF lines
        return implode("\n", array_slice($kcpf_lines, -100));
    }
    
    /**
     * Get full log (last 200 lines)
     */
    private static function getFullLog()
    {
        $log_file = WP_CONTENT_DIR . '/debug.log';
        
        if (!file_exists($log_file)) {
            return 'No log file found.';
        }
        
        $lines = self::tailFile($log_file, 200);
        return implode("\n", $lines);
    }
    
    /**
     * Read last N lines from file
     */
    private static function tailFile($file, $lines = 100)
    {
        $handle = fopen($file, "r");
        if (!$handle) {
            return ['Could not open log file.'];
        }
        
        $line_count = 0;
        $pos = -2;
        $lines_array = [];
        
        // Start from end of file
        fseek($handle, 0, SEEK_END);
        $filesize = ftell($handle);
        
        if ($filesize == 0) {
            fclose($handle);
            return ['Log file is empty.'];
        }
        
        // Read backwards
        while ($line_count < $lines && ftell($handle) > 0) {
            fseek($handle, $pos, SEEK_END);
            $char = fgetc($handle);
            
            if ($char === "\n") {
                if (ftell($handle) < $filesize - 1) {
                    $line_count++;
                }
            }
            
            $pos--;
        }
        
        // Read the lines
        $content = '';
        while (!feof($handle)) {
            $content .= fgets($handle);
        }
        
        fclose($handle);
        
        return array_filter(explode("\n", $content));
    }
    
    /**
     * Clear debug log
     */
    private static function clearLog()
    {
        $log_file = WP_CONTENT_DIR . '/debug.log';
        
        if (file_exists($log_file)) {
            file_put_contents($log_file, '');
        }
    }
}

