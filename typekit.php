<?php
/*
Plugin Name: Adobe Fonts (formerly Typekit) for WordPress
Plugin URI: https://om4.io/plugins/adobe-fonts-for-wordpress/
Description: Use a range of over 25,000 of high quality fonts on your WordPress website by integrating the <a href="https://fonts.adobe.com">Adobe Fonts</a> font service into your WordPress blog.
Version: 1.10.1
Author: OM4
Author URI: https://om4.io/
Text Domain: typekit-fonts-for-wordpress
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*
	Copyright 2009-2024 OM4 (email: plugins@om4.io    web: https://om4.io/)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Adobe Fonts (formerly Typekit) functionality.
 */
class OM4_Typekit {

	/**
	 * The version of the database schema used by this plugin
	 *
	 * @var int
	 */
	private $db_version = 1;

	/**
	 * The version of the plugin that is currently installed
	 *
	 * @var int
	 */
	private $installed_version;

	/**
	 * The name of the option used to store the plugin's settings
	 *
	 * @var string
	 */
	private $option_name = 'OM4_Typekit';

	/**
	 * The format for the Adobe Fonts JavaScript embed code
	 *
	 * @var string
	 */
	public $embedcode_advanced = '<script>
  (function(d) {
    var config = {
      kitId: \'%1$s\',
      scriptTimeout: 3000,
      async: %2$s
    },
    h=d.documentElement,t=setTimeout(function(){h.className=h.className.replace(/\bwf-loading\b/g,"")+" wf-inactive";},config.scriptTimeout),tk=d.createElement("script"),f=false,s=d.getElementsByTagName("script")[0],a;h.className+=" wf-loading";tk.src=\'https://use.typekit.net/\'+config.kitId+\'.js\';tk.async=true;tk.onload=tk.onreadystatechange=function(){a=this.readyState;if(f||a&&a!="complete"&&a!="loaded")return;f=true;clearTimeout(t);try{Typekit.load(config)}catch(e){}};s.parentNode.insertBefore(tk,s)
  })(document);
</script>';

	// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
	/**
	 * The format for the Adobe Fonts CSS file URL
	 *
	 * @var string
	 */
	public $embedcode_css = '<link rel="stylesheet" href="https://use.typekit.net/%s.css">';
	// phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet

	/**
	 * The regular expression used to validate the Adobe Fonts Account/Web Projects ID
	 *
	 * @var string
	 */
	public $kitid_regexp = '#([a-z0-9]*)#i';

	/**
	 * The format for the Adobe Fonts CSS file URL. Used in HTTP requests to verify that the URL doesn't produce a 404 error
	 *
	 * @var string
	 */
	public $embedcodeurl = 'https://use.typekit.net/%s.css';

	const EMBED_METHOD_CSS = 'css';

	const EMBED_METHOD_JAVASCRIPT = 'js';

	/**
	 * Default settings
	 *
	 * @var array<string,string>
	 */
	private $settings = array(
		'id'     => '',
		'method' => self::EMBED_METHOD_CSS,
		'css'    => '',
		'async'  => '',
	);

	/**
	 * Class Constructor
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		add_action( 'init', array( $this, 'initialise' ) );

		add_action( 'plugins_loaded', array( $this, 'load_domain' ) );

		add_action( 'wp_head', array( $this, 'header_code' ), 99 );

		$data = get_option( $this->option_name );
		if ( is_array( $data ) ) {
			$this->installed_version = intval( $data['version'] );
			$this->settings          = $data['settings'];
		}
	}

	/**
	 * Load up the relevant language pack if we're using WordPress in a different language.
	 *
	 * @return void
	 */
	public function load_domain() {
		load_plugin_textdomain( 'typekit-fonts-for-wordpress' );
	}

	/**
	 * Plugin Activation Tasks
	 *
	 * @return void
	 */
	public function activate() {
		// There aren't really any installation tasks at the moment.
		if ( ! $this->installed_version ) {
			$this->installed_version = $this->db_version;
			$this->save_settings();
		}
	}

	/**
	 * Performs any upgrade tasks if required
	 *
	 * @return void
	 */
	public function check_version() {
		if ( $this->installed_version !== $this->db_version ) {
			// Upgrade tasks.
			if ( 0 === $this->installed_version ) {
				++$this->installed_version;
			}
			$this->save_settings();
		}
	}

