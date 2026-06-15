<?php
/**
 * Text generation model.
 *
 * @package Nilambar\AiProviderForGithubModels
 */

declare( strict_types=1 );

namespace Nilambar\AiProviderForGithubModels\Models;

use Nilambar\AiProviderForGithubModels\Provider\GitHubModelsProvider;
use Nilambar\AiProviderForGithubModels\Settings;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleTextGenerationModel;

/**
 * Text generation model.
 *
 * @since 1.0.0
 */
class GitHubModelsTextGenerationModel extends AbstractOpenAiCompatibleTextGenerationModel {

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 *
	 * @param array<\WordPress\AiClient\Messages\DTO\Message> $prompt The prompt messages.
	 * @return array<string, mixed>
	 */
	protected function prepareGenerateTextParams( array $prompt ): array {
		$params = parent::prepareGenerateTextParams( $prompt );

		$selected_model = get_option( Settings::OPTION_NAME, '' );
		if ( '' !== $selected_model ) {
			$params['model'] = $selected_model;
		}

		// o1, o3, and gpt-5 models require max_completion_tokens instead of max_tokens.
		if ( isset( $params['max_tokens'] ) && $this->isReasoningModel( $params['model'] ?? '' ) ) {
			$params['max_completion_tokens'] = $params['max_tokens'];
			unset( $params['max_tokens'] );
		}

		return $params;
	}

	/**
	 * Returns true if the model requires max_completion_tokens instead of max_tokens.
	 *
	 * @since 1.0.0
	 *
	 * @param string $model_id The model identifier.
	 * @return bool
	 */
	private function isReasoningModel( string $model_id ): bool {
		return (bool) preg_match( '/^o\d/i', $model_id )
			|| str_contains( $model_id, 'gpt-5' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 *
	 * @param HttpMethodEnum                     $method  HTTP method.
	 * @param string                             $path    Endpoint path.
	 * @param array<string, string|list<string>> $headers Request headers.
	 * @param string|array<string, mixed>|null   $data    Request data.
	 * @return Request
	 */
	protected function createRequest(
		HttpMethodEnum $method,
		string $path,
		array $headers = [],
		$data = null
	): Request {
		// Rewrite v1/chat/completions → inference/chat/completions.
		$path = ltrim( (string) preg_replace( '#^v1/?#', '', ltrim( $path, '/' ) ), '/' );
		$path = '/inference/' . $path;

		return new Request(
			$method,
			GitHubModelsProvider::url( $path ),
			$headers,
			$data,
			$this->getRequestOptions()
		);
	}
}
