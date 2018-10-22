<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class PageSpeed_Optimization.
 *
 * @class PageSpeed_Optimization
 * version 0.3.1
 */
class PageSpeed_Optimization {

	/**
	 * @var string The plugin ID. Used for option names.
	 */
	public $plugin_id = 'pagespeed_optimization_';

	/**
	 * @var string ID of the class extending the settings. Used in option names.
	 */
	public $id = '';

	/**
	 * @var string Plugin version.
	 */
	public $version = '0.3.1';

	/**
	 * @var string Absolute plugin path.
	 */
	public $plugin_path;

	/**
	 * @var string Absolute plugin URL.
	 */
	public $plugin_url;

	/**
	 * @var array Form fields.
	 */
	public $form_fields;

	/**
	 * @var array Plugin options.
	 */
	public $settings;

	/**
	 * @var array Remote script file names.
	 */
	public $remote_filenames;

	/**
	 * @var array Cache file names.
	 */
	public $local_filenames;

	/**
	 * PageSpeed_Optimization constructor.
	 */
	public function __construct() {
		// Init fields.
		$this->plugin_path      = trailingslashit( plugin_dir_path( __DIR__ ) );
		$this->plugin_url       = trailingslashit( plugin_dir_url( __DIR__ ) );
		$this->remote_filenames = array(
			'ga'         => 'https://www.google-analytics.com/analytics.js',
			'gmap'       => 'https://maps.googleapis.com/maps/api/js',
			'ya_metrika' => 'https://mc.yandex.ru/metrika/watch.js',
		);
		$this->local_filenames  = array(
			'ga'         => 'cache/ga.js',
			'gmap'       => 'cache/gmap.js',
			'ya_metrika' => 'cache/ya_metrika.js',
		);

		$this->init_form_fields();
		$this->init_settings();

		$this->init_hooks();
	}

