<?php
/**
 * Main class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class Main.
 */
class Main {

	/**
	 * The plugin ID. Used for option names.
	 *
	 * @var string
	 */
	public $plugin_id = 'pagespeed_optimization_';

	/**
	 * ID of the class extending the settings. Used in option names.
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	public $form_fields;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	public $settings;

	/**
	 * Remote script file names.
	 *
	 * @var array
	 */
	private $remote_urls = [
		'ga'         => 'https://www.google-analytics.com/analytics.js',
		'gmap'       => 'https://maps.googleapis.com/maps/api/js',
		'ya_metrika' => 'https://mc.yandex.ru/metrika/watch.js',
		'ya_an'      => '//an.yandex.ru/system/context.js',
	];

	/**
	 * Cache file names.
	 *
	 * @var array
	 */
	private $local_filenames = [
		'ga'         => 'cache/ga.js',
		'gmap'       => 'cache/gmap.js',
		'ya_metrika' => 'cache/ya_metrika.js',
		'ya_an'      => 'cache/ya_an.js',
	];

	/**
	 * Options corresponding to the services.
	 *
	 * @var string[]
	 */
	private $service_options = [
		'ga'         => 'ga_id',
		'gmap'       => 'gmap_key',
		'ya_metrika' => 'ya_metrika_id',
		'ya_an'      => '', // @todo: Implement option.
	];

	/**
	 * PageSpeed_Optimization constructor.
	 */
	public function __construct() {
		// Init fields.
		$this->init_form_fields();
		$this->init_settings();

		$this->init_hooks();

		if ( is_admin() ) {
			return;
		}

		if ( ! function_exists( 'is_user_logged_in' ) ) {
			require_once ABSPATH . 'wp-includes/pluggable.php';
		}

		if ( is_user_logged_in() && 'yes' !== $this->get_option( 'optimize_logged_in' ) ) {
			return;
		}

		new Resources( $this );
		new Loader( $this );
		new YandexAdvertisingNetwork( $this );
		new PassiveEvents();
		new LayerSlider();
		new Medusa();
		new Zopim();
		new FBShareLikeButton();
		new StatCounter();
	}

