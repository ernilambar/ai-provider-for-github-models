<?php
/**
 * Text generation model.
 *
 * @package Nilambar\AiProviderForGithubModels
 */

declare( strict_types=1 );

namespace Nilambar\AiProviderForGithubModels\Models;

use Nilambar\AiProviderForGithubModels\Provider\GitHubModelsProvider;
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
