<?php
/**
 * HtmlSanitizer - Allowlist-based sanitizer for HTML fragments
 * PHP 5.5 compatible
 */

class HtmlSanitizer {
    private $allowedTags = [
        "a",
        "abbr",
        "b",
        "blockquote",
        "br",
        "code",
        "div",
        "em",
        "h1",
        "h2",
        "h3",
        "h4",
        "h5",
        "h6",
        "hr",
        "i",
        "img",
        "li",
        "ol",
        "p",
        "span",
        "strong",
        "table",
        "tbody",
        "td",
        "th",
        "thead",
        "tr",
        "u",
        "ul",
    ];

    /**
     * Dangerous tags that must be completely removed (including their content).
     * These can execute code or load external resources dangerously.
     */
    private $dangerousTags = [
        "script",
        "style",
        "iframe",
        "frame",
        "frameset",
        "object",
        "embed",
        "applet",
        "form",
        "input",
        "button",
        "select",
        "textarea",
        "link",
        "meta",
        "base",
        "svg",
        "math",
        "template",
        "noscript",
    ];

    private $allowedAttributes = [
        "a" => ["href", "title", "target", "rel"],
        "img" => ["src", "alt", "title", "width", "height"],
    ];

    private $globalAttributes = ["class"];

    /**
     * Sanitize HTML input using an allowlist approach.
     * @param string $html Raw HTML
     * @return string Sanitized HTML
     */
    public function sanitize($html) {
        if ($html === null || $html === "") {
            return "";
        }

        // First pass: strip dangerous tags using regex (defense in depth)
        // This handles cases where DOMDocument might not parse correctly
        $html = $this->stripDangerousTags($html);

        $doc = new DOMDocument("1.0", "UTF-8");
        $previousState = libxml_use_internal_errors(true);

        // Wrap in a container and use proper encoding
        $wrappedHtml = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>'
            . '<body><div id="sanitizer-root">' . $html . '</div></body></html>';
        $doc->loadHTML($wrappedHtml);

        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        // Find our wrapper div
        $root = $doc->getElementById("sanitizer-root");
        if ($root === null) {
            return "";
        }

        // Second pass: DOM-based sanitization
        $this->sanitizeNode($root);

        return $this->getInnerHtml($root);
    }

    /**
     * Strip dangerous tags using regex as a first line of defense.
     * @param string $html Raw HTML
     * @return string HTML with dangerous tags removed
     */
    private function stripDangerousTags($html) {
        $pattern = '/<\s*(' . implode('|', $this->dangerousTags) . ')(\s[^>]*)?>.*?<\s*\/\s*\1\s*>/is';
        $html = preg_replace($pattern, '', $html);

        // Also remove self-closing variants and opening tags without closing
        $pattern = '/<\s*\/?\s*(' . implode('|', $this->dangerousTags) . ')(\s[^>]*)?\/?\s*>/i';
        $html = preg_replace($pattern, '', $html);

        return $html;
    }

    /**
     * Recursively sanitize a DOM node and its children.
     * @param DOMNode $node DOM node to sanitize
     * @return void
     */
    private function sanitizeNode($node) {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tag = strtolower($node->nodeName);

            // Completely remove dangerous tags (including content)
            if (in_array($tag, $this->dangerousTags, true)) {
                $this->removeNode($node);
                return;
            }

            // Unwrap non-allowed tags (preserve content)
            if (!in_array($tag, $this->allowedTags, true)) {
                $this->unwrapNode($node);
                return;
            }

            $this->sanitizeAttributes($node, $tag);
        } elseif ($node->nodeType === XML_COMMENT_NODE) {
            $this->removeNode($node);
            return;
        }

        // Process children - collect first to avoid modification during iteration
        $children = [];
        for ($child = $node->firstChild; $child !== null; $child = $child->nextSibling) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            $this->sanitizeNode($child);
        }
    }

    /**
     * Remove disallowed attributes and unsafe URL values.
     * @param DOMElement $node Element node
     * @param string $tag Tag name
     * @return void
     */
    private function sanitizeAttributes($node, $tag) {
        if (!$node->hasAttributes()) {
            return;
        }

        $allowed = $this->globalAttributes;
        if (isset($this->allowedAttributes[$tag])) {
            $allowed = array_merge($allowed, $this->allowedAttributes[$tag]);
        }

        $attributes = [];
        foreach ($node->attributes as $attribute) {
            $attributes[] = $attribute;
        }

        foreach ($attributes as $attribute) {
            $name = strtolower($attribute->nodeName);
            $value = $attribute->nodeValue;

            // Remove event handlers (onclick, onerror, etc.)
            if (strpos($name, "on") === 0) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            // Remove non-allowed attributes
            if (!in_array($name, $allowed, true)) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            // Check for dangerous URLs
            if (($name === "href" || $name === "src") && $this->isUnsafeUrl($value)) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            // Sanitize attribute value (remove any potential script injection)
            if ($this->hasUnsafeAttributeValue($value)) {
                $node->removeAttributeNode($attribute);
                continue;
            }
        }

        // Add security headers for external links
        if ($tag === "a" && $node->getAttribute("target") === "_blank" && !$node->hasAttribute("rel")) {
            $node->setAttribute("rel", "noopener noreferrer");
        }
    }

    /**
     * Check if a URL uses a dangerous scheme.
     * Normalizes the value to prevent obfuscation bypasses (tabs, newlines, etc.)
     * @param string $value URL value
     * @return bool True if unsafe
     */
    private function isUnsafeUrl($value) {
        // Remove all whitespace and control characters that could bypass protocol checks
        $normalized = preg_replace('/[\s\x00-\x1f\x7f]+/', '', $value);

        return preg_match('/^(javascript|data|vbscript):/i', $normalized) === 1;
    }

    /**
     * Check if an attribute value contains potentially dangerous content.
     * @param string $value Attribute value
     * @return bool True if unsafe
     */
    private function hasUnsafeAttributeValue($value) {
        $normalized = preg_replace('/[\s\x00-\x1f\x7f]+/', '', strtolower($value));

        // Check for javascript: protocol anywhere in value
        if (strpos($normalized, 'javascript:') !== false) {
            return true;
        }

        // Check for expression() CSS hack (IE)
        if (strpos($normalized, 'expression(') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Completely remove a node from the DOM.
     * @param DOMNode $node DOM node
     * @return void
     */
    private function removeNode($node) {
        if ($node->parentNode !== null) {
            $node->parentNode->removeChild($node);
        }
    }

    /**
     * Remove a node while preserving its children.
     * @param DOMNode $node DOM node
     * @return void
     */
    private function unwrapNode($node) {
        $parent = $node->parentNode;
        if ($parent === null) {
            return;
        }

        while ($node->firstChild !== null) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    /**
     * Get inner HTML of a node.
     * @param DOMNode $node DOM node
     * @return string HTML
     */
    private function getInnerHtml($node) {
        $html = "";
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        return $html;
    }
}