	/**
	 * Init hooks.
	 */
	private function init_hooks() {
		add_filter(
			'plugin_action_links_' . plugin_basename( KAGG_PAGESPEED_OPTIMIZATION_FILE ),
			[ $this, 'add_settings_link' ],
			10,
			2
		);

		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'setup_sections' ] );
		add_action( 'admin_init', [ $this, 'setup_fields' ] );

		add_filter( 'pre_update_option_' . $this->get_option_key(), [ $this, 'pre_update_option_filter' ], 10, 3 );

		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ], 100 );
		add_action( 'plugins_loaded', [ $this, 'check_cron' ], 100 );
		add_action(
			'update_pagespeed_optimization_cache',
			[ $this, 'update_pagespeed_optimization_cache_action' ]
		);

		$enqueue_priority = $this->get_option( 'enqueue_priority' );
		if ( 'header' === $this->get_option( 'position' ) ) {
			add_action( 'wp_print_scripts', [ $this, 'print_scripts_action' ], $enqueue_priority );
		} else {
			add_action( 'wp_print_footer_scripts', [ $this, 'print_scripts_action' ], $enqueue_priority );
		}
		add_action( 'wp_print_scripts', [ $this, 'print_prevent_gmap_roboto' ], 0 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts_action' ], $enqueue_priority );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		// Block emoji.
		add_action( 'init', [ $this, 'block_emoji' ] );

		// Register activation hook to schedule event in wp_cron().
		register_activation_hook(
			KAGG_PAGESPEED_OPTIMIZATION_FILE,
			[
				$this,
				'activate_update_pagespeed_optimization_cache',
			]
		);

		// Register deactivation hook to remove event from wp_cron().
		register_deactivation_hook(
			KAGG_PAGESPEED_OPTIMIZATION_FILE,
			[
				$this,
				'deactivate_update_pagespeed_optimization_cache',
			]
		);

		add_filter( 'aiosp_google_analytics', [ $this, 'replace_urls' ] );
	}

	/**
	 * Add settings page to the menu.
	 */
	public function add_settings_page() {
		$page_title = __( 'KAGG PageSpeed Optimization', 'kagg-pagespeed-optimization' );
		$menu_title = __( 'KAGG PageSpeed', 'kagg-pagespeed-optimization' );
		$capability = 'manage_options';
		$slug       = 'pagespeed-optimization';
		$callback   = [ $this, 'pagespeed_optimization_settings_page' ];
		$icon       = KAGG_PAGESPEED_OPTIMIZATION_URL . '/assets/images/icon-16x16.png';
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
				echo( esc_html( __( 'KAGG PageSpeed Optimization', 'kagg-pagespeed-optimization' ) ) );
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
		add_settings_section(
			'first_section',
			__( 'Options', 'kagg-pagespeed-optimization' ),
			[ $this, 'pagespeed_optimization_first_section' ],
			'pagespeed-optimization'
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
				?>
				<p>
					<?php
					echo esc_html__(
						'Fill out IDs and key below to cache scripts locally and follow "Leverage browser caching" suggestion by Google PageSpeed Insights.',
						'kagg-pagespeed-optimization'
					);
					?>
				</p>
				<p>
					<?php
					echo esc_html__(
						'You can use other options for fine tuning.',
						'kagg-pagespeed-optimization'
					);
					?>
				</p>
				<?php
				break;
			default:
		}
	}

	/**
	 * Init options form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'gas_id'                   => [
				'label'        => __( 'Google AdSense ID', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'text',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
			],
			'ga_id'                    => [
				'label'        => __( 'Google Analytics tracking ID', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'text',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
			],
			'gmap_key'                 => [
				'label'        => __( 'Google Maps API key', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'text',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
			],
			'gtag_id'                  => [
				'label'        => __( 'Google Tag Manager ID', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'text',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
			],
			'ya_metrika_id'            => [
				'label'        => __( 'Yandex Metrika tracking ID', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'text',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
			],
			'position'                 => [
				'label'        => __( 'Position of tracking code', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'radio',
				'options'      => [
					'header' => 'Header',
					'footer' => 'Footer',
				],
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => 'header',
			],
			'bounce_rate'              => [
				'label'        => __( 'Adjusted bounce rate', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'text',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
			],
			'enqueue_priority'         => [
				'label'        => __( 'Enqueue priority', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'text',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => '10',
			],
			'disable_display_features' => [
				'label'        => __( 'Disable display features functionality', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'checkbox',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => 'no',
			],
			'anonymize_ip'             => [
				'label'        => __( 'Anonymize IP', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'checkbox',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => 'yes',
			],
			'prevent_gmap_roboto'      => [
				'label'        => __( 'Prevent Google Maps from loading Roboto font', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'checkbox',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => 'yes',
			],
			'remove_from_wp_cron'      => [
				'label'        => __( 'Remove script from WP-Cron', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'checkbox',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => 'no',
			],
			'optimize_logged_in'       => [
				'label'        => __( 'Optimize when logged-in', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'checkbox',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
				'default'      => 'no',
			],
			'loader_image_url'         => [
				'label'        => __( 'Loader image URL', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'text',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => '',
			],
			'scripts_to_footer'        => [
				'label'        => __( 'Scripts to move from header to footer', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'textarea',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => __( 'Enter handles, one per line.', 'kagg-pagespeed-optimization' ),
				'default'      => [],
			],
			'block_scripts'            => [
				'label'        => __( 'Scripts to block', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'textarea',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => __( 'Enter handles, one per line.', 'kagg-pagespeed-optimization' ),
				'default'      => [],
			],
			'delay_scripts'            => [
				'label'        => __( 'Scripts to delay', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'textarea',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => __( 'Enter handles, one per line.', 'kagg-pagespeed-optimization' ),
				'default'      => [],
			],
			'styles_to_footer'         => [
				'label'        => __( 'Styles to move from header to footer', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'textarea',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => __( 'Enter handles, one per line.', 'kagg-pagespeed-optimization' ),
				'default'      => [],
			],
			'block_styles'             => [
				'label'        => __( 'Styles to block', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'textarea',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => __( 'Enter handles, one per line.', 'kagg-pagespeed-optimization' ),
				'default'      => [],
			],
			'links_to_preload'         => [
				'label'        => __( 'Links to preload', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'textarea',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => __( 'Enter URLs, one per line.', 'kagg-pagespeed-optimization' ),
				'default'      => [],
			],
			'fonts_to_preload'         => [
				'label'        => __( 'Fonts to preload', 'kagg-pagespeed-optimization' ),
				'section'      => 'first_section',
				'type'         => 'textarea',
				'placeholder'  => '',
				'helper'       => '',
				'supplemental' => __( 'Enter css urls containing @font-face directives, one per line.', 'kagg-pagespeed-optimization' ),
				'default'      => [],
			],
		];
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
			$this->settings = array_merge(
				array_fill_keys( array_keys( $form_fields ), '' ),
				wp_list_pluck( $form_fields, 'default' )
			);
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

		return array_map( [ $this, 'set_defaults' ], $this->form_fields );
	}

	/**
	 * Set default required properties for each field.
	 *
	 * @param array $field Field.
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
	 * @param string $key         Key.
	 * @param mixed  $empty_value Empty value.
	 *
	 * @return mixed The value specified for the option or a default value for the option.
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
	 * @param array $field Field.
	 *
	 * @return string
	 */
	public function get_field_default( $field ) {
		return empty( $field['default'] ) ? '' : $field['default'];
	}

	/**
	 * Filter plugin option update.
	 *
	 * @param mixed  $value     New option value.
	 * @param mixed  $old_value Old option value.
	 * @param string $option    Option name.
	 *
	 * @return mixed
	 */
	public function pre_update_option_filter( $value, $old_value, $option ) {
		if ( $value === $old_value ) {
			return $value;
		}

		$form_fields = $this->get_form_fields();
		foreach ( $form_fields as $key => $form_field ) {
			$value[ $key ] = isset( $value[ $key ] ) ? $value[ $key ] : $form_fields[ $key ];
			switch ( $form_field['type'] ) {
				case 'checkbox':
					$value[ $key ] = '1' === $value[ $key ] || 'yes' === $value[ $key ] ? 'yes' : 'no';
					break;
				default:
			}
		}

		$value = $this->generate_font_css( $value, $old_value );

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
				$key,
				$field['label'],
				[ $this, 'field_callback' ],
				'pagespeed-optimization',
				$field['section'],
				$field
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
				printf(
					'<input name="%1$s[%2$s]" id="%2$s" type="%3$s" placeholder="%4$s" value="%5$s" class="regular-text" />',
					esc_html( $this->get_option_key() ),
					esc_attr( $arguments['field_id'] ),
					esc_attr( $arguments['type'] ),
					esc_attr( $arguments['placeholder'] ),
					esc_html( $value )
				);
				break;
			case 'textarea':
				printf(
					'<textarea name="%1$s[%2$s]" id="%2$s" placeholder="%3$s" rows="5" cols="50">%4$s</textarea>',
					esc_html( $this->get_option_key() ),
					esc_attr( $arguments['field_id'] ),
					esc_attr( $arguments['placeholder'] ),
					wp_kses_post( $value )
				);
				break;
			case 'checkbox':
			case 'radio':
				if ( 'checkbox' === $arguments['type'] ) {
					$arguments['options'] = [ 'yes' => '' ];
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
					printf(
						'<fieldset>%s</fieldset>',
						wp_kses(
							$options_markup,
							[
								'label' => [
									'for' => [],
								],
								'input' => [
									'id'      => [],
									'name'    => [],
									'type'    => [],
									'value'   => [],
									'checked' => [],
								],
								'br'    => [],
							]
						)
					);
				}
				break;
			case 'select': // If it is a select dropdown.
				if ( ! empty( $arguments['options'] ) && is_array( $arguments['options'] ) ) {
					$options_markup = '';
					foreach ( $arguments['options'] as $key => $label ) {
						$options_markup .= sprintf(
							'<option value="%s" %s>%s</option>',
							$key,
							selected( $value, $key, false ),
							$label
						);
					}
					printf(
						'<select name="%1$s[%2$s]">%3$s</select>',
						esc_html( $this->get_option_key() ),
						esc_html( $arguments['field_id'] ),
						wp_kses(
							$options_markup,
							[
								'option' => [
									'value'    => [],
									'selected' => [],
								],
							]
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
						$options_markup .= sprintf(
							'<option value="%s" %s>%s</option>',
							$key,
							$selected,
							$label
						);
					}
					printf(
						'<select multiple="multiple" name="%1$s[%2$s][]">%3$s</select>',
						esc_html( $this->get_option_key() ),
						esc_html( $arguments['field_id'] ),
						wp_kses(
							$options_markup,
							[
								'option' => [
									'value'    => [],
									'selected' => [],
								],
							]
						)
					);
				}
				break;
			default:
		}

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
		load_plugin_textdomain(
			'kagg-pagespeed-optimization',
			false,
			plugin_basename( KAGG_PAGESPEED_OPTIMIZATION_PATH ) . '/languages/'
		);
	}

	/**
	 * Add link to plugin setting page on plugins page.
	 *
	 * @param array  $links Plugin links.
	 * @param string $file  Filename.
	 *
	 * @return array|mixed Plugin links
	 */
	public function add_settings_link( $links, $file ) {
		$action_links = [
			'settings' =>
				'<a href="' . admin_url( 'options-general.php?page=pagespeed-optimization' ) .
				'" aria-label="' .
				esc_attr__( 'View PageSpeed Module settings', 'kagg-pagespeed-optimization' ) .
				'">' .
				esc_html__( 'Settings', 'kagg-pagespeed-optimization' ) . '</a>',
		];

		return array_merge( $action_links, $links );
	}

	/**
	 * Check cron status.
	 */
	public function check_cron() {
		// @todo Add selection of interval to options.
		if ( 'yes' === $this->get_option( 'remove_from_wp_cron' ) ) {
			$this->deactivate_update_pagespeed_optimization_cache();
		} else {
			$this->activate_update_pagespeed_optimization_cache();
		}
	}

	/**
	 * Update scripts cache.
	 */
	public function update_pagespeed_optimization_cache_action() {
		$filesystem = new Filesystem();

		foreach ( $this->remote_urls as $service => $remote_filename ) {
			$option = $this->get_option( $this->service_options[ $service ] );
			if ( ! $option ) {
				continue;
			}

			$key = '';
			if ( 'gmap' === $service ) {
				$key = '?key=' . $option;
			}

			$remote_file = $this->remote_urls[ $service ] . $key;
			$local_file  = KAGG_PAGESPEED_OPTIMIZATION_PATH . '/' . $this->local_filenames[ $service ];
			$this->update_local_file( $filesystem, $remote_file, $local_file );
		}
	}

	/**
	 * Update local file.
	 *
	 * @param Filesystem $filesystem  Filesystem.
	 * @param string     $remote_file Remote file url.
	 * @param string     $local_file  Local file name.
	 */
	private function update_local_file( $filesystem, $remote_file, $local_file ) {
		$args = [
			'method'      => 'GET',
			'redirection' => 1,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => [],
			'body'        => [],
			'cookies'     => [],
		];

		$remote_file_arr = wp_parse_url( $remote_file );
		if ( ! isset( $remote_file_arr['schema'] ) ) {
			$remote_file_arr['schema'] = is_ssl() ? 'https' : 'http';
		}
		$remote_file = $remote_file_arr['schema'] . '://' . $remote_file_arr['host'] . $remote_file_arr['path'];

		$result = wp_remote_get( $remote_file, $args );

		if ( is_wp_error( $result ) ) {
			return;
		}

		$content = $result['body'];
		if ( empty( $content ) ) {
			return;
		}

		$local_content = $filesystem->read( $local_file );
		if ( $local_content === $content ) {
			return;
		}

		$dirname = pathinfo( $local_file, PATHINFO_DIRNAME );
		wp_mkdir_p( $dirname );

		$filesystem->write( $local_file, $content );
		$this->clean_cache();
	}

	/**
	 * Clean cache.
	 */
	private function clean_cache() {
		// Clean cache of WP Super Cache plugin.
		if ( function_exists( 'wp_cache_clean_cache' ) ) {
			global $file_prefix;
			wp_cache_clean_cache( $file_prefix, true );

			return;
		}
	}

	/**
	 * Add event to WP-Cron and check local files.
	 */
	public function activate_update_pagespeed_optimization_cache() {
		if ( ! wp_next_scheduled( 'update_pagespeed_optimization_cache' ) ) {
			wp_schedule_event( time(), 'hourly', 'update_pagespeed_optimization_cache' );
			do_action( 'update_pagespeed_optimization_cache' );
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

		$gas_id                   = $this->get_option( 'gas_id' );
		$ga_id                    = $this->get_option( 'ga_id' );
		$gtag_id                  = $this->get_option( 'gtag_id' );
		$ya_metrika_id            = $this->get_option( 'ya_metrika_id' );
		$bounce_rate              = $this->get_option( 'bounce_rate' );
		$disable_display_features = $this->get_option( 'disable_display_features' );
		$anonymize_ip             = $this->get_option( 'anonymize_ip' );

		if ( $gas_id ) {
			DelayedScript::launch(
				[
					'src'  => 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js',
					'data' => [
						'adClient' => esc_attr( $gas_id ),
					],
				],
				5000
			);
		}

		// Google Analytics script.
		if ( $ga_id ) {
			$disable_display_features_code = ( 'yes' === $disable_display_features ) ? "ga('set', 'displayFeaturesTask', null);" : '';
			$anonymize_ip_code             = ( 'on' === $anonymize_ip ) ? "ga('set', 'anonymizeIp', true);" : '';
			$bounce_rate_code              = $bounce_rate ? 'setTimeout("ga(' . "'send','event','adjusted bounce rate','" . $bounce_rate . " seconds')" . '",' . $bounce_rate * 1000 . ');' : '';

			ob_start();

			?>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','<?php echo esc_url( KAGG_PAGESPEED_OPTIMIZATION_URL . '/' . $this->local_filenames['ga'] ); ?>','ga');
			ga('create', '<?php echo esc_html( $ga_id ); ?>', 'auto');
			<?php echo esc_html( $disable_display_features_code ); ?>
			<?php echo esc_html( $anonymize_ip_code ); ?>
			ga('send', 'pageview');
			<?php echo esc_html( $bounce_rate_code ); ?>
			<?php

			$js     = ob_get_clean();
			$script = DelayedScript::create( $js );

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "\n" . $script . "\n";
		}

		if ( $gtag_id ) {
			?>
			<!-- Global site tag (gtag.js) - Google Analytics -->
			<?php
			DelayedScript::launch(
				[ 'src' => 'https://www.googletagmanager.com/gtag/js?id=' . $gtag_id ]
			);
			?>
			<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
			<script>
				window.dataLayer = window.dataLayer || [];

				function gtag() {
					dataLayer.push( arguments );
				}

				gtag( 'js', new Date() );

				gtag( 'config', '<?php echo esc_html( $gtag_id ); ?>' );
			</script>
			<?php
		}

		// Yandex Metrika script.
		if ( $ya_metrika_id ) {
			ob_start();

			?>
			(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
			m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
			(window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

			ym(<?php echo esc_html( $ya_metrika_id ); ?>, "init", {
			clickmap:true,
			trackLinks:true,
			accurateTrackBounce:true
			});
			<?php

			$js     = ob_get_clean();
			$script = DelayedScript::create( $js );

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "\n" . $script . "\n";

			?>
			<noscript><div><img src="https://mc.yandex.ru/watch/<?php echo esc_html( $ya_metrika_id ); ?>" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
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
			var head = document.getElementsByTagName( 'head' )[ 0 ];

			// Save the original method
			var insertBefore = head.insertBefore;

			// Replace it!
			head.insertBefore = function( newElement, referenceElement ) {

				if ( newElement.href && newElement.href.indexOf( '//fonts.googleapis.com/css?family=Roboto' ) > -1 ) {

					return;
				}

				insertBefore.call( head, newElement, referenceElement );
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

		$script = KAGG_PAGESPEED_OPTIMIZATION_URL . '/' . $this->local_filenames['gmap'];

		$script .= '?key=' . $gmap_key;
		if ( 'header' === $this->get_option( 'position' ) ) {
			$in_footer = false;
		} else {
			$in_footer = true;
		}

		wp_enqueue_script(
			'pagespeed-optimization-google-maps',
			$script,
			[],
			KAGG_PAGESPEED_OPTIMIZATION_VERSION,
			$in_footer
		);
	}

	/**
	 * Enqueue plugin scripts.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style(
			'pagespeed-optimization-admin',
			KAGG_PAGESPEED_OPTIMIZATION_URL . '/assets/css/admin.css',
			[],
			KAGG_PAGESPEED_OPTIMIZATION_VERSION
		);
	}

	/**
	 * Block emoji
	 */
	public function block_emoji() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'embed_head', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		add_filter( 'tiny_mce_plugins', [ $this, 'disable_emojis_tinymce' ] );
		add_filter( 'wp_resource_hints', [ $this, 'disable_emojis_remove_dns_prefetch' ], 10, 2 );
	}

	/**
	 * Filter function used to remove the tinymce emoji plugin.
	 *
	 * @param array $plugins Plugins.
	 *
	 * @return array Difference between the two arrays
	 */
	public function disable_emojis_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, [ 'wpemoji' ] );
		}

		return [];
	}

	/**
	 * Remove emoji CDN hostname from DNS prefetching hints.
	 *
	 * @param array  $urls          URLs to print for resource hints.
	 * @param string $relation_type The relation type the URLs are printed for.
	 *
	 * @return array Difference between the two arrays.
	 */
	public function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' === $relation_type ) {
			// This filter is documented in wp-includes/formatting.php.
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );

			$urls = array_diff( $urls, [ $emoji_svg_url ] );
		}

		return $urls;
	}

	/**
	 * Filter code html and replace remote url by local.
	 *
	 * @param string $html Html code.
	 *
	 * @return string
	 */
	public function replace_urls( $html ) {
		$local_filenames = array_map(
			static function ( $item ) {
				return KAGG_PAGESPEED_OPTIMIZATION_URL . '/' . $item;
			},
			$this->local_filenames
		);

		$html = str_replace(
			$this->remote_urls,
			$local_filenames,
			$html
		);

		return $html;
	}

	/**
	 * Generate inline css for fonts.
	 *
	 * @param mixed $value     New option value.
	 * @param mixed $old_value Old option value.
	 *
	 * @return mixed
	 */
	private function generate_font_css( $value, $old_value ) {
		if ( $value['fonts_to_preload'] === $old_value['fonts_to_preload'] ) {
			$value['_fonts_preload_links'] = $old_value['_fonts_preload_links'];
			$value['_fonts_generated_css'] = $old_value['_fonts_generated_css'];

			return $value;
		}

		$swap = 'font-display: swap;';

		$font_face_formats_to_types = [
			'otf'      => 'font/otf',
			'truetype' => 'font/ttf',
			'woff'     => 'font/woff',
			'woff2'    => 'font/woff2',
		];

		$css_urls = array_unique( array_filter( explode( "\n", $value['fonts_to_preload'] ), 'trim' ) );

		$value['fonts_to_preload'] = implode( "\n", $css_urls );

		$generated_css = [];
		$links         = [];

		foreach ( $css_urls as $css_url ) {
			$args     = [ 'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36' ];
			$response = wp_remote_get( $css_url, $args );

			$css = wp_remote_retrieve_body( $response );

			if ( '' === $css ) {
				continue;
			}

			$result = preg_match_all( '#(?:/\*.*\*/\s*)?@font-face\s*{[\s\S]*?}#i', $css, $matches );

			if ( 0 === (int) $result ) {
				continue;
			}

			foreach ( $matches[0] as $font_face ) {
				$url_result = preg_match_all(
					'/url\s*\(([^)]+)\)(?: format\s*\(([^)]+)\)\s*)?\s*,?/i',
					$font_face,
					$m
				);

				for ( $i = 0; $i < $url_result; $i ++ ) {
					$url    = trim( $m[1][ $i ], "'\"" );
					$format = trim( $m[2][ $i ], "'\"" );

					// @todo: Add option to select font formats to preload.
					if ( ( 'woff' !== $format ) && ( 'woff2' !== $format ) ) {
						$font_face = str_replace( $m[0][ $i ], '', $font_face );
						continue;
					}

					$type = isset( $font_face_formats_to_types[ $format ] ) ?
						'type="' . $font_face_formats_to_types[ $format ] . '" ' :
						'';

					$absolute_url = $this->absolute_url( $url, $css_url );
					$links[]      = '<link rel="preload" href="' . $absolute_url . '" as="font" ' . $type . 'crossorigin="anonymous">';

					$font_face = str_replace( $url, $absolute_url, $font_face );
				}

				if ( false === strpos( $font_face, $swap ) ) {
					$font_face = preg_replace( '/;\s*}/', '}', $font_face );
					$font_face = str_replace( '}', ";\n" . $swap . "\n}", $font_face );
				}

				$font_face       = preg_replace( '/,\s*;/', ';', $font_face );
				$generated_css[] = preg_replace( '/src\s*:\s*;/i', '', $font_face );
			}
		}

		$value['_fonts_preload_links'] = implode( "\n", array_unique( $links ) );
		$value['_fonts_generated_css'] = implode( "\n", $generated_css );

		return $value;
	}

	/**
	 * Get absolute url.
	 *
	 * @param string $relative_url Url relative to main.
	 * @param string $base_url     Base absolute url.
	 *
	 * @return string
	 */
	private function absolute_url( $relative_url, $base_url ) {
		// Return if already absolute URL.
		if ( wp_parse_url( $relative_url, PHP_URL_SCHEME ) !== null ) {
			return $relative_url;
		}

		// Parse base URL and convert to local variables: $scheme, $host, $path.
		$base_url_arr = wp_parse_url( $base_url );
		$scheme       = $base_url_arr['scheme'];
		$host         = $base_url_arr['host'];
		$path         = $base_url_arr['path'];

		// Url begins with //.
		if ( 0 === strpos( $relative_url, '//' ) ) {
			return $scheme . ':' . $relative_url;
		}

		// Queries and anchors.
		if ( '#' === $relative_url[0] || '?' === $relative_url[0] ) {
			return $base_url . $relative_url;
		}

		// Remove non-directory element from path.
		$path = preg_replace( '#/[^/]*$#', '', $path );

		// Destroy path if relative url points to root.
		if ( '/' === $relative_url[0] ) {
			$path = '';
		}

		// Preliminary absolute URL.
		$absolute_url = "$host$path/$relative_url";

		// Replace '//' or '/./' or '/foo/../' with '/'.
		$pattern = [ '#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#' ];
		do {
			$absolute_url = preg_replace( $pattern, '/', $absolute_url, - 1, $n );
		} while ( $n > 0 );

		return $scheme . '://' . $absolute_url;
	}
}
