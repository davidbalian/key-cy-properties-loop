# Bedroom & Bathroom Filter Debug Guide

## What I've Added

I've added comprehensive logging to help diagnose why bedroom and bathroom filters are returning all properties. The logging covers:

### 1. JavaScript Console Logging
- **AJAX Handler**: Logs all parameters being sent to the server
- **Multiselect Handler**: Logs when bedroom/bathroom checkboxes are changed
- **Parameter Details**: Shows specific bedroom/bathroom parameter values

### 2. PHP Server-Side Logging
- **AJAX Manager**: Logs received filter parameters
- **URL Manager**: Logs how filters are processed from $_GET
- **Query Handler**: Detailed logging of bedroom/bathroom query construction
- **Field Config**: Debug information about meta key mapping

## How to Test

### Step 1: Open Browser Console
1. Go to your property filter page
2. Open browser developer tools (F12)
3. Go to the Console tab

### Step 2: Apply a Bedroom Filter
1. Click on the Bedrooms filter dropdown
2. Check one or more bedroom options (e.g., "2", "3")
3. Watch the console for logs starting with `[KCPF]`

### Step 3: Check Console Output
Look for these specific logs:
```
[KCPF] Bedrooms filter changed
[KCPF] bedrooms checked values: ["2", "3"]
[KCPF] All bedroom parameters: ["2", "3"]
[KCPF] Bedrooms parameter: 2
```

### Step 4: Check Server Logs
The server will log detailed information about:
- What parameters are received
- How they're processed
- What meta keys are used
- What query conditions are built

## Common Issues to Look For

### 1. Parameters Not Being Sent
If you see:
```
[KCPF] Bedrooms parameter: null
[KCPF] All bedroom parameters: []
```
The JavaScript isn't collecting the filter values properly.

### 2. Parameters Not Being Received
If you see in server logs:
```
[KCPF] URL_Manager - Raw $_GET bedrooms: NOT_SET
[KCPF] URL_Manager - Processed bedrooms: 
```
The AJAX request isn't sending the parameters correctly.

### 3. Wrong Meta Keys
If you see:
```
[KCPF] Field Config Debug: Array ( [metaKey] => wrong_key )
```
The field configuration isn't mapping to the correct database fields.

### 4. Empty Filter Values
If you see:
```
[KCPF] No bedrooms filter applied - filters[bedrooms] is empty
```
The filters are being received but processed as empty.

## Expected Behavior

### For Sale Properties
- Meta key should be: `bedrooms` and `bathrooms`
- Query should use LIKE comparison
- Multiple values should create OR conditions

### For Rent Properties  
- Meta key should be: `rent_bedrooms` and `rent_bathrooms`
- Same query logic as sale properties

## Quick Test Commands

You can also test the field configuration directly by adding this to a PHP file:

```php
// Test field configuration
$bedroomConfig = KCPF_Field_Config::debugFieldConfig('bedrooms', 'sale');
$bathroomConfig = KCPF_Field_Config::debugFieldConfig('bathrooms', 'sale');
var_dump($bedroomConfig, $bathroomConfig);
```

## Next Steps

1. Apply a bedroom filter and check console output
2. Share the console logs with me
3. If no logs appear, the JavaScript might not be loading properly
4. If logs show wrong values, we'll know where the issue is

The logging will help us identify exactly where the bedroom/bathroom filter data is getting lost or processed incorrectly.
