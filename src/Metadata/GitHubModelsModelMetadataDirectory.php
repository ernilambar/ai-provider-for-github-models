<?php
/**
 * Model metadata directory.
 *
 * @package Nilambar\AiProviderForGithubModels
 */

declare( strict_types=1 );

namespace Nilambar\AiProviderForGithubModels\Metadata;

use Nilambar\AiProviderForGithubModels\Provider\GitHubModelsProvider;
use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiBasedModelMetadataDirectory;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\Http\Exception\ResponseException;
use WordPress\AiClient\Providers\Http\Util\ResponseUtil;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;

/**
 * Model metadata directory.
 *
 * @since 1.0.0
 *
 * @phpstan-type ModelsResponseData array{
 *     object: string,
 *     data: list<array{id: string, object: string}>
 * }
 */
class GitHubModelsModelMetadataDirectory extends AbstractApiBasedModelMetadataDirectory {

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, ModelMetadata>
	 * @throws ResponseException When the response is unsuccessful or missing data.
	 */
	protected function sendListModelsRequest(): array {
		$request  = $this->createRequest( HttpMethodEnum::GET(), 'v1/models' );
		$request  = $this->getRequestAuthentication()->authenticateRequest( $request );
		$response = $this->getHttpTransporter()->send( $request );

		ResponseUtil::throwIfNotSuccessful( $response );

		/* @var ModelsResponseData $models_data */
		$models_data = $response->getData();
		if ( ! isset( $models_data['data'] ) ) {
			throw ResponseException::fromMissingData( 'GitHub Models', 'data' );
		}

		$models_map = [];
		foreach ( $models_data['data'] as $model_entry ) {
			$model_id = $model_entry['id'];
			$metadata = $this->buildModelMetadata( $model_id );
			if ( null === $metadata ) {
				continue;
			}

			$models_map[ $model_id ] = $metadata;
		}

		ksort( $models_map );

		return $models_map;
	}

	/**
	 * Builds a ModelMetadata object for a single model.
	 *
	 * No model details endpoint is available, so all listed models
	 * are treated as text generation models without vision support.
	 *
	 * @since 1.0.0
	 *
	 * @param string $model_id The model identifier.
	 * @return \WordPress\AiClient\Providers\Models\DTO\ModelMetadata|null The model metadata.
	 */
	private function buildModelMetadata( string $model_id ): ?ModelMetadata {
		$input_modalities_option = new SupportedOption(
			OptionEnum::inputModalities(),
			[ [ ModalityEnum::text() ] ]
		);

		$options = [
			new SupportedOption( OptionEnum::systemInstruction() ),
			new SupportedOption( OptionEnum::candidateCount() ),
			new SupportedOption( OptionEnum::maxTokens() ),
			new SupportedOption( OptionEnum::temperature() ),
			new SupportedOption( OptionEnum::topP() ),
			new SupportedOption( OptionEnum::stopSequences() ),
			new SupportedOption( OptionEnum::frequencyPenalty() ),
			new SupportedOption( OptionEnum::presencePenalty() ),
			new SupportedOption( OptionEnum::outputMimeType(), [ 'text/plain', 'application/json' ] ),
			new SupportedOption( OptionEnum::outputSchema() ),
			new SupportedOption( OptionEnum::functionDeclarations() ),
			new SupportedOption( OptionEnum::customOptions() ),
			new SupportedOption( OptionEnum::outputModalities(), [ [ ModalityEnum::text() ] ] ),
			$input_modalities_option,
		];

		return new ModelMetadata(
			$model_id,
			$model_id,
			[
				CapabilityEnum::textGeneration(),
				CapabilityEnum::chatHistory(),
			],
			$options
		);
	}

	/**
	 * Creates a request object for the API.
	 *
	 * @since 1.0.0
	 *
	 * @param \WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum $method  The HTTP method.
	 * @param string                                                  $path    The API endpoint path, relative to the base URI.
	 * @param array<string, string|list<string>>                      $headers The request headers.
	 * @param string|array<string, mixed>|null                        $data    The request data.
	 * @return \WordPress\AiClient\Providers\Http\DTO\Request The request object.
	 */
	private function createRequest( HttpMethodEnum $method, string $path, array $headers = [], $data = null ): Request {
		return new Request(
			$method,
			GitHubModelsProvider::url( $path ),
			$headers,
			$data
		);
	}
}
