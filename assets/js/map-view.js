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
    markers: [],
    circles: [],
    markerClusterer: null,
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

      // Add markers
      this.addMarkers();

      // Initialize clustering
      this.initializeClustering();

      // Fit bounds to show all markers
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

        // Create marker
        const marker = new google.maps.Marker({
          position: { lat: property.lat, lng: property.lng },
          map: this.map,
          title: property.title,
          propertyId: property.id,
        });

        // Create 100m radius circle
        const circle = new google.maps.Circle({
          strokeColor: "#007bff",
          strokeOpacity: 0.6,
          strokeWeight: 2,
          fillColor: "#007bff",
          fillOpacity: 0.15,
          map: this.map,
          center: { lat: property.lat, lng: property.lng },
          radius: 100, // 100 meters
        });

        // Add click listener to marker
        marker.addListener("click", () => {
          this.onMarkerClick(marker, property);
        });

        this.markers.push(marker);
        this.circles.push(circle);
      });

      console.log("[KCPF Map] Added " + this.markers.length + " markers");
    },

    /**
     * Initialize marker clustering
     */
    initializeClustering: function () {
      // Check if MarkerClusterer is available
      if (typeof MarkerClusterer === "undefined") {
        console.warn(
          "[KCPF Map] MarkerClusterer not available, loading from CDN"
        );
        this.loadMarkerClusterer();
        return;
      }

      console.log("[KCPF Map] Initializing marker clustering");

      // Clear existing clusterer
      if (this.markerClusterer) {
        this.markerClusterer.clearMarkers();
      }

      // Create new clusterer
      this.markerClusterer = new MarkerClusterer(this.map, this.markers, {
        imagePath:
          "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
        gridSize: 50,
        maxZoom: 15,
      });
    },

    /**
     * Load MarkerClusterer library
     */
    loadMarkerClusterer: function () {
      const script = document.createElement("script");
      script.src =
        "https://unpkg.com/@googlemaps/markerclustererplus/dist/index.min.js";
      script.onload = () => {
        console.log("[KCPF Map] MarkerClusterer loaded");
        this.initializeClustering();
      };
      document.head.appendChild(script);
    },

    /**
     * Handle marker click
     */
    onMarkerClick: function (marker, property) {
      console.log("[KCPF Map] Marker clicked:", property.id);

      // Pan to marker
      this.map.panTo(marker.getPosition());
      this.map.setZoom(16);

      // Show info window
      const content = `
        <div class="kcpf-map-info-window">
          <h4 class="kcpf-map-info-title">${property.title}</h4>
          <a href="${property.url}" class="kcpf-map-info-link" target="_blank">View Details →</a>
        </div>
      `;

      this.infoWindow.setContent(content);
      this.infoWindow.open(this.map, marker);

      // Highlight and scroll to corresponding card
      this.highlightCard(property.id);
    },

    /**
     * Highlight card in sidebar
     */
    highlightCard: function (propertyId) {
      // Remove active class from all cards
      $(".kcpf-map-card").removeClass("kcpf-card-active");

      // Add active class to target card
      const targetCard = $(`.kcpf-map-card[data-property-id="${propertyId}"]`);
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
     * Pan to marker by property ID
     */
    panToMarker: function (propertyId) {
      const marker = this.markers.find(
        (m) => m.propertyId === parseInt(propertyId)
      );

      if (marker) {
        this.map.panTo(marker.getPosition());
        this.map.setZoom(16);

        // Optionally show info window
        const property = this.properties.find(
          (p) => p.id === parseInt(propertyId)
        );
        if (property) {
          const content = `
            <div class="kcpf-map-info-window">
              <h4 class="kcpf-map-info-title">${property.title}</h4>
              <a href="${property.url}" class="kcpf-map-info-link" target="_blank">View Details →</a>
            </div>
          `;
          this.infoWindow.setContent(content);
          this.infoWindow.open(this.map, marker);
        }
      }
    },

    /**
     * Fit map bounds to show all markers
     */
    fitBoundsToMarkers: function () {
      if (this.markers.length === 0) {
        return;
      }

      const bounds = new google.maps.LatLngBounds();

      this.markers.forEach((marker) => {
        bounds.extend(marker.getPosition());
      });

      this.map.fitBounds(bounds);

      // Prevent over-zooming for single marker
      if (this.markers.length === 1) {
        this.map.setZoom(14);
      }
    },

    /**
     * Clear all markers and circles
     */
    clearMarkers: function () {
      // Remove markers from map
      this.markers.forEach((marker) => {
        marker.setMap(null);
      });

      // Remove circles from map
      this.circles.forEach((circle) => {
        circle.setMap(null);
      });

      // Clear clusterer
      if (this.markerClusterer) {
        this.markerClusterer.clearMarkers();
      }

      this.markers = [];
      this.circles = [];
    },

    /**
     * Setup event handlers
     */
    setupEventHandlers: function () {
      // Card hover - pan to marker
      $(document).on("mouseenter", ".kcpf-map-card", (e) => {
        const propertyId = $(e.currentTarget).data("property-id");
        if (propertyId) {
          this.panToMarker(propertyId);
        }
      });

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

            // Update map markers
            this.addMarkers();
            this.initializeClustering();
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
