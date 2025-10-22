# AJAX Timeout Debugging Guide

## Issue

AJAX requests are timing out and the URL doesn't change when filters are applied.

## Changes Made (v2.3.3)

### 1. PHP AJAX Handler (`key-cy-properties-filter.php`)

- Added `set_time_limit(60)` to prevent PHP from hanging indefinitely
- Enhanced error logging with timestamps
- All filter parameters are automatically read from `$_GET` via `URL_Manager`

### 2. JavaScript (`assets/js/filters.js`)

- Increased AJAX timeout from 30 to 60 seconds
- Added comprehensive logging with timestamps at every step
- Enhanced error reporting to show XHR status and response
- Clear indication of when AJAX starts and completes

## How to Debug

### Step 1: Check Browser Console

Open your browser's developer console and submit a filter. You should see:

```
[KCPF] === Starting AJAX Request ===
[KCPF] AJAX URL: [full URL]
[KCPF] New URL: [destination URL]
[KCPF] Request timestamp: [ISO timestamp]
[KCPF] Sending AJAX request...
```

**What to look for:**

- Does the AJAX URL look correct?
- Are the filter parameters included in the URL?
- Does it get stuck at "Sending AJAX request..."?

### Step 2: Check Network Tab

1. Open browser DevTools → Network tab
2. Submit a filter
3. Look for the AJAX request (should start with `admin-ajax.php`)
4. Click on it to see details

**What to check:**

- **Status**: Pending, 200, 500, or timeout?
- **Time**: How long does it take?
- **Preview/Response**: What does the server return?
- **Headers**: Check Request URL and Response headers

### Step 3: Check Server Logs

Check your WordPress debug log (usually `wp-content/debug.log`) for:

```
[KCPF] AJAX request started with params: ...
[KCPF] Calling render with attrs: ...
[KCPF] Render completed, HTML length: ...
```

**What to look for:**

- Does it log the start?
- Does it log completion?
- Any PHP errors or warnings?
- Where does it stop?

### Step 4: Test AJAX URL Directly

Copy the AJAX URL from the console and open it directly in your browser (while logged in to WordPress).

**What to expect:**

- If it works: You'll see JSON response with HTML
- If it fails: You'll see an error message or blank page

## Common Issues

### Issue 1: AJAX URL is Wrong

**Symptom**: Console shows incorrect URL
**Fix**: Check that `kcpfData.ajaxUrl` is correct in the console

### Issue 2: No Server Response

**Symptom**: Request stays "pending" forever
**Fix**:

- Check server error logs
- Verify PHP error reporting is enabled
- Check if query is running (might be slow database query)

### Issue 3: URL Doesn't Update

**Symptom**: AJAX completes but URL stays the same
**Fix**: This happens AFTER AJAX succeeds - check if AJAX is actually completing

### Issue 4: Slow Database Query

**Symptom**: Takes 30+ seconds, then times out
**Fix**:

- Check if database has proper indexes
- Look for complex queries in logs
- Might need to optimize WP_Query

## Understanding the Flow

```
1. User submits filter form
   ↓
2. JavaScript intercepts submit (prevents default)
   ↓
3. JS builds URL parameters from form data
   ↓
4. JS sends AJAX request to admin-ajax.php
   ↓
5. Server receives request, processes filters
   ↓
6. Server queries database for matching properties
   ↓
7. Server returns JSON with HTML
   ↓
8. JS receives response
   ↓
9. JS updates page content
   ↓
10. JS updates browser URL (history.pushState)
```

## Expected Behavior

- **URL changes AFTER AJAX succeeds** - this is correct behavior
- If AJAX times out, URL won't change
- Console should show timestamps showing how long each step takes

## Next Steps

1. Clear browser cache to ensure latest JavaScript loads
2. Submit a filter and check the console logs
3. Share the console output and any error messages
4. Check server logs for PHP errors
