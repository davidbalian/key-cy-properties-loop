/**
 * Key CY Properties Filter - Homepage Manager
 * Handles homepage-specific filter behavior
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Homepage Manager
   */
  window.KCPF_HomepageManager = {
    /**
     * Initialize homepage filters
     */
    init: function () {
      this.initializeHomepage();
      console.log("[KCPF] Homepage Manager initialized");
    },

    /**
     * Initialize homepage filters on page load
     */
    initializeHomepage: function () {
      const $homepage = $(".kcpf-homepage-filters");
      if ($homepage.length === 0) {
        return;
      }

      // Always default to sale on homepage, clear URL if present
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.toString()) {
        // Clear URL parameters without reload
        window.history.replaceState(
          {},
          document.title,
          window.location.pathname
        );
        console.log("[KCPF] Homepage filters: cleared URL parameters");
      }

      // Ensure 'sale' is selected
      $homepage
        .find('input[name="purpose"][value="sale"]')
        .prop("checked", true);
    },

    /**
     * Refresh homepage filters for purpose change
     *
     * @param {jQuery} $root - Homepage filters root element
     * @param {string} purpose - Selected purpose (sale/rent)
     */
    refreshFilters: function ($root, purpose) {
      const params = new URLSearchParams();
      params.set("purpose", purpose);

      if (typeof kcpfData === "undefined" || !kcpfData.ajaxUrl) {
        console.error("[KCPF] Missing ajaxUrl for refresh");
        return;
      }

      const ajaxUrl =
        kcpfData.ajaxUrl + "?action=kcpf_refresh_filters&" + params.toString();
      console.log("[KCPF] Refreshing filters for purpose:", purpose, ajaxUrl);

      // Close any open dropdowns before refresh
      $root.find(".kcpf-multiselect-dropdown").removeClass("active");
      $root.find(".kcpf-range-dropdown").removeClass("active");

      // Show spinner
      $root.find(".kcpf-refresh-spinner").show();

      $.ajax({
        url: ajaxUrl,
        type: "GET",
        dataType: "json",
        timeout: 20000,
        beforeSend: function () {
          // Add loading state
          $root.addClass("kcpf-refreshing");
        },
        success: function (response) {
          KCPF_HomepageManager.handleRefreshSuccess(response, $root, purpose);
        },
        error: function (xhr, status, error) {
          console.error("[KCPF] Refresh failed", status, error);
        },
        complete: function () {
          // Remove loading state and hide spinner
          $root.removeClass("kcpf-refreshing");
          $root.find(".kcpf-refresh-spinner").hide();
        },
      });
    },

    /**
     * Handle successful filter refresh
     */
    handleRefreshSuccess: function (response, $root, purpose) {
      if (response && response.success && response.data && response.data.html) {
        // Destroy existing sliders before replacing HTML
        if (window.KCPF_RangeSliders) {
          KCPF_RangeSliders.destroySliders($root);
        }

        // Replace fragments inside the homepage block
        if (response.data.html.type) {
          $root.find(".kcpf-filter-type").replaceWith(response.data.html.type);
        }
        if (response.data.html.location) {
          $root
            .find(".kcpf-filter-location")
            .replaceWith(response.data.html.location);
        }
        if (response.data.html.bedrooms) {
          $root
            .find(".kcpf-filter-bedrooms")
            .replaceWith(response.data.html.bedrooms);
        }
        if (response.data.html.price) {
          $root
            .find(".kcpf-filter-price")
            .replaceWith(response.data.html.price);
        }

        // Re-init sliders with new data
        if (window.KCPF_RangeSliders) {
          KCPF_RangeSliders.init();
        }

        // Update data attribute for future checks
        $root.attr("data-current-purpose", purpose);

        console.log("[KCPF] Filters refreshed for purpose:", purpose);
        console.log("[KCPF] New price range:", response.data.priceRange);
      } else {
        console.error("[KCPF] Invalid refresh response", response);
      }
    },
  };
})(jQuery);
