# AJAX Timeout Debugging - v2.3.5

## What I Added

### 1. Simple AJAX Test Endpoint

A test endpoint that doesn't do any processing - just returns success. This will tell us if AJAX works at all on your server.

**URL:** `https://gni.932.myftpupload.com/wp-admin/admin-ajax.php?action=kcpf_test`

### 2. Automatic Test on Page Load

The page will automatically test AJAX 1 second after loading. Check your console for:

- `[KCPF] AJAX test SUCCESS:` = AJAX is working ✅
- `[KCPF] AJAX test FAILED:` = AJAX is broken ❌

### 3. Enhanced Error Logging

When AJAX fails, you'll now see:

- HTTP status code
- Response headers
- Response text (even if it's an error)
- Ready state

### 4. Progress Tracking

Added listeners to track if the request is making any progress.

## What to Do Now

### Step 1: Clear Cache

Clear your browser cache completely to ensure the new JavaScript loads.

### Step 2: Load the Page

Open the page with the filters and immediately check the console. You should see:

```
[KCPF] Filters initialized
[KCPF] Testing AJAX endpoint...
[KCPF] AJAX test SUCCESS: {...}
```

### Step 3: Try Submitting Filters

Submit the filters again and wait for the timeout (60 seconds). Check for any new error messages.

### Step 4: Share the Results

Copy and paste ALL console output from:

1. The AJAX test
2. The filter submission attempt

## Possible Issues

### Issue 1: AJAX Test Fails Too

**Meaning:** WordPress AJAX isn't working at all on your server.

**Possible Causes:**

- Security plugin blocking AJAX requests
- Server firewall blocking admin-ajax.php
- WordPress AJAX disabled in wp-config.php
- `.htaccess` rules interfering

**Quick Fix:** Open the test URL directly in your browser:

```
https://gni.932.myftpupload.com/wp-admin/admin-ajax.php?action=kcpf_test
```

If this gives you JSON, AJAX works. If it gives an error or blank page, AJAX is broken.

### Issue 2: AJAX Test Works, But Filter Request Times Out

**Meaning:** The AJAX handler runs into an infinite loop or slow query.

**Quick Test:** Temporarily simplify the query by commenting out complex filters.

### Issue 3: Network Error (xhr.status = 0)

**Meaning:** The request is being blocked before it reaches the server.

**Possible Causes:**

- Browser extension blocking requests
- CORS error
- HTTPS/HTTP mismatch
- Server rejecting the request

## What the Logs Should Show

### If Working Correctly:

```
[KCPF] === Starting AJAX Request ===
[KCPF] AJAX URL: ...
[KCPF] Sending AJAX request...
[KCPF] AJAX response received: {...}
[KCPF] Response timestamp: ...
[KCPF] Results updated successfully
[KCPF] AJAX request complete
```

### If Timing Out:

```
[KCPF] === Starting AJAX Request ===
[KCPF] AJAX URL: ...
[KCPF] Sending AJAX request...
... (60 seconds pass) ...
[KCPF] ============ AJAX ERROR ============
[KCPF] Status: timeout
[KCPF] Request timed out after 60 seconds
```

## Manual Server Check

If you have SSH or FTP access, check:

1. **PHP Error Log** (location varies):

   ```bash
   tail -f /path/to/error.log
   ```

2. **WordPress Debug Log**:

   ```bash
   tail -f wp-content/debug.log
   ```

3. **Test PHP directly**:
   Create a test file `test-ajax.php` in the plugin folder:

   ```php
   <?php
   define('WP_USE_THEMES', false);
   require_once '../../../../wp-load.php';

   wp_send_json_success(['test' => 'ok']);
   ```

   Access: `https://gni.932.myftpupload.com/wp-content/plugins/key-cy-properties-filter/test-ajax.php`

## Next Steps Based on Results

### If AJAX Test Works:

The issue is in the `ajaxLoadProperties()` function. Share the server logs.

### If AJAX Test Fails:

WordPress AJAX is broken. We need to fix server configuration first.

### If Still Timing Out:

Need to check:

1. Is the request reaching the server? (check server access logs)
2. Is PHP erroring? (check PHP error logs)
3. Is there an infinite loop? (check for recursive calls)

## Questions to Answer

1. Does the test AJAX endpoint work? (check console)
2. Does opening the test URL directly in browser work?
3. Are there any PHP errors visible in browser Network tab?
4. Is there a security plugin that might be blocking AJAX?
5. Are you using a caching plugin that might interfere?
