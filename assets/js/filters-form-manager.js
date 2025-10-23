/**
 * Key CY Properties Filter - Form Manager
 * Handles form wrapping and submission
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Form Manager
   */
  window.KCPF_FormManager = {
    /**
     * Initialize form management
     */
    init: function () {
      this.wrapFiltersInForm();
      this.handleFormSubmission();
      this.handleResetButton();
      this.handleApplyButton();
      this.handleBrowserNavigation();
    },

    /**
     * Wrap all filter shortcodes in a form
     */
    wrapFiltersInForm: function () {
      const filters = $(".kcpf-filter");

      console.log("[KCPF] Found " + filters.length + " filter elements");

      if (filters.length === 0) {
        console.warn("[KCPF] No filter elements found");
        return;
      }

      // Check if already wrapped
      if (filters.first().closest("form").length > 0) {
        console.log("[KCPF] Filters already wrapped in form");
        return;
      }

      // Wrap all filters together in a form
      console.log("[KCPF] Wrapping " + filters.length + " filters in form");

      if (filters.length > 0) {
        filters.wrapAll('<form class="kcpf-filters-form" method="get"></form>');
        console.log("[KCPF] Filters wrapped successfully");
      }
    },

    /**
     * Handle form submission
     */
    handleFormSubmission: function () {
      // Always use AJAX for form submission
      $(document).on("submit", ".kcpf-filters-form", function (e) {
        e.preventDefault();
        console.log("[KCPF] Form submit event triggered");
        KCPF_FormManager.processFormSubmission($(this));
      });
    },

    /**
     * Handle apply button clicks
     */
    handleApplyButton: function () {
      $(document).on("click", ".kcpf-apply-button", function (e) {
        e.preventDefault();
        console.log("[KCPF] Apply button clicked");

        const form = $(this).closest("form");
        console.log("[KCPF] Form found:", form.length);

        // Homepage composite redirect behavior
        const $homepage = $(this).closest(".kcpf-homepage-filters");
        const submitType = $(this).data("type");
        if ($homepage.length > 0 && submitType === "redirect") {
          KCPF_FormManager.handleHomepageRedirect(form, $homepage, $(this));
          return;
        }

        if (form.length > 0) {
          form.submit();
        } else {
          // Fallback: find form on page
          const $form = $(".kcpf-filters-form").first();
          console.log("[KCPF] Fallback form found:", $form.length);
          if ($form.length > 0) {
            $form.submit();
          } else {
            console.error("[KCPF] No form found - cannot submit filters");
          }
        }
      });
    },

    /**
     * Handle homepage redirect (composite filters)
     */
    handleHomepageRedirect: function (form, $homepage, $button) {
      const params = new URLSearchParams();
      const data = {};
      (form.length ? form : $(".kcpf-filters-form").first())
        .serializeArray()
        .forEach(function (item) {
          if (item.value !== "" && item.value !== null) {
            if (data.hasOwnProperty(item.name)) {
              if (!Array.isArray(data[item.name])) {
                data[item.name] = [data[item.name]];
              }
              data[item.name].push(item.value);
            } else {
              data[item.name] = item.value;
            }
          }
        });

      Object.keys(data).forEach(function (key) {
        const value = data[key];
        if (Array.isArray(value)) {
          const cleanKey = key.replace(/\[\]$/, "");
          if (value.length === 1) {
            params.set(cleanKey, value[0]);
          } else {
            value.forEach(function (v) {
              params.append(cleanKey + "[]", v);
            });
          }
        } else {
          params.set(key, value);
        }
      });

      // Ensure purpose present
      let purpose = params.get("purpose");
      if (!purpose) {
        const $purposeInput = $homepage.find('input[name="purpose"]:checked');
        if ($purposeInput.length) {
          purpose = $purposeInput.val();
          params.set("purpose", purpose);
        }
      }

      const saleUrl =
        $button.data("sale-url") ||
        $homepage.data("sale-url") ||
        "/test-sale-archive";
      const rentUrl =
        $button.data("rent-url") ||
        $homepage.data("rent-url") ||
        "/test-rent-page";
      const target = purpose === "rent" ? rentUrl : saleUrl;
      const url = target + (params.toString() ? "?" + params.toString() : "");
      console.log("[KCPF] Redirecting to:", url);
      window.location.href = url;
    },

    /**
     * Handle reset button clicks
     */
    handleResetButton: function () {
      $(document).on("click", ".kcpf-reset-button", function (e) {
        e.preventDefault();
        console.log("[KCPF] Reset button clicked");

        // Clear URL and reload properties
        const params = new URLSearchParams();

        // Keep purpose parameter if it exists
        const currentPurpose = new URLSearchParams(window.location.search).get(
          "purpose"
        );
        if (currentPurpose) {
          params.set("purpose", currentPurpose);
        }

        // Update URL and load properties
        const newUrl =
          window.location.pathname +
          (params.toString() ? "?" + params.toString() : "");
        window.history.pushState({ kcpfFilters: true }, "", newUrl);

        // Use AJAX handler to load properties
        if (window.KCPF_AjaxHandler) {
          KCPF_AjaxHandler.loadProperties(params, false);
        }
      });
    },

    /**
     * Handle browser back/forward buttons
     */
    handleBrowserNavigation: function () {
      window.addEventListener("popstate", function (e) {
        if (e.state && e.state.kcpfFilters) {
          const params = new URLSearchParams(window.location.search);
          // Detect purpose from URL params or current page
          if (!params.get("purpose")) {
            const $loop = $(".kcpf-properties-loop").first();
            if ($loop.length > 0) {
              const purpose = $loop.data("purpose");
              if (purpose) {
                params.set("purpose", purpose);
              }
            }
          }
          // Use AJAX handler to load properties
          if (window.KCPF_AjaxHandler) {
            KCPF_AjaxHandler.loadProperties(params, false);
          }
        }
      });
    },

    /**
     * Process form submission
     */
    processFormSubmission: function (form) {
      console.log("[KCPF] Form submitted, processing...");

      // Clean up empty values
      const formData = {};
      form.serializeArray().forEach(function (item) {
        if (item.value !== "" && item.value !== null) {
          // Check if this field name already exists (means multiple values)
          if (formData.hasOwnProperty(item.name)) {
            // Convert to array if not already
            if (!Array.isArray(formData[item.name])) {
              formData[item.name] = [formData[item.name]];
            }
            formData[item.name].push(item.value);
          } else {
            // First occurrence - store as single value
            formData[item.name] = item.value;
          }
        }
      });

      console.log("[KCPF] Form data:", formData);

      const params = new URLSearchParams();
      Object.keys(formData).forEach(function (key) {
        const value = formData[key];

        if (Array.isArray(value)) {
          // Remove [] from key name if present
          const cleanKey = key.replace(/\[\]$/, "");

          if (value.length === 1) {
            // Single value - use simple parameter
            params.set(cleanKey, value[0]);
          } else {
            // Multiple values - append each value without [] brackets
            // PHP automatically converts these to an array
            value.forEach(function (v) {
              params.append(cleanKey, v);
            });
          }
        } else {
          // Single value
          params.set(key, value);
        }
      });

      // Get purpose from form or find closest loop
      let purpose = params.get("purpose");
      if (!purpose) {
        // Try to find the purpose from the closest loop
        const $closestLoop = form.closest(".kcpf-properties-loop");
        if ($closestLoop.length > 0) {
          purpose = $closestLoop.data("purpose");
        } else {
          // Fallback: find first loop on page
          const $firstLoop = $(".kcpf-properties-loop").first();
          if ($firstLoop.length > 0) {
            purpose = $firstLoop.data("purpose");
          }
        }
      }

      // Add purpose to params if found
      if (purpose) {
        params.set("purpose", purpose);
        console.log("[KCPF] Detected purpose:", purpose);
      }

      console.log("[KCPF] URL params:", params.toString());

      // Use AJAX handler to load properties
      if (window.KCPF_AjaxHandler) {
        KCPF_AjaxHandler.loadProperties(params);
      }
    },
  };
})(jQuery);
