<?php
/**
 * Settings class.
 *
 * @package Nilambar\AiProviderForGithubModels
 */

declare( strict_types=1 );

namespace Nilambar\AiProviderForGithubModels;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Exception;
use Nilambar\AiProviderForGithubModels\Provider\GitHubModelsProvider;
use WordPress\AiClient\AiClient;

/**
 * Settings class.
 *
 * @since 1.0.0
 */
class Settings {

	const OPTION_NAME = 'ai_provider_github_models_default_text_model';
	const AJAX_ACTION = 'ai_provider_github_models_get_models';
	const NONCE_KEY   = 'ai_provider_github_models_get_models';

	/**
	 * Initializes settings hooks.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {
		add_action( 'admin_menu', [ $this, 'add_options_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'ajax_get_models' ] );
	}

	/**
	 * Registers the options page.
	 *
	 * @since 1.0.0
	 */
	public function add_options_page(): void {
		add_options_page(
			_x( 'GitHub Models Settings', 'page title', 'ai-provider-for-github-models' ),
			_x( 'GitHub Models', 'menu title', 'ai-provider-for-github-models' ),
			'manage_options',
			'ai-provider-for-github-models',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Registers settings, sections, and fields.
	 *
	 * @since 1.0.0
	 */
	public function register_settings(): void {
		register_setting(
			'ai_provider_for_github_models',
			self::OPTION_NAME,
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			]
		);

		add_settings_section(
			'ai_provider_for_github_models_general',
			'',
			'__return_false',
			'ai-provider-for-github-models'
		);

		add_settings_field(
			'default_text_model',
			__( 'Default Text Model', 'ai-provider-for-github-models' ),
			[ $this, 'render_default_text_model_field' ],
			'ai-provider-for-github-models',
			'ai_provider_for_github_models_general'
		);
	}

	/**
	 * Enqueues scripts on the settings page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_scripts( string $hook_suffix ): void {
		if ( 'settings_page_ai-provider-for-github-models' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_script(
			'ai-provider-github-models-settings',
			plugin_dir_url( AI_PROVIDER_FOR_GITHUB_MODELS_BASE_FILEPATH ) . 'assets/js/settings.js',
			[],
			AI_PROVIDER_FOR_GITHUB_MODELS_VERSION,
			true
		);

		wp_localize_script(
			'ai-provider-github-models-settings',
			'aiProviderGithubModels',
			[
				'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( self::NONCE_KEY ),
				'currentValue'    => get_option( self::OPTION_NAME, '' ),
				'noOverrideLabel' => __( '— Default —', 'ai-provider-for-github-models' ),
			]
		);
	}

	/**
	 * Renders the Default Text Model select field.
	 *
	 * @since 1.0.0
	 */
	public function render_default_text_model_field(): void {
		?>
		<select
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>"
			id="<?php echo esc_attr( self::OPTION_NAME ); ?>"
			disabled
		>
			<option value=""><?php esc_html_e( 'Loading models…', 'ai-provider-for-github-models' ); ?></option>
		</select>
		<span id="ai-provider-github-models-error" style="display:none;color:#d63638;"></span>
		<?php
	}

	/**
	 * Renders the settings page.
	 *
	 * @since 1.0.0
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<div class="notice notice-info inline">
				<p>
					<?php
					printf(
						wp_kses(
							/* translators: %s: URL of the Connectors settings page */
							__( 'To use GitHub Models, add your API key on the <a href="%s">Connectors</a> page.', 'ai-provider-for-github-models' ),
							[ 'a' => [ 'href' => [] ] ]
						),
						esc_url( admin_url( 'options-connectors.php' ) )
					);
					?>
				</p>
			</div>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'ai_provider_for_github_models' );
				do_settings_sections( 'ai-provider-for-github-models' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * AJAX handler: returns the list of model IDs as JSON.
	 *
	 * @since 1.0.0
	 */
	public function ajax_get_models(): void {
		check_ajax_referer( self::NONCE_KEY, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'ai-provider-for-github-models' ), 403 );
		}

		if ( ! class_exists( AiClient::class ) ) {
			wp_send_json_error( __( 'AI Client is not available.', 'ai-provider-for-github-models' ) );
		}

		$registry = AiClient::defaultRegistry();
		if ( ! $registry->hasProvider( GitHubModelsProvider::class ) || ! AiClient::isConfigured( GitHubModelsProvider::class ) ) {
			wp_send_json_error( [ 'code' => 'not_configured' ] );
		}

		try {
			$models_metadata = GitHubModelsProvider::modelMetadataDirectory()->listModelMetadata();
			$model_ids       = array_map( fn( $metadata ) => $metadata->getId(), $models_metadata );
			wp_send_json_success( $model_ids );
		} catch ( Exception $e ) {
			wp_send_json_error( [ 'code' => 'api_error' ] );
		}
	}
}
