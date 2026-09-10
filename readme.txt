=== Product Meta Debugger ===
Contributors: yourname
Donate link: https://example.com/
Tags: woocommerce, meta, debugger, acf, developer, inspector
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Inspect WooCommerce product post meta and ACF fields with a sleek Shopify-style nested tree viewer, search, and optional AI diagnostics.

== Description ==

**Product Meta Debugger** is a high-performance developer tool and metadata inspector for WooCommerce and WordPress. It allows store managers and developers to instantly search, view, and understand product post meta, custom fields, and Advanced Custom Fields (ACF) data right from the frontend or wp-admin via a slide-out drawer or keyboard shortcut.

### Features
* **Shopify-Style Nested Tree**: Recursively browse deeply nested arrays and objects with clear connector lines and collapse/expand controls.
* **Instant Product Search**: Quickly search products by Title, Post ID, or SKU with keyboard navigation.
* **Categorized Inspection**: Filter meta by WooCommerce internal keys (`_`), ACF fields, standard Post Meta, or All.
* **Full-Value Modal**: Inspect complete payloads formatted in readable JSON with one-click clipboard copying.
* **Developer Quick Actions**: One-click link to edit the inspected product in the WordPress admin.
* **WP Admin Bar & Hotkey**: Accessible via the top Admin Bar or using the keyboard shortcut `Ctrl+Shift+D` (`Cmd+Shift+D` on Mac).
* **Enterprise Security**: Strict nonce validation, capability checks (`manage_woocommerce` / `manage_options`), and automatic server-side filtering of sensitive keys (passwords, tokens, auth cookies).
* **Optional AI Meta Assistant (Opt-in)**: Connect your own API key (OpenAI, Claude, Gemini, or local Ollama) to explain obscure meta keys, detect corrupted data, or generate ready-to-use PHP/REST code snippets.

== External Services ==

This plugin includes an **optional, strictly opt-in** AI Assistant feature. By default, **no data is ever sent to any third-party service**.

The plugin is 100% functional without configuring or using any AI services.

If you choose to enable the AI Assistant in **Settings > Meta Debugger > AI Assistant** by entering your own API key (Bring Your Own Key - BYOK), the plugin will connect to the AI provider you select when you click "Explain with AI" or "Generate Code":

* **OpenAI (ChatGPT)**:
  * Purpose: Explaining metadata structures and generating PHP code snippets.
  * Data Transmitted: The selected meta key name, its sanitized data structure, and post type context.
  * Privacy Filter: All passwords, authentication tokens, customer PII (emails, names, billing addresses), and IP addresses are scrubbed on your server prior to transmission.
  * Terms of Service: https://openai.com/policies/terms-of-use/
  * Privacy Policy: https://openai.com/policies/privacy-policy/

* **Anthropic (Claude)**:
  * Purpose: Explaining metadata structures and generating PHP code snippets.
  * Data Transmitted: The selected meta key name, its sanitized data structure, and post type context.
  * Terms of Service: https://www.anthropic.com/legal/consumer-terms
  * Privacy Policy: https://www.anthropic.com/legal/privacy

* **Google (Gemini)**:
  * Purpose: Explaining metadata structures and generating PHP code snippets.
  * Data Transmitted: The selected meta key name, its sanitized data structure, and post type context.
  * Terms of Service: https://policies.google.com/terms
  * Privacy Policy: https://policies.google.com/privacy

* **Local / Self-Hosted LLM (Ollama / LocalAI)**:
  * Purpose: Local, offline AI processing on your own server or localhost (`http://localhost:11434/v1`).
  * Data Transmitted: Stays entirely within your local network or server.

== Installation ==

1. Upload the `meta-debugger` folder to your `/wp-content/plugins/` directory, or install via the WordPress Plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. As an administrator or shop manager, navigate to any product page on the frontend or backend.
4. Press `Ctrl+Shift+D` (`Cmd+Shift+D` on Mac) or click the **Meta Debugger** trigger to open the inspector.
5. (Optional) Configure display options and AI assistant settings under **Settings > Meta Debugger**.

== Frequently Asked Questions ==

= Who can access the debugger drawer? =
Only authenticated users with the `manage_woocommerce` or `manage_options` capability. Regular site visitors and customers cannot see or access the debugger, and all AJAX endpoints reject unauthorized requests.

= Can I use this plugin if WooCommerce is not installed? =
Yes! If WooCommerce is not active, the plugin operates as a standard WordPress post meta debugger, allowing you to inspect any post or custom post type.

= Does this plugin load external CDNs or scripts? =
No. In accordance with WordPress.org guidelines, all styles, scripts, and SVG icons are bundled locally within the plugin.

= Is the AI feature mandatory? =
No. The AI feature is completely disabled by default. You do not need an API key to use any of the debugger's inspection, tree visualization, search, or copying capabilities.

== Screenshots ==

1. Slide-out drawer with Shopify-style nested tree viewer and product search.
2. Value viewer modal with JSON formatting and one-click copy.
3. Settings page with display preferences and optional AI configuration.

== Changelog ==

= 1.0.0 =
* Initial release for WordPress.org.
* Slide-out product meta drawer with responsive nested tree view.
* Instant product search by title, ID, or SKU.
* Mode tabs: All, WooCommerce, ACF, Post Meta.
* Safe recursion engine with sensitive key redaction.
* WP Admin Bar and keyboard shortcut integration.
* Optional BYOK AI assistant with local PII sanitizer.
