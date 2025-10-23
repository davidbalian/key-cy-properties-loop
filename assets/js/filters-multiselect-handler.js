/**
 * Key CY Properties Filter - Multiselect Handler
 * Handles multiselect dropdown interactions
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Multiselect Handler
   */
  window.KCPF_MultiselectHandler = {
    /**
     * Initialize multiselect dropdowns
     */
    init: function () {
      console.log("[KCPF] Multiselect Handler starting initialization...");
      this.handleDropdownToggle();
      this.handleRangeDropdownToggle();
      this.handleOutsideClick();
      this.handleChipRemoval();
      this.handleCheckboxChange();
      console.log("[KCPF] Multiselect Handler initialized successfully");
    },

    /**
     * Handle dropdown toggle
     */
    handleDropdownToggle: function () {
      $(document).on("click", ".kcpf-multiselect-trigger", function (e) {
        e.stopPropagation();
        console.log("[KCPF] Dropdown trigger clicked");
        const $dropdown = $(this).closest(".kcpf-multiselect-dropdown");
        const isActive = $dropdown.hasClass("active");

        // Close all dropdowns
        $(".kcpf-multiselect-dropdown").removeClass("active");
        $(".kcpf-range-dropdown").removeClass("active");

        // Toggle current dropdown
        if (!isActive) {
          $dropdown.addClass("active");
          console.log(
            "[KCPF] Dropdown opened for:",
            $dropdown.data("filter-name")
          );
        } else {
          console.log("[KCPF] Dropdown closed");
        }
      });
    },

    /**
     * Handle range dropdown toggle
     */
    handleRangeDropdownToggle: function () {
      $(document).on("click", ".kcpf-range-trigger", function (e) {
        e.stopPropagation();
        console.log("[KCPF] Range dropdown trigger clicked");
        const $dropdown = $(this).closest(".kcpf-range-dropdown");
        const isActive = $dropdown.hasClass("active");

        // Close all dropdowns
        $(".kcpf-multiselect-dropdown").removeClass("active");
        $(".kcpf-range-dropdown").removeClass("active");

        // Toggle current dropdown
        if (!isActive) {
          $dropdown.addClass("active");
          console.log("[KCPF] Range dropdown opened");
        } else {
          console.log("[KCPF] Range dropdown closed");
        }
      });
    },

    /**
     * Close dropdowns when clicking outside
     */
    handleOutsideClick: function () {
      $(document).on("click", function (e) {
        if (!$(e.target).closest(".kcpf-multiselect-dropdown").length) {
          $(".kcpf-multiselect-dropdown").removeClass("active");
        }
        if (!$(e.target).closest(".kcpf-range-dropdown").length) {
          $(".kcpf-range-dropdown").removeClass("active");
        }
      });
    },

    /**
     * Handle chip removal
     */
    handleChipRemoval: function () {
      $(document).on("click", ".kcpf-chip-remove", function (e) {
        e.stopPropagation();
        e.preventDefault();
        console.log("[KCPF] Chip remove clicked");
        const $chip = $(this).closest(".kcpf-chip");
        const value = $(this).data("value");
        const $dropdown = $(this).closest(".kcpf-multiselect-dropdown");
        const filterName = $dropdown.data("filter-name");

        // Uncheck the checkbox
        $dropdown
          .find('input[type="checkbox"][value="' + value + '"]')
          .prop("checked", false);

        // Remove the chip
        $chip.remove();

        // Show placeholder if no chips remain
        const $selected = $dropdown.find(".kcpf-multiselect-selected");
        if ($selected.find(".kcpf-chip").length === 0) {
          const placeholder =
            "Select " +
            filterName.charAt(0).toUpperCase() +
            filterName.slice(1);
          $selected.html(
            '<span class="kcpf-placeholder">' + placeholder + "</span>"
          );
        }
      });
    },

    /**
     * Handle checkbox changes
     */
    handleCheckboxChange: function () {
      $(document).on(
        "change",
        ".kcpf-multiselect-option input[type='checkbox']",
        function () {
          console.log("[KCPF] Checkbox changed");
          const $dropdown = $(this).closest(".kcpf-multiselect-dropdown");
          const filterName = $dropdown.data("filter-name");
          const $selected = $dropdown.find(".kcpf-multiselect-selected");
          const checkedValues = [];

          // Get all checked values
          $dropdown.find('input[type="checkbox"]:checked').each(function () {
            checkedValues.push($(this).val());
          });

          // Update selected chips
          if (checkedValues.length === 0) {
            const placeholder =
              "Select " +
              filterName.charAt(0).toUpperCase() +
              filterName.slice(1);
            $selected.html(
              '<span class="kcpf-placeholder">' + placeholder + "</span>"
            );
          } else {
            let chipsHtml = "";
            $dropdown.find(".kcpf-multiselect-option").each(function () {
              const $checkbox = $(this).find('input[type="checkbox"]');
              const value = $checkbox.val();
              // Get label without the count span
              const label = $(this).find("span").not(".kcpf-count").text();

              if ($checkbox.is(":checked")) {
                chipsHtml +=
                  '<span class="kcpf-chip">' +
                  label +
                  '<span class="kcpf-chip-remove" data-value="' +
                  value +
                  '">&times;</span></span>';
              }
            });
            $selected.html(chipsHtml);
          }
        }
      );
    },
  };
})(jQuery);
