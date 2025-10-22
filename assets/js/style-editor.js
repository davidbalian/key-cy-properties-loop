/**
 * Style Editor Admin JavaScript
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  $(document).ready(function () {
    const $form = $("#kcpf-style-form");
    const $inputs = $(".kcpf-style-input");

    // Initialize range slider in preview
    initializePreviewSlider();

    // Initialize multi-select dropdowns
    initializeMultiSelectDropdowns();

    // Live preview updates
    $inputs.on("input", function () {
      updatePreview();
    });

    // Initialize preview on load
    updatePreview();
  });

  /**
   * Initialize preview slider
   */
  function initializePreviewSlider() {
    const sliderElement = document.querySelector(
      ".kcpf-style-preview .kcpf-range-slider"
    );

    if (!sliderElement) {
      return;
    }

    // Check if noUiSlider is available
    if (typeof noUiSlider === "undefined") {
      return;
    }

    // Destroy existing slider if any
    if (sliderElement.noUiSlider) {
      sliderElement.noUiSlider.destroy();
    }

    noUiSlider.create(sliderElement, {
      start: [100000, 500000],
      connect: true,
      range: {
        min: 0,
        max: 1000000,
      },
      step: 10000,
      format: {
        to: function (value) {
          return Math.round(value).toLocaleString();
        },
        from: function (value) {
          return Number(value);
        },
      },
    });
  }

  /**
   * Initialize multi-select dropdowns for preview
   */
  function initializeMultiSelectDropdowns() {
    // Handle dropdown trigger clicks
    $(document).on(
      "click",
      ".kcpf-style-preview .kcpf-multiselect-trigger",
      function (e) {
        e.stopPropagation();
        const $dropdown = $(this).closest(".kcpf-multiselect-dropdown");
        const $menu = $dropdown.find(".kcpf-multiselect-dropdown-menu");

        // Toggle active state
        $dropdown.toggleClass("active");

        // Close other dropdowns
        $(".kcpf-style-preview .kcpf-multiselect-dropdown")
          .not($dropdown)
          .removeClass("active");
      }
    );

    // Close dropdowns when clicking outside
    $(document).on("click", function (e) {
      if (!$(e.target).closest(".kcpf-multiselect-dropdown").length) {
        $(".kcpf-style-preview .kcpf-multiselect-dropdown").removeClass(
          "active"
        );
      }
    });

    // Handle checkbox changes
    $(document).on(
      "change",
      ".kcpf-style-preview .kcpf-multiselect-option input[type='checkbox']",
      function () {
        // Simulate chip behavior for preview
        const $option = $(this).closest(".kcpf-multiselect-option");
        const text = $option.find("span").text();

        // This is just for preview - don't need to actually manage chips
      }
    );
  }

  /**
   * Update preview with current settings
   */
  function updatePreview() {
    const settings = {};

    $(".kcpf-field-group").each(function () {
      const $group = $(this);
      const sectionName = $group
        .closest(".kcpf-editor-section")
        .find("h2")
        .text()
        .toLowerCase()
        .replace(/\s+/g, "_");

      settings[sectionName] = {};

      $group.find(".kcpf-style-input").each(function () {
        const $input = $(this);
        const key = $input.attr("name").match(/\[([^\]]+)\]$/)[1];
        const value = $input.val();

        if (value) {
          settings[sectionName][key] = value;
        }
      });
    });

    // Generate and apply CSS
    const css = generateCSS(settings);
    applyPreviewCSS(css);
  }

  /**
   * Generate CSS from settings
   */
  function generateCSS(settings) {
    let css = "";

    // Add high specificity wrapper
    css += ".kcpf-style-preview {\n}\n\n";

    // Filter Container
    css += generateSectionCSS(
      ".kcpf-style-preview .kcpf-filter",
      settings.filter_container
    );

    // Filter Label
    css += generateSectionCSS(
      ".kcpf-style-preview .kcpf-filter label",
      settings.filter_label
    );

    // Select
    css += generateSectionCSS(
      ".kcpf-style-preview .kcpf-filter-select",
      settings.select
    );
    css += generateFocusCSS(
      ".kcpf-style-preview .kcpf-filter-select:focus",
      settings.select
    );

    // Multi-select Trigger
    css += generateSectionCSS(
      ".kcpf-style-preview .kcpf-multiselect-trigger",
      settings.multiselect_trigger
    );
    css += generateHoverCSS(
      ".kcpf-style-preview .kcpf-multiselect-trigger:hover",
      settings.multiselect_trigger
    );

    // Multi-select Chip
    css += generateSectionCSS(
      ".kcpf-style-preview .kcpf-chip",
      settings.multiselect_chip
    );

    // Multi-select Dropdown
    css += generateSectionCSS(
      ".kcpf-style-preview .kcpf-multiselect-dropdown-menu",
      settings.multiselect_dropdown
    );

    // Multi-select Option
    css += generateSectionCSS(
      ".kcpf-style-preview .kcpf-multiselect-option",
      settings.multiselect_option
    );
    css += generateHoverCSS(
      ".kcpf-style-preview .kcpf-multiselect-option:hover",
      settings.multiselect_option
    );

    // Input
    css += generateSectionCSS(
      ".kcpf-style-preview .kcpf-input",
      settings.input
    );
    css += generateFocusCSS(
      ".kcpf-style-preview .kcpf-input:focus",
      settings.input
    );

    // Apply Button
    css += generateSectionCSS(
      ".kcpf-style-preview .kcpf-apply-button",
      settings.apply_button
    );
    css += generateHoverCSS(
      ".kcpf-style-preview .kcpf-apply-button:hover",
      settings.apply_button
    );

    // Reset Button
    css += generateSectionCSS(
      ".kcpf-style-preview .kcpf-reset-button",
      settings.reset_button
    );
    css += generateHoverCSS(
      ".kcpf-style-preview .kcpf-reset-button:hover",
      settings.reset_button
    );

    // Toggle Buttons
    css += generateSectionCSS(
      ".kcpf-style-preview .kcpf-toggle-label span, .kcpf-style-preview .kcpf-radio-label span, .kcpf-style-preview .kcpf-button-label span",
      settings.toggle_button
    );
    css += generateActiveCSS(
      ".kcpf-style-preview .kcpf-toggle-label.active span, .kcpf-style-preview .kcpf-radio-label input:checked + span",
      settings.toggle_button
    );

    // Range Slider
    css += generateSliderCSS(settings.range_slider);

    return css;
  }

  /**
   * Generate section CSS
   */
  function generateSectionCSS(selector, settings) {
    if (!settings) return "";

    let css = selector + " {";

    for (let key in settings) {
      if (!settings[key]) continue;

      const property = convertKeyToProperty(key);
      if (property) {
        css += "\n    " + property + ": " + settings[key] + " !important;";
      }
    }

    css += "\n}\n\n";
    return css;
  }

  /**
   * Generate hover CSS
   */
  function generateHoverCSS(selector, settings) {
    if (!settings) return "";

    let css = "";

    if (settings.background_color_hover) {
      css +=
        selector +
        " { background-color: " +
        settings.background_color_hover +
        " !important; }\n";
    }

    if (settings.border_color_hover) {
      css +=
        selector +
        " { border-color: " +
        settings.border_color_hover +
        " !important; }\n";
    }

    return css ? css + "\n" : "";
  }

  /**
   * Generate active CSS
   */
  function generateActiveCSS(selector, settings) {
    if (!settings) return "";

    let css = "";

    if (settings.background_color_active) {
      css +=
        selector +
        " { background-color: " +
        settings.background_color_active +
        " !important; }\n";
    }

    if (settings.color_active) {
      css +=
        selector + " { color: " + settings.color_active + " !important; }\n";
    }

    if (settings.border_color_active) {
      css +=
        selector +
        " { border-color: " +
        settings.border_color_active +
        " !important; }\n";
    }

    return css ? css + "\n" : "";
  }

  /**
   * Generate focus CSS
   */
  function generateFocusCSS(selector, settings) {
    if (!settings) return "";

    let css = "";

    if (settings.border_color_focus) {
      css +=
        selector +
        " { border-color: " +
        settings.border_color_focus +
        " !important; }\n";
    }

    return css ? css + "\n" : "";
  }

  /**
   * Generate slider CSS
   */
  function generateSliderCSS(settings) {
    if (!settings) return "";

    let css = "";

    if (settings.height) {
      css +=
        ".kcpf-style-preview .kcpf-range-slider { height: " +
        settings.height +
        " !important; }\n";
    }

    if (settings.connect_color) {
      css +=
        ".kcpf-style-preview .kcpf-range-slider .noUi-connect { background: " +
        settings.connect_color +
        " !important; }\n";
    }

    if (settings.handle_border) {
      css +=
        ".kcpf-style-preview .kcpf-range-slider .noUi-handle { border: " +
        settings.handle_border +
        " !important; }\n";
    }

    if (settings.handle_background) {
      css +=
        ".kcpf-style-preview .kcpf-range-slider .noUi-handle { background: " +
        settings.handle_background +
        " !important; }\n";
    }

    if (settings.handle_box_shadow) {
      css +=
        ".kcpf-style-preview .kcpf-range-slider .noUi-handle { box-shadow: " +
        settings.handle_box_shadow +
        " !important; }\n";
    }

    return css + "\n";
  }

  /**
   * Convert key to CSS property
   */
  function convertKeyToProperty(key) {
    const mapping = {
      margin_bottom: "margin-bottom",
      padding: "padding",
      font_size: "font-size",
      font_weight: "font-weight",
      color: "color",
      width: "width",
      border: "border",
      border_radius: "border-radius",
      background_color: "background-color",
      border_color: "border-color",
      max_height: "max-height",
      box_shadow: "box-shadow",
      cursor: "cursor",
    };

    return mapping[key] || null;
  }

  /**
   * Apply preview CSS
   */
  function applyPreviewCSS(css) {
    let $style = $("#kcpf-preview-style");

    if ($style.length === 0) {
      $style = $('<style id="kcpf-preview-style"></style>');
      $("head").append($style);
    }

    $style.text(css);

    // Force browser to recalculate styles
    const $preview = $(".kcpf-style-preview");
    if ($preview.length) {
      $preview[0].offsetHeight; // Force reflow
    }
  }
})(jQuery);
