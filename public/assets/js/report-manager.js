/**
 * Report Manager JavaScript
 * Handles interactive features for the report manager admin interface
 */

(function () {
  "use strict";

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
    });
  } else {
    initShortcodeCopy();
    initThemeSelector();
  }
})();
