<?php
/**
 * Plugin Name:       AI Provider for GitHub Models
 * Plugin URI:        https://github.com/ernilambar/ai-provider-for-github-models
 * Description:       AI Provider for GitHub Models for the WordPress AI Client.
 * Requires at least: 7.0
 * Requires PHP:      7.4
 * Version: 1.0.1
 * Author:            Nilambar Sharma
 * Author URI:        https://nilambar.net
 * License:           GPL-2.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html
 * Text Domain:       ai-provider-for-github-models
 * Domain Path:       /languages
 *
 * @package Nilambar\AiProviderForGithubModels
 */

declare( strict_types=1 );

namespace Nilambar\AiProviderForGithubModels;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AI_PROVIDER_FOR_GITHUB_MODELS_VERSION', '1.0.1' );
define( 'AI_PROVIDER_FOR_GITHUB_MODELS_BASE_NAME', basename( __DIR__ ) );
define( 'AI_PROVIDER_FOR_GITHUB_MODELS_BASE_FILEPATH', __FILE__ );
define( 'AI_PROVIDER_FOR_GITHUB_MODELS_BASE_FILENAME', plugin_basename( __FILE__ ) );
define( 'AI_PROVIDER_FOR_GITHUB_MODELS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

add_action(
	'plugins_loaded',
	static function () {
		if ( ! file_exists( AI_PROVIDER_FOR_GITHUB_MODELS_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
			return;
		}

		require_once AI_PROVIDER_FOR_GITHUB_MODELS_PLUGIN_DIR . 'vendor/autoload.php';

		( new Bootstrap() )->init();
	}
);
