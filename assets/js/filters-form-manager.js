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
      this.initAccordion();
    },

    /**
     * Wrap all filter shortcodes in a form
     */
    wrapFiltersInForm: function () {
      const filters = $(".kcpf-filter");

      if (filters.length === 0) {
        return;
      }

      // Check if already wrapped
      if (filters.first().closest("form").length > 0) {
        return;
      }

      // Wrap all filters together in a form
      if (filters.length > 0) {
        filters.wrapAll('<form class="kcpf-filters-form" method="get"></form>');
      }
    },

    /**
     * Handle form submission
     */
    handleFormSubmission: function () {
      // Check if debug mode is enabled before binding AJAX
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get("debug_filters") === "1") {
        return; // Don't bind AJAX handlers in debug mode
      }

      // Always use AJAX for form submission (except in debug mode)
      $(document).on("submit", ".kcpf-filters-form", function (e) {
        e.preventDefault();
        KCPF_FormManager.processFormSubmission($(this));
      });
    },

    /**
     * Handle apply button clicks
     */
    handleApplyButton: function () {
      // Check if debug mode is enabled before binding
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get("debug_filters") === "1") {
        return; // Don't bind AJAX handlers in debug mode
      }

      $(document).on("click", ".kcpf-apply-button", function (e) {
        e.preventDefault();

        const form = $(this).closest("form");

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
          if ($form.length > 0) {
            $form.submit();
          } else {
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
      window.location.href = url;
    },

    /**
     * Handle reset button clicks
     */
    handleResetButton: function () {
      $(document).on("click", ".kcpf-reset-button", function (e) {
        e.preventDefault();

        // Clear all form inputs
        const $form = $(this).closest("form");
        if ($form.length === 0) {
          // Fallback: find any form on page
          $form = $(".kcpf-filters-form").first();
        }

        if ($form.length > 0) {
          // Clear all inputs
          $form
            .find("input[type='checkbox'], input[type='radio']")
            .prop("checked", false);
          $form
            .find("input[type='text'], input[type='number'], select")
            .val("");
          $form.find("input[type='hidden']").val("");

          // Clear multiselect dropdowns
          $form.find(".kcpf-multiselect-selected").each(function () {
            const $selected = $(this);
            const filterName = $selected
              .closest(".kcpf-multiselect-dropdown")
              .data("filter-name");
            // Convert underscores to spaces and capitalize each word
            const displayName = filterName
              .replace(/_/g, " ")
              .split(" ")
              .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
              .join(" ");
            const placeholder = displayName;
            $selected.html(
              '<span class="kcpf-placeholder">' + placeholder + "</span>"
            );
          });

          // Clear range sliders (if any)
          if (window.KCPF_RangeSliders && window.KCPF_RangeSliders.reset) {
            window.KCPF_RangeSliders.reset();
          }
        }

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
      // Initial URL sync
      KCPF_FormManager.syncFormWithURL();

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
          // Sync form with URL parameters
          KCPF_FormManager.syncFormWithURL();
          // Use AJAX handler to load properties
          if (window.KCPF_AjaxHandler) {
            KCPF_AjaxHandler.loadProperties(params, false);
          }
        }
      });
    },

    /**
     * Sync form values with URL parameters
     */
    syncFormWithURL: function () {
      const params = new URLSearchParams(window.location.search);
      const $form = $(".kcpf-filters-form").first();

      if ($form.length === 0) {
        return;
      }

      // Clear existing selections first
      $form
        .find("input[type='checkbox'], input[type='radio']")
        .prop("checked", false);
      $form.find("select").val("");

      // Handle each parameter
      for (const [key, value] of params.entries()) {
        // Handle comma-separated values (bedrooms, bathrooms)
        if (
          (key === "bedrooms" || key === "bathrooms") &&
          value.includes(",")
        ) {
          const values = value.split(",");
          values.forEach((val) => {
            $form
              .find(`input[name="${key}[]"][value="${val}"]`)
              .prop("checked", true);
            $form.find(`select[name="${key}[]"]`).val(val);
          });
        }
        // Handle array parameters (key[])
        else if (key.endsWith("[]")) {
          const baseKey = key.slice(0, -2);
          $form
            .find(`input[name="${key}"][value="${value}"]`)
            .prop("checked", true);
          $form.find(`select[name="${key}"]`).val(value);
        }
        // Handle single values
        else {
          $form
            .find(`input[name="${key}"][value="${value}"]`)
            .prop("checked", true);
          $form.find(`select[name="${key}"]`).val(value);
        }
      }
    },

    /**
     * Process form submission
     */
    processFormSubmission: function (form) {
      // Check if debug mode is enabled - if so, don't use AJAX
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get("debug_filters") === "1") {
        return true; // Allow normal form submission for debugging
      }

      // Group form values by field name
      const formData = {};
      form.serializeArray().forEach(function (item) {
        if (item.value !== "" && item.value !== null) {
          // Remove [] from name if present
          const cleanName = item.name.replace(/\[\]$/, "");

          if (formData.hasOwnProperty(cleanName)) {
            if (!Array.isArray(formData[cleanName])) {
              formData[cleanName] = [formData[cleanName]];
            }
            formData[cleanName].push(item.value);
          } else {
            formData[cleanName] = item.value;
          }
        }
      });

      const params = new URLSearchParams();
      Object.keys(formData).forEach(function (key) {
        const value = formData[key];
        const cleanKey = key.replace(/\[\]$/, "");

        if (Array.isArray(value)) {
          // Use comma-separated format for bedrooms and bathrooms
          if (cleanKey === "bedrooms" || cleanKey === "bathrooms") {
            params.set(cleanKey, value.join(","));
          } else {
            // For other fields, use array format
            value.forEach((v) => params.append(cleanKey + "[]", v));
          }
        } else {
          // Single value
          params.set(cleanKey, value);
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
      }

      // Use AJAX handler to load properties
      if (window.KCPF_AjaxHandler) {
        KCPF_AjaxHandler.loadProperties(params);
      }
    },

    /**
     * Initialize accordion functionality
     */
    initAccordion: function () {
      const $accordion = $(".kcpf-accordion");
      if ($accordion.length === 0) {
        return;
      }

      // Set initial state based on screen size
      this.setInitialAccordionState($accordion);

      // Handle accordion toggle
      $accordion.on("click", ".kcpf-accordion-header", function () {
        const $thisAccordion = $(this).closest(".kcpf-accordion");
        KCPF_FormManager.toggleAccordion($thisAccordion);
      });

      // Handle window resize to update accordion state
      $(window).on("resize", function () {
        KCPF_FormManager.updateAccordionState($accordion);
      });
    },

    /**
     * Set initial accordion state based on screen size
     */
    setInitialAccordionState: function ($accordion) {
      const screenWidth = window.innerWidth;

      if (screenWidth >= 1024) {
        // Expanded by default on large screens
        $accordion.addClass("expanded");
      } else {
        // Collapsed by default on small screens
        $accordion.removeClass("expanded");
      }
    },

    /**
     * Toggle accordion state
     */
    toggleAccordion: function ($accordion) {
      if ($accordion.hasClass("expanded")) {
        $accordion.removeClass("expanded");
      } else {
        $accordion.addClass("expanded");
      }
    },

    /**
     * Update accordion state on window resize
     */
    updateAccordionState: function ($accordion) {
      const screenWidth = window.innerWidth;

      if (screenWidth >= 1024) {
        // Ensure expanded on large screens
        $accordion.addClass("expanded");
      } else {
        // Ensure collapsed on small screens
        $accordion.removeClass("expanded");
      }
    },
  };
})(jQuery);
