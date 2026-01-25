/**
 * Report Manager JavaScript
 * Handles interactive features for the report manager admin interface
 */

(function () {
  "use strict";

  /**
   * Template field state management
   * Handles Default/Custom/Empty radio button interactions
   */
  var templateFieldManager = {
    // Cache for loaded default templates
    templateCache: {},

    // Track original content for modification detection
    originalContent: {},

    /**
     * Initialize template field management
     */
    init: function () {
      var self = this;
      var templateFields = document.querySelectorAll(".html-template-field");

      templateFields.forEach(function (fieldContainer) {
        self.initField(fieldContainer);
      });

      // Add form validation
      self.initFormValidation();
    },

    /**
     * Initialize a single template field
     * @param {HTMLElement} fieldContainer - The field container element
     */
    initField: function (fieldContainer) {
      var self = this;
      var fieldName = fieldContainer.getAttribute("data-field");
      var defaultTemplate = fieldContainer.getAttribute("data-default-template");
      var textarea = fieldContainer.querySelector("textarea");
      var radioButtons = fieldContainer.querySelectorAll('input[type="radio"]');

      // Store reference to default template filename
      fieldContainer.defaultTemplate = defaultTemplate;

      // Get the currently selected state
      var selectedRadio = fieldContainer.querySelector('input[type="radio"]:checked');
      var currentState = selectedRadio ? selectedRadio.value : "default";

      // Initialize field based on current state
      if (currentState === "default") {
        // Load default template content if textarea is empty or we're in default mode
        self.loadDefaultTemplate(fieldContainer, textarea, defaultTemplate);
      } else if (currentState === "empty") {
        textarea.value = "The field is disabled and excluded from reports.";
        textarea.readOnly = true;
        textarea.classList.add("readonly-field");
      }

      // Store original content for modification detection
      self.originalContent[fieldName] = textarea.value;

      // Add event listeners to radio buttons
      radioButtons.forEach(function (radio) {
        radio.addEventListener("change", function () {
          self.handleStateChange(fieldContainer, textarea, radio.value);
        });
      });

      // Add input listener to detect content modification in default mode
      textarea.addEventListener("input", function () {
        self.handleContentChange(fieldContainer, textarea, fieldName);
      });
    },

    /**
     * Load default template content via AJAX
     * @param {HTMLElement} fieldContainer - The field container element
     * @param {HTMLTextAreaElement} textarea - The textarea element
     * @param {string} templateFile - Template filename to load
     */
    loadDefaultTemplate: function (fieldContainer, textarea, templateFile) {
      var self = this;
      var fieldName = fieldContainer.getAttribute("data-field");

      // Check cache first
      if (self.templateCache[templateFile]) {
        textarea.value = self.templateCache[templateFile];
        self.originalContent[fieldName] = textarea.value;
        return;
      }

      // Show loading state
      textarea.placeholder = "Loading default template...";
      textarea.disabled = true;

      // Fetch template via AJAX
      var xhr = new XMLHttpRequest();
      xhr.open("GET", "reportManager?action=get_template&template=" + encodeURIComponent(templateFile), true);
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          textarea.disabled = false;
          if (xhr.status === 200) {
            try {
              var response = JSON.parse(xhr.responseText);
              if (response.success) {
                // Cache the template
                self.templateCache[templateFile] = response.content;
                textarea.value = response.content;
                self.originalContent[fieldName] = response.content;
              } else {
                console.error("Failed to load template:", response.error);
                textarea.placeholder = "Failed to load default template";
              }
            } catch (e) {
              console.error("Failed to parse template response:", e);
              textarea.placeholder = "Failed to load default template";
            }
          } else {
            console.error("Failed to fetch template:", xhr.status);
            textarea.placeholder = "Failed to load default template";
          }
        }
      };
      xhr.send();
    },

    /**
     * Handle radio button state change
     * @param {HTMLElement} fieldContainer - The field container element
     * @param {HTMLTextAreaElement} textarea - The textarea element
     * @param {string} newState - The new state value
     */
    handleStateChange: function (fieldContainer, textarea, newState) {
      var self = this;
      var defaultTemplate = fieldContainer.defaultTemplate;

      // Remove readonly styling
      textarea.readOnly = false;
      textarea.classList.remove("readonly-field");

      switch (newState) {
        case "default":
          // Load default template content
          self.loadDefaultTemplate(fieldContainer, textarea, defaultTemplate);
          break;

        case "custom":
          // Keep current content, make editable
          // If switching from empty, clear the placeholder text
          if (textarea.value === "" || textarea.value === "The field is disabled and excluded from reports.") {
            textarea.value = "";
            textarea.placeholder = "";
            textarea.focus();
          }
          break;

        case "empty":
          // Clear content and make readonly
          textarea.value = "The field is disabled and excluded from reports.";
          textarea.readOnly = true;
          textarea.classList.add("readonly-field");
          break;
      }
    },

    /**
     * Handle content change in textarea
     * @param {HTMLElement} fieldContainer - The field container element
     * @param {HTMLTextAreaElement} textarea - The textarea element
     * @param {string} fieldName - The field name
     */
    handleContentChange: function (fieldContainer, textarea, fieldName) {
      var self = this;
      var selectedRadio = fieldContainer.querySelector('input[type="radio"]:checked');
      var currentState = selectedRadio ? selectedRadio.value : "default";

      // Only switch to custom if in default mode and content has changed
      if (currentState === "default") {
        var originalContent = self.originalContent[fieldName] || "";
        if (textarea.value !== originalContent) {
          // Switch to custom mode
          var customRadio = fieldContainer.querySelector('input[type="radio"][value="custom"]');
          if (customRadio) {
            customRadio.checked = true;
          }
        }
      }
    },

    /**
     * Initialize form validation
     */
    initFormValidation: function () {
      var form = document.querySelector('form[action="reportManager"]');
      if (!form) {
        return;
      }

      form.addEventListener("submit", function (e) {
        var isValid = true;
        var errorMessages = [];

        // Check each template field
        var templateFields = document.querySelectorAll(".html-template-field");
        templateFields.forEach(function (fieldContainer) {
          var fieldName = fieldContainer.getAttribute("data-field");
          var textarea = fieldContainer.querySelector("textarea");
          var selectedRadio = fieldContainer.querySelector('input[type="radio"]:checked');
          var currentState = selectedRadio ? selectedRadio.value : "default";

          // If state is custom, content is required
          if (currentState === "custom" && !textarea.value.trim()) {
            isValid = false;
            var label = fieldContainer.querySelector("label");
            var fieldLabel = label ? label.textContent : fieldName;
            errorMessages.push(fieldLabel + " is required when set to Custom");
            textarea.classList.add("is-invalid");
          } else {
            textarea.classList.remove("is-invalid");
          }
        });

        if (!isValid) {
          e.preventDefault();
          alert("Validation errors:\n\n" + errorMessages.join("\n"));
        }
      });
    }
  };

  /**
   * Copy text to clipboard using modern Clipboard API with fallback
   * @param {string} text - Text to copy
   * @returns {Promise<boolean>} - Success status
   */
  function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(text).then(
        function () {
          return true;
        },
        function () {
          return fallbackCopyToClipboard(text);
        }
      );
    }
    return Promise.resolve(fallbackCopyToClipboard(text));
  }

  /**
   * Fallback copy method for older browsers
   * @param {string} text - Text to copy
   * @returns {boolean} - Success status
   */
  function fallbackCopyToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-9999px";
    textArea.style.top = "-9999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    var success = false;
    try {
      success = document.execCommand("copy");
    } catch (err) {
      console.error("Fallback copy failed:", err);
    }

    document.body.removeChild(textArea);
    return success;
  }

  /**
   * Show visual feedback when shortcode is copied
   * @param {HTMLElement} element - The clicked element
   */
  function showCopyFeedback(element) {
    var originalText = element.textContent;
    element.classList.add("copied");
    element.setAttribute("data-original-text", originalText);
    element.textContent = "Copied!";

    setTimeout(function () {
      element.classList.remove("copied");
      element.textContent = element.getAttribute("data-original-text");
      element.removeAttribute("data-original-text");
    }, 1000);
  }

  /**
   * Initialize shortcode copy functionality
   */
  function initShortcodeCopy() {
    var shortcodes = document.querySelectorAll(".shortcode-copy");

    shortcodes.forEach(function (element) {
      element.addEventListener("click", function (e) {
        var text = e.target.getAttribute("data-shortcode") || e.target.textContent;
        copyToClipboard(text).then(function (success) {
          if (success) {
            showCopyFeedback(e.target);
          }
        });
      });
    });
  }

  /**
   * Initialize theme selector with local persistence
   */
  function initThemeSelector() {
    var themeSelect = document.getElementById("theme-select");
    if (!themeSelect) {
      return;
    }

    var storageKey = "reportManagerTheme";

    function applyTheme(themeName) {
      var root = document.documentElement;
      var body = document.body;

      if (themeName === "default") {
        if (root) {
          root.removeAttribute("data-theme");
        }
        if (body) {
          body.removeAttribute("data-theme");
          body.classList.remove("theme-modern");
        }
        themeSelect.value = "default";
        return;
      }
      if (root) {
        root.setAttribute("data-theme", themeName);
      }
      if (body) {
        body.setAttribute("data-theme", themeName);
        body.classList.add("theme-modern");
      }
      themeSelect.value = themeName;
    }

    var savedTheme = null;
    try {
      savedTheme = localStorage.getItem(storageKey);
    } catch (err) {
      console.warn("Theme storage unavailable:", err);
    }

    if (savedTheme === "modern") {
      applyTheme("modern");
    } else {
      applyTheme("default");
    }

    themeSelect.addEventListener("change", function (e) {
      var selectedTheme = e.target.value;
      applyTheme(selectedTheme);
      try {
        if (selectedTheme === "default") {
          localStorage.removeItem(storageKey);
        } else {
          localStorage.setItem(storageKey, selectedTheme);
        }
      } catch (err) {
        console.warn("Theme storage unavailable:", err);
      }
    });
  }

  // Initialize when DOM is ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      initShortcodeCopy();
      initThemeSelector();
      templateFieldManager.init();
    });
  } else {
    initShortcodeCopy();
    initThemeSelector();
    templateFieldManager.init();
  }
})();
