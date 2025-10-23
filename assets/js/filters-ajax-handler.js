/**
 * Key CY Properties Filter - AJAX Handler
 * Handles AJAX requests and responses
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * AJAX Handler
   */
  window.KCPF_AjaxHandler = {
    /**
     * Initialize AJAX handling
     */
    init: function () {
      console.log("[KCPF] AJAX Handler initialized");
    },

    /**
     * Load properties via AJAX
     *
     * @param {URLSearchParams} params - URL parameters
     * @param {boolean} updateHistory - Whether to update browser history
     */
    loadProperties: function (params, updateHistory) {
      if (updateHistory === undefined) {
        updateHistory = true;
      }

      console.log("[KCPF] Loading filtered results...");

      // Check if kcpfData is available
      if (typeof kcpfData === "undefined" || !kcpfData.ajaxUrl) {
        console.error("[KCPF] kcpfData not found - AJAX URL not available");
        return;
      }

      const ajaxUrl =
        kcpfData.ajaxUrl + "?action=kcpf_load_properties&" + params.toString();
      const newUrl =
        window.location.pathname +
        (params.toString() ? "?" + params.toString() : "");

      console.log("[KCPF] === Starting AJAX Request ===");
      console.log("[KCPF] AJAX URL:", ajaxUrl);
      console.log("[KCPF] URL Parameters:", params.toString());
      console.log(
        "[KCPF] Parameter count:",
        params.toString().split("&").length
      );
      console.log("[KCPF] New URL:", newUrl);
      console.log("[KCPF] Request timestamp:", new Date().toISOString());
      
      // Log bedroom and bathroom parameters specifically
      console.log("[KCPF] Bedrooms parameter:", params.get('bedrooms'));
      console.log("[KCPF] Bathrooms parameter:", params.get('bathrooms'));
      console.log("[KCPF] All bedroom parameters:", params.getAll('bedrooms'));
      console.log("[KCPF] All bathroom parameters:", params.getAll('bathrooms'));
      
      // Log all parameters for debugging
      console.log("[KCPF] All parameters:");
      for (const [key, value] of params.entries()) {
        console.log(`[KCPF] ${key}:`, value);
      }

      $.ajax({
        url: ajaxUrl,
        type: "GET",
        dataType: "json",
        timeout: 60000, // 60 second timeout
        beforeSend: function () {
          console.log("[KCPF] Sending AJAX request...");
          // Get purpose from params to target correct loop
          const purpose = params.get("purpose");
          if (purpose) {
            $('.kcpf-properties-loop[data-purpose="' + purpose + '"]').addClass(
              "kcpf-loading"
            );
          } else {
            $(".kcpf-properties-loop").addClass("kcpf-loading");
          }
        },
        success: function (response) {
          KCPF_AjaxHandler.handleSuccess(
            response,
            params,
            newUrl,
            updateHistory
          );
        },
        complete: function () {
          console.log("[KCPF] AJAX request complete");
          // Get purpose from params to target correct loop
          const purpose = params.get("purpose");
          if (purpose) {
            $(
              '.kcpf-properties-loop[data-purpose="' + purpose + '"]'
            ).removeClass("kcpf-loading");
          } else {
            $(".kcpf-properties-loop").removeClass("kcpf-loading");
          }
        },
        error: function (xhr, status, error) {
          KCPF_AjaxHandler.handleError(xhr, status, error);
        },
      });
    },

    /**
     * Handle successful AJAX response
     */
    handleSuccess: function (response, params, newUrl, updateHistory) {
      console.log("[KCPF] AJAX response received:", response);
      console.log("[KCPF] Response timestamp:", new Date().toISOString());

      if (response.success && response.data.html) {
        // Get purpose from params to find matching loop
        const purpose = params.get("purpose");

        // Find the matching loop by purpose
        let $targetLoop = null;
        if (purpose) {
          $targetLoop = $(
            '.kcpf-properties-loop[data-purpose="' + purpose + '"]'
          );
          console.log("[KCPF] Found matching loop for purpose:", purpose);
        }

        // Fallback to first loop if no purpose match found
        if (!$targetLoop || $targetLoop.length === 0) {
          $targetLoop = $(".kcpf-properties-loop").first();
          console.log("[KCPF] Using first loop as fallback");
        }

        // Replace the matching properties loop content
        const $newContent = $(response.data.html);
        $targetLoop.replaceWith($newContent);

        // Reset infinite scroll state
        window.kcpfLoadingNextPage = false;

        // Update URL without reload
        if (updateHistory) {
          console.log("[KCPF] Updating URL to:", newUrl);
          history.pushState({ kcpfFilters: true }, "", newUrl);
        }

        // Scroll to results if exists
        const $loop = $(".kcpf-properties-loop");
        if ($loop.length > 0) {
          $("html, body").animate(
            {
              scrollTop: $loop.offset().top - 100,
            },
            400
          );
        }

        console.log("[KCPF] Results updated successfully");
      } else {
        console.error("[KCPF] Invalid response format:", response);
        const $loop = $(".kcpf-properties-loop").first();
        if ($loop.length > 0) {
          $loop.html(
            '<div class="kcpf-error"><p>Invalid response from server</p></div>'
          );
        }
      }
    },

    /**
     * Handle AJAX error
     */
    handleError: function (xhr, status, error) {
      console.error("[KCPF] ============ AJAX ERROR ============");
      console.error("[KCPF] Status:", status);
      console.error("[KCPF] Error:", error);
      console.error("[KCPF] XHR Status:", xhr.status);
      console.error("[KCPF] Response Headers:", xhr.getAllResponseHeaders());
      console.error("[KCPF] Response Text:", xhr.responseText);
      console.error("[KCPF] Ready State:", xhr.readyState);
      console.error("[KCPF] Error timestamp:", new Date().toISOString());
      console.error("[KCPF] Full XHR object:", xhr);

      // Show error message to user
      if (status === "timeout") {
        console.error("[KCPF] Request timed out after 60 seconds");
        $(".kcpf-properties-loop").html(
          '<div class="kcpf-error"><p>Request timed out. Please try again.</p></div>'
        );
      } else if (status === "error" && xhr.status === 0) {
        console.error("[KCPF] Network error - request may have been blocked");
        $(".kcpf-properties-loop").html(
          '<div class="kcpf-error"><p>Network error. Please check your connection and try again.</p></div>'
        );
      } else {
        console.error("[KCPF] Server error - HTTP Status:", xhr.status);
        $(".kcpf-properties-loop").html(
          '<div class="kcpf-error"><p>An error occurred while loading properties (HTTP ' +
            xhr.status +
            "). Please try again.</p></div>"
        );
      }
    },

    /**
     * Test AJAX endpoint
     */
    testEndpoint: function () {
      console.log("[KCPF] Testing AJAX endpoint...");
      const testUrl = kcpfData.ajaxUrl + "?action=kcpf_test";

      $.ajax({
        url: testUrl,
        type: "GET",
        dataType: "json",
        timeout: 10000,
        success: function (response) {
          console.log("[KCPF] AJAX test SUCCESS:", response);
        },
        error: function (xhr, status, error) {
          console.error("[KCPF] AJAX test FAILED:", status, error);
          console.error("[KCPF] XHR Status:", xhr.status);
          console.error("[KCPF] Response:", xhr.responseText);
        },
      });
    },
  };
})(jQuery);