	/**
	 * Init various hooks.
	 */
	private function init_hooks() {
		add_filter( 'plugin_action_links_' . plugin_basename( PAGESPEED_OPTIMIZATION_PLUGIN_FILE ), array(
			$this,
			'add_settings_link',
		), 10, 2 );

		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'setup_sections' ) );
		add_action( 'admin_init', array( $this, 'setup_fields' ) );

		add_filter( 'pre_update_option_' . $this->get_option_key(), array( $this, 'pre_update_option_filter' ), 10, 3 );

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ), 100 );
		add_action( 'plugins_loaded', array( $this, 'check_cron' ), 100 );
		add_action( 'update_pagespeed_optimization_cache', array(
			$this,
			'update_pagespeed_optimization_cache_action',
		) );

		$enqueue_priority = $this->get_option( 'enqueue_priority' );
		if ( 'header' === $this->get_option( 'position' ) ) {
			add_action( 'wp_print_scripts', array( $this, 'print_scripts_action' ), $enqueue_priority );
		} else {
			add_action( 'wp_print_footer_scripts', array( $this, 'print_scripts_action' ), $enqueue_priority );
		}
		add_action( 'wp_print_scripts', array( $this, 'print_prevent_gmap_roboto' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_action' ), $enqueue_priority );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Register activation hook to schedule event in wp_cron()
		register_activation_hook( PAGESPEED_OPTIMIZATION_PLUGIN_FILE, array(
			$this,
			'activate_update_pagespeed_optimization_cache',
		) );

		// Register deactivation hook to remove event from wp_cron()
		register_deactivation_hook( PAGESPEED_OPTIMIZATION_PLUGIN_FILE, array(
			$this,
			'deactivate_update_pagespeed_optimization_cache',
		) );
	}

	public function test_update_pagespeed_optimization_cache_action() {
		echo 'test_update_pagespeed_optimization_cache_action';
	}

	/**
	 * Add settings page to the menu.
	 */
	public function add_settings_page() {
		$page_title = __( 'PageSpeed Optimization', 'kagg-pagespeed-optimization' );
		$menu_title = __( 'PageSpeed Opt.', 'kagg-pagespeed-optimization' );
		$capability = 'manage_options';
		$slug       = 'pagespeed-optimization';
		$callback   = array( $this, 'pagespeed_optimization_settings_page' );
		$icon       = $this->plugin_url . 'images/icon-16x16.png';
		$icon       = '<img class="pso-menu-image" src="' . $icon . '">';
		$menu_title = $icon . '<span class="pso-menu-title">' . $menu_title . '</span>';
		add_options_page( $page_title, $menu_title, $capability, $slug, $callback );
	}

	/**
	 * Options page.
	 */
	public function pagespeed_optimization_settings_page() {
		?>
		<div class="wrap">
			<h2 id="title">
				<?php
				// Admin panel title.
				echo( esc_html( __( 'PageSpeed Optimization', 'kagg-pagespeed-optimization' ) ) );
				?>
			</h2>

			<form action="options.php" method="POST">
				<?php
				settings_fields( 'pagespeed_optimization_group' ); // Hidden protection fields.
				do_settings_sections( 'pagespeed-optimization' ); // Sections with options.
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Setup settings sections.
	 */
	public function setup_sections() {
		add_settings_section( 'first_section', __( 'Options', 'kagg-pagespeed-optimization' ),
			array( $this, 'pagespeed_optimization_first_section' ), 'pagespeed-optimization'
		);
	}

	/**
	 * Section callback.
	 *
	 * @param array $arguments Section arguments.
	 */
	public function pagespeed_optimization_first_section( $arguments ) {
		switch ( $arguments['id'] ) {
			case 'first_section':
				echo '<p>' . esc_html__( 'Fill out IDs and key below to cache scripts locally and follow "Leverage browser caching" suggestion by Google PageSpeed Insights.', 'kagg-pagespeed-optimization' ) . '</p>';
				echo '<p>' . esc_html__( 'You can use other options for fine tuning.', 'kagg-pagespeed-optimization' ) . '</p>';
				break;
			default:
		}
	}

	/**
	 * Init options form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'ga_id'                    => array(
				'label'        => __( 'Google Analytics tracking ID', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'text',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
			),
			'gmap_key'                 => array(
				'label'        => __( 'Google Maps API key', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'text',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
			),
			'ya_metrika_id'            => array(
				'label'        => __( 'Yandex Metrika tracking ID', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'text',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
			),
			'position'                 => array(
				'label'        => __( 'Position of tracking code', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'radio',
				'options'      => array(
					'header' => 'Header',
					'footer' => 'Footer',
				),
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => 'header',
			),
			'bounce_rate'              => array(
				'label'        => __( 'Adjusted bounce rate', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'text',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
			),
			'enqueue_priority'         => array(
				'label'        => __( 'Enqueue priority', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'text',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => '10',
			),
			'disable_display_features' => array(
				'label'        => __( 'Disable display features functionality', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'checkbox',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
			),
			'anonymize_ip'             => array(
				'label'        => __( 'Anonymize IP', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'checkbox',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => 'yes',
			),
			'prevent_gmap_roboto'      => array(
				'label'        => __( 'Prevent Google Maps from loading Roboto font', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'checkbox',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => 'yes',
			),
			'remove_from_wp_cron'      => array(
				'label'        => __( 'Remove script from WP-Cron', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'checkbox',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
			),
		);
	}

	/**
	 * Initialise Settings.
	 *
	 * Store all settings in a single database entry
	 * and make sure the $settings array is either the default
	 * or the settings stored in the database.
	 */
	public function init_settings() {
		$this->settings = get_option( $this->get_option_key(), null );

		// If there are no settings defined, use defaults.
		if ( ! is_array( $this->settings ) ) {
			$form_fields    = $this->get_form_fields();
			$this->settings = array_merge( array_fill_keys( array_keys( $form_fields ), '' ), wp_list_pluck( $form_fields, 'default' ) );
		}
	}

	/**
	 * Get the form fields after they are initialized.
	 *
	 * @return array of options
	 */
	public function get_form_fields() {
		if ( empty( $this->form_fields ) ) {
			$this->init_form_fields();
		}

		return array_map( array( $this, 'set_defaults' ), $this->form_fields );
	}

	/**
	 * Set default required properties for each field.
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	protected function set_defaults( $field ) {
		if ( ! isset( $field['default'] ) ) {
			$field['default'] = '';
		}

		return $field;
	}

	/**
	 * Return the name of the options in the database.
	 *
	 * @return string
	 */
	public function get_option_key() {
		return $this->plugin_id . $this->id . '_settings';
	}

	/**
	 * Get plugin option.
	 *
	 * @param string $key
	 * @param mixed $empty_value
	 *
	 * @return string The value specified for the option or a default value for the option.
	 */
	public function get_option( $key, $empty_value = null ) {
		if ( empty( $this->settings ) ) {
			$this->init_settings();
		}

		// Get option default if unset.
		if ( ! isset( $this->settings[ $key ] ) ) {
			$form_fields            = $this->get_form_fields();
			$this->settings[ $key ] = isset( $form_fields[ $key ] ) ? $this->get_field_default( $form_fields[ $key ] ) : '';
		}

		if ( ! is_null( $empty_value ) && '' === $this->settings[ $key ] ) {
			$this->settings[ $key ] = $empty_value;
		}

		return $this->settings[ $key ];
	}

	/**
	 * Get a fields default value. Defaults to '' if not set.
	 *
	 * @param $field
	 *
	 * @return string
	 */
	public function get_field_default( $field ) {
		return empty( $field['default'] ) ? '' : $field['default'];
	}

	/**
	 * Filter plugin option update.
	 *
	 * @param mixed $value New option value.
	 * @param mixed $old_value Old option value.
	 * @param string $option Option name.
	 *
	 * @return mixed
	 */
	public function pre_update_option_filter( $value, $old_value, $option ) {
		if ( $value === $old_value ) {
			return $value;
		}

		$form_fields = $this->get_form_fields();
		foreach ( $form_fields as $key => $form_field ) {
			switch ( $form_field['type'] ) {
				case 'checkbox':
					$value[ $key ] = '1' === $value[ $key ] || 'yes' === $value[ $key ] ? 'yes' : 'no';
					break;
				default:
			}
		}

		return $value;
	}

	/**
	 * Setup options fields.
	 */
	public function setup_fields() {

		register_setting( 'pagespeed_optimization_group', $this->get_option_key() );

		foreach ( $this->form_fields as $key => $field ) {
			$field['field_id'] = $key;

			add_settings_field(
				$key, $field['label'], array( $this, 'field_callback' ),
				'pagespeed-optimization', $field['section'], $field
			);
		}
	}

	/**
	 * Output settings field.
	 *
	 * @param array $arguments Field arguments.
	 */
	public function field_callback( $arguments ) {
		$value = $this->get_option( $arguments['field_id'] );

		// Check which type of field we want.
		switch ( $arguments['type'] ) {
			case 'text':
			case 'password':
			case 'number':
				printf( '<input name="%1$s[%2$s]" id="%2$s" type="%3$s" placeholder="%4$s" value="%5$s" class="regular-text" />',
					esc_html( $this->get_option_key() ),
					esc_attr( $arguments['field_id'] ),
					esc_attr( $arguments['type'] ),
					esc_attr( $arguments['placeholder'] ),
					esc_html( $value )
				);
				break;
			case 'textarea':
				printf( '<textarea name="%1$s[%2$s]" id="%2$s" placeholder="%3$s" rows="5" cols="50">%4$s</textarea>',
					esc_html( $this->get_option_key() ),
					esc_attr( $arguments['field_id'] ),
					esc_attr( $arguments['placeholder'] ),
					wp_kses_post( $value )
				);
				break;
			case 'checkbox':
			case 'radio':
				if ( 'checkbox' === $arguments['type'] ) {
					$arguments['options'] = array( 'yes' => '' );
				}

				if ( ! empty( $arguments['options'] ) && is_array( $arguments['options'] ) ) {
					$options_markup = '';
					$iterator       = 0;
					foreach ( $arguments['options'] as $key => $label ) {
						$iterator ++;
						$options_markup .= sprintf(
							'<label for="%2$s_%7$s"><input id="%2$s_%7$s" name="%1$s[%2$s]" type="%3$s" value="%4$s" %5$s /> %6$s</label><br/>',
							esc_html( $this->get_option_key() ),
							$arguments['field_id'],
							$arguments['type'],
							$key,
							checked( $value, $key, false ),
							$label,
							$iterator
						);
					}
					printf( '<fieldset>%s</fieldset>',
						wp_kses( $options_markup,
							array(
								'label' => array(
									'for' => array(),
								),
								'input' => array(
									'id'      => array(),
									'name'    => array(),
									'type'    => array(),
									'value'   => array(),
									'checked' => array(),
								),
								'br'    => array(),
							)
						)
					);
				}
				break;
			case 'select': // If it is a select dropdown.
				if ( ! empty( $arguments['options'] ) && is_array( $arguments['options'] ) ) {
					$options_markup = '';
					foreach ( $arguments['options'] as $key => $label ) {
						$options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key,
							selected( $value, $key, false ), $label
						);
					}
					printf(
						'<select name="%1$s[%2$s]">%3$s</select>',
						esc_html( $this->get_option_key() ),
						esc_html( $arguments['field_id'] ),
						wp_kses( $options_markup,
							array(
								'option' => array(
									'value'    => array(),
									'selected' => array(),
								),
							)
						)
					);
				}
				break;
			case 'multiple': // If it is a multiple select dropdown.
				if ( ! empty( $arguments['options'] ) && is_array( $arguments['options'] ) ) {
					$options_markup = '';
					foreach ( $arguments['options'] as $key => $label ) {
						$selected = '';
						if ( is_array( $value ) ) {
							if ( in_array( $key, $value, true ) ) {
								$selected = selected( $key, $key, false );
							}
						}
						$options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key,
							$selected, $label
						);
					}
					printf(
						'<select multiple="multiple" name="%1$s[%2$s][]">%3$s</select>',
						esc_html( $this->get_option_key() ),
						esc_html( $arguments['field_id'] ),
						wp_kses( $options_markup,
							array(
								'option' => array(
									'value'    => array(),
									'selected' => array(),
								),
							)
						)
					);
				}
				break;
			default:
		} // End switch().

		// If there is help text.
		$helper = $arguments['helper'];
		if ( $helper ) {
			printf( '<span class="helper"> %s</span>', esc_html( $helper ) );
		}

		// If there is supplemental text.
		$supplemental = $arguments['supplemental'];
		if ( $supplemental ) {
			printf( '<p class="description">%s</p>', esc_html( $supplemental ) );
		}
	}

	/**
	 * Load plugin text domain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'kagg-pagespeed-optimization', false,
			plugin_basename( $this->plugin_path ) . '/languages/'
		);
	}

	/**
	 * Add link to plugin setting page on plugins page.
	 *
	 * @param array $links Plugin links
	 *
	 * @return array|mixed Plugin links
	 */
	public function add_settings_link( $links, $file ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=pagespeed-optimization' ) . '" aria-label="' . esc_attr__( 'View PageSpeed Module settings', 'kagg-pagespeed-optimization' ) . '">' . esc_html__( 'Settings', 'kagg-pagespeed-optimization' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Check cron status.
	 */
	public function check_cron() {
		//@todo Add selection of interval to options
		if ( 'yes' === $this->get_option( 'remove_from_wp_cron' ) ) {
			$this->deactivate_update_pagespeed_optimization_cache();
		} else {
			$this->activate_update_pagespeed_optimization_cache();
		}
	}

	/**
	 * Check if local files are in place. Update them if not.
	 */
	private function check_local_files() {
		foreach ( $this->local_filenames as $local_filename ) {
			if ( ! file_exists( $this->plugin_path . $local_filename ) ) {
				do_action( 'update_pagespeed_optimization_cache' );

				return;
			}
		}
	}

	/**
	 * Update scripts cache.
	 */
	public function update_pagespeed_optimization_cache_action() {
		$remote_file = $this->remote_filenames['ga'];
		$local_file  = $this->plugin_path . $this->local_filenames['ga'];
		$this->update_local_files( $remote_file, $local_file );

		$gmap_key = $this->get_option( 'gmap_key' );
		$key      = '';
		if ( '' !== $gmap_key ) {
			$key = '?key=' . $gmap_key;
		}
		$remote_file = $this->remote_filenames['gmap'] . $key;
		$local_file  = $this->plugin_path . $this->local_filenames['gmap'];
		$this->update_local_files( $remote_file, $local_file );

		$remote_file = $this->remote_filenames['ya_metrika'];
		$local_file  = $this->plugin_path . $this->local_filenames['ya_metrika'];
		$this->update_local_files( $remote_file, $local_file );
	}

	/**
	 * Update local file.
	 *
	 * @param string $remote_file Remote file url.
	 * @param string $local_file Local file name.
	 */
	private function update_local_files( $remote_file, $local_file ) {
		$args   = array(
			'method'      => 'GET',
			'redirection' => 1,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(),
			'body'        => array(),
			'cookies'     => array(),
		);
		$result = wp_remote_post( $remote_file, $args );

		if ( ! is_wp_error( $result ) ) {
			$response = $result['body'];
			if ( ! empty( $response ) ) {
				// @codingStandardsIgnoreStart
				// Save the response to the local file
				if ( ! file_exists( $local_file ) ) {
					// Try to create the file, if doesn't exist
					fopen( $local_file, 'w' );
					fclose( $local_file );
				}

				if ( is_writable( $local_file ) ) {
					$fp = fopen( $local_file, 'w' );
					if ( $fp ) {
						fwrite( $fp, $response );
						fclose( $fp );
					}
				}
				// @codingStandardsIgnoreEnd
			}
		}
	}

	/**
	 * Add event to WP-Cron and check local files.
	 */
	public function activate_update_pagespeed_optimization_cache() {
		if ( ! wp_next_scheduled( 'update_pagespeed_optimization_cache' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'update_pagespeed_optimization_cache' );
			$this->check_local_files();
		}
	}

	/**
	 * Remove event from WP-Cron.
	 */
	public function deactivate_update_pagespeed_optimization_cache() {
		if ( wp_next_scheduled( 'update_pagespeed_optimization_cache' ) ) {
			wp_clear_scheduled_hook( 'update_pagespeed_optimization_cache' );
		}
	}

	/**
	 * Add cached scripts to site.
	 */
	public function print_scripts_action() {
		static $done = false;

		if ( $done ) {
			// wp_print_scripts action can be called several times.
			return;
		}

		$done = true;

		$ga_id                    = $this->get_option( 'ga_id' );
		$ya_metrika_id            = $this->get_option( 'ya_metrika_id' );
		$bounce_rate              = $this->get_option( 'bounce_rate' );
		$disable_display_features = $this->get_option( 'disable_display_features' );
		$anonymize_ip             = $this->get_option( 'anonymize_ip' );

		// Google Analytics script.
		if ( $ga_id ) {
			echo "<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','" . esc_url( $this->plugin_url . $this->local_filenames['ga'] ) . "','ga');";

			echo "\nga('create', '" . esc_html( $ga_id ) . "', 'auto');";

			$disable_display_features_code = ( 'yes' === $disable_display_features ) ? "ga('set', 'displayFeaturesTask', null);" : '';
			echo esc_html( "\n" . $disable_display_features_code );

			$anonymize_ip_code = ( 'on' === $anonymize_ip ) ? "ga('set', 'anonymizeIp', true);" : '';
			echo esc_html( "\n" . $anonymize_ip_code );

			echo "\nga('send', 'pageview');";

			$bounce_rate_code = ( $bounce_rate ) ? 'setTimeout("ga(' . "'send','event','adjusted bounce rate','" . $bounce_rate . " seconds')" . '",' . $bounce_rate * 1000 . ');' : '';
			echo esc_html( "\n" . $bounce_rate_code );

			echo "\n" . '</script>' . "\n";
		}

		if ( $ya_metrika_id ) {
			// Yandex Metrika script.
			?>
			<!-- Yandex.Metrika counter -->
			<script type="text/javascript">
				(function (d, w, c) {
					(w[c] = w[c] || []).push(function () {
						try {
							w.yaCounter<?php echo esc_html( $ya_metrika_id ); ?> = new Ya.Metrika({
								id:<?php echo esc_html( $ya_metrika_id ); ?>,
								enableAll: true,
								webvisor: true
							});
						} catch (e) {
						}
					});

					var n = d.getElementsByTagName("script")[0],
						s = d.createElement("script"),
						f = function () {
							n.parentNode.insertBefore(s, n);
						};
					s.type = "text/javascript";
					s.async = true;
					// s.src = "https://mc.yandex.ru/metrika/watch.js";
					s.src = "<?php echo esc_url( $this->plugin_url . $this->local_filenames['ya_metrika'] ); ?>";

					if (w.opera == "[object Opera]") {
						d.addEventListener("DOMContentLoaded", f, false);
					} else {
						f();
					}
				})(document, window, "yandex_metrika_callbacks");
			</script>
			<noscript>
				<div><img
							src="//mc.yandex.ru/watch/<?php echo esc_html( $ya_metrika_id ); ?>"
							style="position:absolute; left:-9999px;" alt=""/>
				</div>
			</noscript>
			<!-- /Yandex.Metrika counter -->
			<?php

		}
	}

	/**
	 * Prevent Google Maps from loading Roboto font.
	 */
	public function print_prevent_gmap_roboto() {
		static $done = false;

		if ( $done ) {
			// wp_print_scripts action can be called several times.
			return;
		}

		$done = true;

		$gmap_key = $this->get_option( 'gmap_key' );
		if ( ! $gmap_key ) {
			return;
		}

		if ( 'yes' !== $this->get_option( 'prevent_gmap_roboto' ) ) {
			return;
		}

		?>
		<script type="text/javascript">
			var head = document.getElementsByTagName('head')[0];

			// Save the original method
			var insertBefore = head.insertBefore;

			// Replace it!
			head.insertBefore = function (newElement, referenceElement) {

				if (newElement.href && newElement.href.indexOf('//fonts.googleapis.com/css?family=Roboto') > -1) {

					return;
				}

				insertBefore.call(head, newElement, referenceElement);
			};
		</script>
		<?php
	}

	/**
	 * Enqueue cached scripts.
	 */
	public function enqueue_scripts_action() {
		$gmap_key = $this->get_option( 'gmap_key' );
		if ( ! $gmap_key ) {
			return;
		}

		// $script = 'https://maps.googleapis.com/maps/api/js';
		$script = $this->plugin_url . $this->local_filenames['gmap'];

		$script .= '?key=' . $gmap_key;
		if ( 'header' === $this->get_option( 'position' ) ) {
			$in_footer = false;
		} else {
			$in_footer = true;
		}
		wp_enqueue_script( 'pagespeed-optimization-google-maps', $script, array(), null, $in_footer );
	}

	/**
	 * Enqueue plugin scripts.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'pagespeed-optimization-admin', $this->plugin_url . 'css/pagespeed-optimization-admin.css', array(), $this->version );
	}
}
