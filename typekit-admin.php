<?php

/**
 * The Administration interface
 */
class OM4_Typekit_Admin {

	/**
	 * Reference to the OM4_Typekit instance
	 *
	 * @var OM4_Typekit
	 */
	private $typekit_instance;

	/**
	 * Class Constructor
	 *
	 * @param OM4_Typekit $instance Reference to the OM4_Typekit instance.
	 */
	public function __construct( &$instance ) {
		global $wpdb;

		$this->typekit_instance = $instance;

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_filter( 'plugin_action_links_' . str_replace( 'typekit-admin.php', 'typekit.php', plugin_basename( __FILE__ ) ), array( $this, 'action_links' ) );
	}

	/**
	 * Set up the Admin Settings menu
	 *
	 * @return void
	 */
	public function admin_menu() {
		add_options_page(
			__( 'Adobe Fonts (formerly Typekit)', 'typekit-fonts-for-wordpress' ),
			__( 'Adobe Fonts', 'typekit-fonts-for-wordpress' ),
			'manage_options',
			'typekit-admin',
			array( $this, 'admin_page' )
		);
	}

	/**
	 * Add "Settings" link to the plugin's action links on the plugins screen.
	 *
	 * @param string[] $links The existing links.
	 *
	 * @return string[] The modified links.
	 */
	public function action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'options-general.php?page=typekit-admin' ) . '">' . __( 'Settings', 'typekit-fonts-for-wordpress' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Display the admin settings page
	 *
	 * @return void
	 */
	public function admin_page() {
		?>
		<div class="wrap typekitsettings">
		<style type="text/css">
			.typekitsettings label { font-weight: bold; vertical-align: top; padding-right: 1em; }
			.typekitsettings li p { margin: 1em 0em; }
			.typekitsettings p code { margin: 0.5em; padding: 0.5em; display: block; }
			.typekitsettings code.inline { margin: 0; padding: 0.2em; display: inline; }
			.typekitsettings textarea { width: 90%; font-family: Courier, Fixed, monospace; }
			.typekitsettings .indent { margin-left: 2em; }
		</style>
		<?php
		if (
			isset( $_POST['submit'] ) &&
			check_admin_referer( 'typekit-fonts-for-wordpress-save-settings' ) &&
			current_user_can( 'manage_options' )
		) {
			// Settings page has been submitted.
			if ( isset( $_POST['kitid'] ) && isset( $_POST['method'] ) ) {
				$kitid = sanitize_text_field( wp_unslash( $_POST['kitid'] ) );
				$this->typekit_instance->parse_kit_id( $kitid );
				$method = sanitize_text_field( wp_unslash( $_POST['method'] ) );
				$this->typekit_instance->parse_embed_method( $method );

				$id = $this->typekit_instance->get_account_id();
				if ( '' === $id ) {
					// Embed code is empty.
					// Translators: %s is a link to the Adobe Fonts instructions.
					$instructions = sprintf( __( ' Please <a href="%s">click here for instructions</a> on how to obtain your Adobe Fonts embed code.', 'typekit-fonts-for-wordpress' ), '#register' );

					$message = strlen( $kitid )
						// An embed code has been submitted, but was rejected.
						// Translators: %s is a link to the Adobe Fonts instructions.
						? sprintf( __( 'Invalid Adobe Fonts Web Projects ID. %s', 'typekit-fonts-for-wordpress' ), $instructions )
						// No Web Projects ID was submitted.
						// Translators: %s is a link to the Adobe Fonts instructions.
						: sprintf( __( 'You must enter your Adobe Fonts Web Projects ID. %s', 'typekit-fonts-for-wordpress' ), $instructions );

					echo wp_kses_post( sprintf( '<div id="error" class="error"><p>%s</p></div>', $message ) );
				} else {
					// Ensure the Adobe Fonts account ID maps to a valid JS file on Adobe Fonts' servers (ie doesn't return a 404 error).
					$url      = sprintf( $this->typekit_instance->embedcodeurl, $id );
					$response = wp_remote_head( $url );

					if ( 404 === wp_remote_retrieve_response_code( $response ) ) {
						?>
						<div id="error" class="error"><p>
							<?php
								// Translators: %s is a link to the Adobe Fonts Embed Code page.
								printf( wp_kses_post( __( 'Your Adobe Fonts Web Projects ID may be incorrect because <a href="%1$s" target="_blank">%1$s</a> does not exist. Please verify that your Adobe Fonts Web Projects ID is correct. If you have just published your Web Projects, please try again in a few minutes.', 'typekit-fonts-for-wordpress' ) ), esc_url( $url ) );
							?>
						</p></div>
						<?php
					}
				}
			}
			if ( isset( $_POST['css'] ) ) {
				$css = wp_strip_all_tags( wp_unslash( $_POST['css'] ) );
				$this->typekit_instance->set_css_rules( $css );
			}
			$this->typekit_instance->save_settings();
			?>
			<div id="message" class="updated fade"><p><?php esc_html_e( 'Settings saved.', 'typekit-fonts-for-wordpress' ); ?></p></div>
			<?php
		}
		?>
		<h1><?php esc_html_e( 'Adobe Fonts (formerly Typekit) for WordPress', 'typekit-fonts-for-wordpress' ); ?></h1>

		<!-- Settings -->
		<h2><?php esc_html_e( 'Plugin Configuration', 'typekit-fonts-for-wordpress' ); ?></h2>
		<form method="post">
		<?php wp_nonce_field( 'typekit-fonts-for-wordpress-save-settings' ); ?>
		<ol>
			<li><?php esc_html_e( 'Enter your Adobe Fonts Web Project ID (shown on the Web Project screen).', 'typekit-fonts-for-wordpress' ); ?><br />
				<p class="option"><label for="kitid"><?php esc_html_e( 'Adobe Fonts Web Projects ID:', 'typekit-fonts-for-wordpress' ); ?></label> <input type="text" name="kitid" value="<?php echo esc_attr( $this->typekit_instance->get_account_id() ); ?>" /><br />
			</li>
			<li><?php esc_html_e( 'Choose your preferred embed method.', 'typekit-fonts-for-wordpress' ); ?><br />
				<p class="option"><label for="method"><?php esc_attr_e( 'Embed Method:', 'typekit-fonts-for-wordpress' ); ?></label>
					<select name="method">
						<option value="css"<?php echo selected( $this->typekit_instance->get_embed_method(), 'css', false ); ?>><?php esc_html_e( 'CSS Link (Simple)', 'typekit-fonts-for-wordpress' ); ?></option>
						<option value="js"<?php echo selected( $this->typekit_instance->get_embed_method(), 'js', false ); ?>><?php esc_html_e( 'JavaScript (Advanced)', 'typekit-fonts-for-wordpress' ); ?></option>
					</select>
			</li>

			<li><?php esc_html_e( 'Define your own CSS rules in your style sheet or use the Custom CSS Rules field below. (Technical note: These CSS rules will be embedded in the header of each page.)', 'typekit-fonts-for-wordpress' ); ?>
				<p class="option"><label for="css"><?php esc_attr_e( 'Custom CSS Rules:', 'typekit-fonts-for-wordpress' ); ?></label> <textarea name="css" rows="10" cols="80"><?php echo esc_textarea( $this->typekit_instance->get_css_rules() ); ?></textarea><br />
				<a href="#help-css"><?php esc_html_e( 'Click here for help on CSS', 'typekit-fonts-for-wordpress' ); ?></a>
				</p>
			</li>
		</ol>

		<p class="submit"><input name="submit" type="submit" value="<?php esc_attr_e( 'Save Settings', 'typekit-fonts-for-wordpress' ); ?>" class="button-primary" /></p>
		</form>

		<!-- Help -->
		<h2 id="help"><?php esc_html_e( 'Help', 'typekit-fonts-for-wordpress' ); ?></h2>

		<p><?php echo wp_kses_post( __( 'Adobe Fonts offers a service that allows you to select from over 25,000 high-quality fonts for your WordPress website. The fonts are applied using the <code class="inline">font-face</code> standard, so they are standards-compliant, fully licensed, and accessible.', 'typekit-fonts-for-wordpress' ) ); ?></p>
		<p><?php esc_html_e( 'To use this plugin, you need to sign up with Adobe Fonts and then configure the following options.', 'typekit-fonts-for-wordpress' ); ?></p>
		<h3 id="register"><?php esc_html_e( 'Register with Adobe Fonts', 'typekit-fonts-for-wordpress' ); ?></h3>
		<ol>
			<li>
			<?php
				// Translators: %s is a link to Adobe Fonts.
				printf( wp_kses( __( 'Go to <a href="%s" target="_blank">Adobe Fonts</a> and register for an account.', 'typekit-fonts-for-wordpress' ), 'post' ), 'https://fonts.adobe.com' );
			?>
			</li>
			<li><?php esc_html_e( 'Choose a few fonts to add to a Web Project.', 'typekit-fonts-for-wordpress' ); ?></li>
			<li><?php esc_html_e( 'Go to the Web Projects (link inside the Manage Fonts drop-down menu).', 'typekit-fonts-for-wordpress' ); ?></li>
		</ol>

		<h3 id="help-fontsnotshowing"><?php esc_html_e( 'Fonts not showing?', 'typekit-fonts-for-wordpress' ); ?></h3>
			<ul>
				<li><?php echo wp_kses_post( __( 'Have you created your Adobe Fonts account, added fonts to a <strong>Web Projects</strong>? Fonts aren\'t available without adding to a Web Projects.', 'typekit-fonts-for-wordpress' ) ); ?></li>
				<li><?php echo wp_kses_post( __( 'Have you <strong>waited a few minutes</strong> to allow Adobe Fonts time to send your fonts out around the world? Grab a cup of coffee and try again soon.', 'typekit-fonts-for-wordpress' ) ); ?></li>
				<li><?php echo wp_kses_post( __( 'Have you <strong>added CSS rules</strong> to display your fonts? If in doubt, just try the H2 rule shown in the example and see if that works for you.', 'typekit-fonts-for-wordpress' ) ); ?></li>
			</ul>
		<h3 id="help-css"><?php esc_html_e( 'CSS', 'typekit-fonts-for-wordpress' ); ?></h3>
			<p><?php esc_html_e( 'You can use CSS selectors to apply your new Adobe Fonts. The settings for this plugin allow you to add new CSS rules to your website to activate Adobe Fonts. If you are using fonts for more than just a few elements, managing them this way may be easier. And using your own CSS rules is a good way to access different font weights.', 'typekit-fonts-for-wordpress' ); ?></p>
			<p><?php esc_html_e( 'There are many options for using CSS, but here are a few common scenarios. Note: We\'ve used proxima-nova for our examples; you\'ll need to change "proxima-nova" to the name of your chosen font from Adobe Fonts your added font names will be visible in the Web Projects Editor.', 'typekit-fonts-for-wordpress' ); ?></p>
			<h4><?php esc_html_e( 'Headings', 'typekit-fonts-for-wordpress' ); ?></h4>
			<p>
				<?php esc_html_e( 'If you want your Adobe Fonts to be used for H2 headings, add a rule like this to your CSS Rules field:', 'typekit-fonts-for-wordpress' ); ?>
				<code>h2 { font-family: "proxima-nova-1","proxima-nova-2",sans-serif; }</code>
				<?php esc_html_e( 'You can add similar rules if you want to target other headings such as H3.', 'typekit-fonts-for-wordpress' ); ?>
			</p>
			<h4><?php esc_html_e( 'Sidebar Headings', 'typekit-fonts-for-wordpress' ); ?></h4>
			<p>
				<?php esc_html_e( 'If you want your Adobe Fonts to be used for sidebar H2 headings, add a rule like this to your CSS Rules field:', 'typekit-fonts-for-wordpress' ); ?>
				<code>#sidebar h2 { font-family: "proxima-nova-1","proxima-nova-2",sans-serif; }</code>
			</p>
			<h4><?php esc_html_e( 'Font Weights', 'typekit-fonts-for-wordpress' ); ?></h4>
			<p><?php echo wp_kses_post( __( 'If your Web Project contains more than one weight and/or style for a particular font, you need to use numeric <code class="inline">font-weight</code> values in your CSS rules to map to a font\'s weights.', 'typekit-fonts-for-wordpress' ) ); ?></p>
			<p><?php echo wp_kses_post( __( 'Adobe Fonts assigns values from 100 to 900 based on information from the font designer. Web browsers will guess which weight to display if the specified value isn\'t available. For example, if your font has weights 100, 300, and 900, setting your text with <code class="inline">font-weight: 400</code> will display the 300 weight font.', 'typekit-fonts-for-wordpress' ) ); ?></p>
			<p>
			<?php
				// Translators: %s is a link to Adobe Fonts Help.
				printf( wp_kses_post( __( 'See <a href="%s" target="_blank">this help article</a> for more details.', 'typekit-fonts-for-wordpress' ) ), 'http://getsatisfaction.com/typekit/topics/how_do_i_use_alternate_weights_and_styles' );
			?>
			</p>
		<h3 id="help-css-advanced"><?php esc_html_e( 'Advanced Targeting of Fonts with CSS Selectors', 'typekit-fonts-for-wordpress' ); ?></h3>
			<p>
				<?php esc_html_e( 'You can target your fonts to specific parts of your website if you know a bit more about your current WordPress theme and where the font family is specified. All WordPress themes have a style.css file, and if you know how to check that you should be able to see the selectors in use. Or you can install Chris Pederick\'s Web Developer Toolbar for Firefox and use the CSS, View CSS option to see all the CSS rules in use for your theme. When you find the selectors that are used for font-family, you can create a rule just for that selector to override that rule.', 'typekit-fonts-for-wordpress' ); ?>
				<?php esc_html_e( 'For example, if your theme has this CSS rule:', 'typekit-fonts-for-wordpress' ); ?>
				<code>body { font-family: Arial, Helvetica, Sans-Serif; }</code>
				<?php esc_html_e( 'you could create this rule to apply your new font to the body of your website:', 'typekit-fonts-for-wordpress' ); ?>
				<code>body { font-family: "proxima-nova-1","proxima-nova-2", sans-serif; }</code>
			</p>
		<h3 id="help-css-external"><?php esc_html_e( 'Where to Go for Help', 'typekit-fonts-for-wordpress' ); ?></h3>
			<p class="indent">
				<?php
					// Translators: %s is a link to Adobe Fonts Support.
					printf( wp_kses_post( __( '<a href="%s" target="_blank">Adobe Fonts Support</a>', 'typekit-fonts-for-wordpress' ) ), 'https://helpx.adobe.com/support/fonts.html' );
				?>
				<br />
				<?php
					// Translators: %s is a link to Sitepoint Community.
					printf( wp_kses_post( __( '<a href="%s" target="_blank">SitePoint CSS Forums</a>', 'typekit-fonts-for-wordpress' ) ), 'http://www.sitepoint.com/forums/forumdisplay.php?f=53' );
				?>
				<br />
				<?php
					// Translators: %s is a link to W3Schools CSS Help.
					printf( wp_kses_post( __( '<a href="%s" target="_blank">W3Schools CSS Help</a>', 'typekit-fonts-for-wordpress' ) ), 'http://www.w3schools.com/CSS/default.asp' );
				?>
			</p>
		</div>
		<?php
	}
}