	/**
	 * Initialise the plugin.
	 * Set up the admin interface if necessary
	 *
	 * @return void
	 */
	public function initialise() {

		$this->check_version();

		if ( is_admin() ) {
			// WP Dashboard.
			require_once 'typekit-admin.php';
			new OM4_Typekit_Admin( $this );
		}
	}

	/**
	 * Saves the plugin's settings to the database
	 *
	 * @return void
	 */
	public function save_settings() {
		$data = array_merge( array( 'version' => $this->installed_version ), array( 'settings' => $this->settings ) );
		update_option( $this->option_name, $data );
	}

	/**
	 * Retrieve the Adobe Fonts embed code if the unique account id has been set
	 *
	 * @return string The Adobe Fonts embed code if the unique account ID has been set, otherwise an empty string.
	 */
	public function get_embed_code() {
		$id = $this->get_account_id();
		if ( '' !== $id ) {
			switch ( $this->get_embed_method() ) {
				case self::EMBED_METHOD_CSS:
					return sprintf( $this->embedcode_css, $id );
				case self::EMBED_METHOD_JAVASCRIPT:
					$async = $this->get_async() ? 'true' : 'false';
					return sprintf( $this->embedcode_advanced, $id, $async );
			}
		}
		return '';
	}

	/**
	 * Get the stored Adobe Fonts Account/Web Projects ID
	 *
	 * @return string The account ID if it has been specified, otherwise an empty string
	 */
	public function get_account_id() {
		if ( strlen( $this->settings['id'] ) ) {
			return $this->settings['id'];
		}
		return '';
	}

	/**
	 * Get the stored value for the async parameter.
	 *
	 * Defaults to true.
	 *
	 * @return bool
	 */
	public function get_async() {
		if ( isset( $this->settings['async'] ) && false === $this->settings['async'] ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get the stored value for the embed method.
	 *
	 * @return string
	 */
	public function get_embed_method() {
		if ( isset( $this->settings['method'] ) ) {
			return $this->settings['method'];
		} else {
			// No embed method chosen, so default to the JS method.
			return self::EMBED_METHOD_JAVASCRIPT;
		}
	}

	/**
	 * Parse and save the Adobe Fonts Account/Web Projects ID
	 *
	 * @param string $id The Adobe Fonts Account/Web Projects ID.
	 * @return void
	 */
	public function parse_kit_id( $id ) {
		if ( preg_match( $this->kitid_regexp, $id, $matches ) && 2 === count( $matches ) ) {
			$this->settings['id'] = $matches[0];
		} else {
			$this->settings['id'] = '';
		}
	}

	/**
	 * Parse and save the embed method.
	 *
	 * @param string $method Embed method.
	 * @return void
	 */
	public function parse_embed_method( $method ) {
		if ( self::EMBED_METHOD_JAVASCRIPT === $method ) {
			$this->settings['method'] = self::EMBED_METHOD_JAVASCRIPT;
		} else {
			$this->settings['method'] = self::EMBED_METHOD_CSS;
			$this->settings['async']  = '';
		}
	}


	/**
	 * Retrieve the custom CSS rules
	 *
	 * @return string The custom CSS rules
	 */
	public function get_css_rules() {
		return $this->settings['css'];
	}

	/**
	 * Parse and save the custom css rules.
	 * The input is sanitized by stripping all HTML tags
	 *
	 * @param string $code CSS code.
	 * @return void
	 */
	public function set_css_rules( $code ) {
		$this->settings['css'] = '';
		$code                  = wp_strip_all_tags( $code );
		if ( strlen( $code ) ) {
			$this->settings['css'] = $code;
		}
	}

	/**
	 * Display the plugin's javascript and css code in the site's header
	 *
	 * @return void
	 */
	public function header_code() {

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<!-- BEGIN Adobe Fonts for WordPress -->';
		echo $this->get_embed_code();
		// If CSS settings exist, echo them within style tags.
		if ( strlen( $this->settings['css'] ) ) {
			echo "<style type='text/css'>{$this->settings['css']}</style>";
		}
		echo '<!-- END Adobe Fonts for WordPress -->';
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

if ( defined( 'ABSPATH' ) && defined( 'WPINC' ) ) {
	if ( ! isset( $GLOBALS['OM4_Typekit'] ) ) {
		$GLOBALS['OM4_Typekit'] = new OM4_Typekit();
	}
}
