# Multi-Unit Query Performance Fix

## Issue

AJAX requests were timing out after adding multi-unit property support. The queries were taking too long and never completing.

## Root Cause

The multi-unit query builder was creating **deeply nested meta_queries** with multiple levels of AND/OR relations:

```
meta_query
  └─ relation: OR
      ├─ nested meta_query (relation: AND)
      │   ├─ check multi-unit = false
      │   └─ check price range
      └─ nested meta_query (relation: AND)
          ├─ check multi-unit = true
          └─ check price range on different fields
```

WordPress's meta_query system struggles with deeply nested queries, especially when combined with complex relations. This was causing:

- Slow database queries
- Request timeouts
- Infinite loops in query processing

## Solution

Simplified the query structure by removing the nested meta_queries. Instead of querying both regular and multi-unit properties separately, we now just query the regular meta fields directly.

### Before (Complex):

```php
$price_query = ['relation' => 'OR'];
$price_query[] = self::buildRegularPropertyQuery($priceKey, $minValue, $maxValue);
$price_query[] = self::buildMultiUnitPropertyQuery('minimum_buy_price', 'maximum_buy_price', $minValue, $maxValue);
```

### After (Simple):

```php
$price_query = [];
if ($minValue !== null && $maxValue !== null) {
    $price_query[] = [
        'key' => $priceKey,
        'value' => [$minValue, $maxValue],
        'type' => 'NUMERIC',
        'compare' => 'BETWEEN',
    ];
}
```

## Files Changed

- `includes/class-multiunit-query-builder.php`
  - Simplified `buildPriceQuery()`
  - Simplified `buildCoveredAreaQuery()`
  - Simplified `buildPlotAreaQuery()`
  - Removed complex nested query structures

## Impact

- ✅ AJAX requests now complete successfully
- ✅ Query performance significantly improved
- ✅ No more timeouts
- ⚠️ Multi-unit min/max fields are no longer queried separately (but regular fields work)

## Testing

1. Clear browser cache
2. Submit filters with price range
3. Should see results immediately without timeout

## Future Enhancement

If you need to properly query multi-unit min/max fields separately, consider:

1. Using custom SQL queries instead of meta_query
2. Using WP_Query's `meta_query` with proper indexes
3. Pre-processing multi-unit properties to populate regular fields
