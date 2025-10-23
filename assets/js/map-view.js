/**
 * Map View JavaScript
 *
 * Handles Google Maps integration, markers, clustering, and card interactivity
 *
 * @package Key_CY_Properties_Filter
 */

(function ($) {
  "use strict";

  /**
   * Map View Controller
   */
  const KCPFMapView = {
    map: null,
    circles: [],
    infoWindow: null,
    properties: [],
    currentPurpose: "sale",

    /**
     * Initialize map view
     */
    init: function () {
      console.log("[KCPF Map] Initializing map view");

      // Check if map container exists
      const mapContainer = document.getElementById("kcpf-google-map");
      if (!mapContainer) {
        console.log(
          "[KCPF Map] Map container not found, skipping initialization"
        );
        return;
      }

      // Get purpose
      const mapView = $(".kcpf-map-view");
      if (mapView.length) {
        this.currentPurpose = mapView.data("purpose") || "sale";
      }

      // Load properties data
      this.loadPropertiesData();

      // Initialize Google Map when API is ready
      if (typeof google !== "undefined" && google.maps) {
        this.initializeMap();
      } else {
        console.error("[KCPF Map] Google Maps API not loaded");
      }

      // Setup event handlers
      this.setupEventHandlers();
    },

    /**
     * Load properties data from JSON script tag
     */
    loadPropertiesData: function () {
      const dataElement = document.getElementById("kcpf-map-properties-data");
      if (dataElement) {
        try {
          this.properties = JSON.parse(dataElement.textContent);
          console.log(
            "[KCPF Map] Loaded " + this.properties.length + " properties"
          );
        } catch (e) {
          console.error("[KCPF Map] Error parsing properties data:", e);
          this.properties = [];
        }
      }
    },

    /**
     * Initialize Google Map
     */
    initializeMap: function () {
      console.log("[KCPF Map] Initializing Google Map");

      // Default center (Cyprus)
      const defaultCenter = { lat: 35.1264, lng: 33.4299 };

      // Create map
      this.map = new google.maps.Map(
        document.getElementById("kcpf-google-map"),
        {
          zoom: 10,
          center: defaultCenter,
          mapTypeControl: true,
          streetViewControl: true,
          fullscreenControl: true,
          zoomControl: true,
        }
      );

      // Create info window
      this.infoWindow = new google.maps.InfoWindow();

      // Add circles (replacing markers)
      this.addMarkers();

      // Fit bounds to show all circles
      this.fitBoundsToMarkers();
    },

    /**
     * Add markers to map
     */
    addMarkers: function () {
      console.log(
        "[KCPF Map] Adding markers for " +
          this.properties.length +
          " properties"
      );

      // Clear existing markers and circles
      this.clearMarkers();

      this.properties.forEach((property) => {
        if (!property.lat || !property.lng) {
          return;
        }

        // Create 200m radius circle (black) - this replaces the marker
        const circle = new google.maps.Circle({
          strokeColor: "#000000",
          strokeOpacity: 0.6,
          strokeWeight: 2,
          fillColor: "#000000",
          fillOpacity: 0.15,
          map: this.map,
          center: { lat: property.lat, lng: property.lng },
          radius: 200, // 200 meters
          clickable: true,
          cursor: "pointer",
        });

        // Store property data on the circle
        circle.propertyId = property.id;
        circle.propertyData = property;

        // Add click listener to circle
        circle.addListener("click", () => {
          this.onCircleClick(circle, property);
        });

        this.circles.push(circle);
      });

      console.log(
        "[KCPF Map] Added " + this.circles.length + " property circles"
      );
    },

    /**
     * Handle circle click
     */
    onCircleClick: function (circle, property) {
      console.log("[KCPF Map] Circle clicked:", property.id);

      // Pan to circle center
      this.map.panTo(circle.getCenter());
      this.map.setZoom(16);

      // Get full property card HTML via AJAX
      this.showPropertyInfoWindow(circle, property);

      // Highlight and scroll to corresponding card
      this.highlightCard(property.id);
    },

    /**
     * Show property info window with full card details
     */
    showPropertyInfoWindow: function (circle, property) {
      // Make AJAX request to get property card HTML
      $.ajax({
        url: kcpfData.ajaxUrl,
        type: "GET",
        data: {
          action: "kcpf_get_property_card",
          property_id: property.id,
        },
        success: (response) => {
          if (response.success && response.data.html) {
            this.infoWindow.setContent(response.data.html);
            this.infoWindow.setPosition(circle.getCenter());
            this.infoWindow.open(this.map);
          }
        },
        error: (xhr, status, error) => {
          console.error("[KCPF Map] Error loading property card:", error);
        },
      });
    },

    /**
     * Highlight card in sidebar
     */
    highlightCard: function (propertyId) {
      // Remove active class from all cards
      $(".kcpf-map-sidebar .kcpf-property-card").removeClass(
        "kcpf-card-active"
      );

      // Add active class to target card
      const targetCard = $(
        `.kcpf-map-sidebar .kcpf-property-card[data-property-id="${propertyId}"]`
      );
      if (targetCard.length) {
        targetCard.addClass("kcpf-card-active");

        // Scroll card into view
        const container = $(".kcpf-map-cards-container");
        const scrollTop =
          targetCard.offset().top -
          container.offset().top +
          container.scrollTop() -
          20;

        container.animate(
          {
            scrollTop: scrollTop,
          },
          300
        );
      }
    },

    /**
     * Pan to circle by property ID
     */
    panToMarker: function (propertyId) {
      const circle = this.circles.find(
        (c) => c.propertyId === parseInt(propertyId)
      );

      if (circle) {
        this.map.panTo(circle.getCenter());
        this.map.setZoom(16);

        // Show info window with full property card
        const property = this.properties.find(
          (p) => p.id === parseInt(propertyId)
        );
        if (property) {
          this.showPropertyInfoWindow(circle, property);
        }
      }
    },

    /**
     * Fit map bounds to show all circles
     */
    fitBoundsToMarkers: function () {
      if (this.circles.length === 0) {
        return;
      }

      const bounds = new google.maps.LatLngBounds();

      this.circles.forEach((circle) => {
        bounds.extend(circle.getCenter());
      });

      this.map.fitBounds(bounds);

      // Prevent over-zooming for single circle
      if (this.circles.length === 1) {
        this.map.setZoom(14);
      }
    },

    /**
     * Clear all circles
     */
    clearMarkers: function () {
      // Remove circles from map
      this.circles.forEach((circle) => {
        circle.setMap(null);
      });

      this.circles = [];
    },

    /**
     * Setup event handlers
     */
    setupEventHandlers: function () {
      // Card hover - pan to marker
      $(document).on(
        "mouseenter",
        ".kcpf-map-sidebar .kcpf-property-card",
        (e) => {
          const propertyId = $(e.currentTarget).data("property-id");
          if (propertyId) {
            this.panToMarker(propertyId);
          }
        }
      );

      // Filter form submission
      $(document).on("submit", ".kcpf-map-filters-form", (e) => {
        e.preventDefault();
        this.handleFilterSubmit($(e.currentTarget));
      });

      // Apply button click
      $(document).on("click", ".kcpf-map-filters .kcpf-apply-button", (e) => {
        e.preventDefault();
        const form = $(e.currentTarget).closest("form");
        this.handleFilterSubmit(form);
      });

      // Reset button click
      $(document).on("click", ".kcpf-map-filters .kcpf-reset-button", (e) => {
        e.preventDefault();
        this.handleFilterReset();
      });
    },

    /**
     * Handle filter form submission
     */
    handleFilterSubmit: function (form) {
      console.log("[KCPF Map] Filter submit");

      // Show loading state
      $(".kcpf-map-cards-container").hide();
      $(".kcpf-map-loading").show();

      // Serialize form data
      const formData = form.serializeArray();
      const params = { action: "kcpf_load_map_properties" };

      formData.forEach((item) => {
        if (item.value !== "" && item.value !== null) {
          if (params.hasOwnProperty(item.name)) {
            if (!Array.isArray(params[item.name])) {
              params[item.name] = [params[item.name]];
            }
            params[item.name].push(item.value);
          } else {
            params[item.name] = item.value;
          }
        }
      });

      console.log("[KCPF Map] AJAX params:", params);

      // AJAX request
      $.ajax({
        url: kcpfData.ajaxUrl,
        type: "GET",
        data: params,
        success: (response) => {
          console.log("[KCPF Map] AJAX success:", response);

          if (response.success) {
            // Update cards
            $("#kcpf-map-cards").html(response.data.cards_html);

            // Update results count
            $(".kcpf-map-results-count").text(
              response.data.count +
                (response.data.count === 1
                  ? " property found"
                  : " properties found")
            );

            // Update properties data
            this.properties = response.data.properties_data || [];

            // Update map circles
            this.addMarkers();
            this.fitBoundsToMarkers();
          } else {
            console.error("[KCPF Map] AJAX error:", response.data);
          }

          // Hide loading state
          $(".kcpf-map-loading").hide();
          $(".kcpf-map-cards-container").show();
        },
        error: (xhr, status, error) => {
          console.error("[KCPF Map] AJAX error:", error);

          // Hide loading state
          $(".kcpf-map-loading").hide();
          $(".kcpf-map-cards-container").show();
        },
      });
    },

    /**
     * Handle filter reset
     */
    handleFilterReset: function () {
      console.log("[KCPF Map] Filter reset");

      // Reload page to reset filters
      window.location.href =
        window.location.pathname + "?purpose=" + this.currentPurpose;
    },
  };

  /**
   * Initialize when document is ready
   */
  $(document).ready(function () {
    // Wait a bit for Google Maps to load
    setTimeout(() => {
      KCPFMapView.init();
    }, 100);
  });

  // Also try to initialize when Google Maps callback is triggered
  window.kcpfInitMap = function () {
    console.log("[KCPF Map] Google Maps API loaded via callback");
    KCPFMapView.init();
  };
})(jQuery);
