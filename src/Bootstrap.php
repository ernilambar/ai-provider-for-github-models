<?php
/**
 * Bootstrap class.
 *
 * @package Nilambar\AiProviderForGithubModels
 */

declare( strict_types=1 );

namespace Nilambar\AiProviderForGithubModels;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Nilambar\AiProviderForGithubModels\Provider\GitHubModelsProvider;
use WordPress\AiClient\AiClient;

/**
 * Bootstrap class.
 *
 * @since 1.0.0
 */
class Bootstrap {

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {
		add_action( 'init', [ $this, 'register_provider' ], 5 );
		add_filter( 'plugin_action_links_' . AI_PROVIDER_FOR_GITHUB_MODELS_BASE_FILENAME, [ $this, 'plugin_action_links' ] );

		( new Settings() )->init();
	}

	/**
	 * Adds a Settings link to the plugin row.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $links Existing action links.
	 * @return array<string> Modified action links.
	 */
	public function plugin_action_links( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'options-connectors.php' ),
			esc_html__( 'Settings', 'ai-provider-for-github-models' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Registers the provider with the AI Client.
	 *
	 * @since 1.0.0
	 */
	public function register_provider(): void {
		if ( ! class_exists( AiClient::class ) ) {
			return;
		}

		$registry = AiClient::defaultRegistry();

		if ( $registry->hasProvider( GitHubModelsProvider::class ) ) {
			return;
		}

		$registry->registerProvider( GitHubModelsProvider::class );
	}
}
