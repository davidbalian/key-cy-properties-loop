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
    init: function () {},

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

      // Check if kcpfData is available
      if (typeof kcpfData === "undefined" || !kcpfData.ajaxUrl) {
        return;
      }

      const ajaxUrl =
        kcpfData.ajaxUrl + "?action=kcpf_load_properties&" + params.toString();
      const newUrl =
        window.location.pathname +
        (params.toString() ? "?" + params.toString() : "");

      $.ajax({
        url: ajaxUrl,
        type: "GET",
        dataType: "json",
        timeout: 60000, // 60 second timeout
        beforeSend: function () {
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
      if (response.success && response.data.html) {
        // Get purpose from params to find matching loop
        const purpose = params.get("purpose");

        // Find the matching loop by purpose
        let $targetLoop = null;
        if (purpose) {
          $targetLoop = $(
            '.kcpf-properties-loop[data-purpose="' + purpose + '"]'
          );
        }

        // Fallback to first loop if no purpose match found
        if (!$targetLoop || $targetLoop.length === 0) {
          $targetLoop = $(".kcpf-properties-loop").first();
        }

        // Replace the matching properties loop content
        const $newContent = $(response.data.html);
        $targetLoop.replaceWith($newContent);

        // Reset infinite scroll state
        window.kcpfLoadingNextPage = false;

        // Update URL without reload
        if (updateHistory) {
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
      } else {
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
      // Show error message to user
      if (status === "timeout") {
        $(".kcpf-properties-loop").html(
          '<div class="kcpf-error"><p>Request timed out. Please try again.</p></div>'
        );
      } else if (status === "error" && xhr.status === 0) {
        $(".kcpf-properties-loop").html(
          '<div class="kcpf-error"><p>Network error. Please check your connection and try again.</p></div>'
        );
      } else {
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
      const testUrl = kcpfData.ajaxUrl + "?action=kcpf_test";

      $.ajax({
        url: testUrl,
        type: "GET",
        dataType: "json",
        timeout: 10000,
        success: function (response) {},
        error: function (xhr, status, error) {},
      });
    },
  };
})(jQuery);
