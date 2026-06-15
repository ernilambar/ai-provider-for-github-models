<?php
/**
 * Provider implementation.
 *
 * @package Nilambar\AiProviderForGithubModels
 */

declare( strict_types=1 );

namespace Nilambar\AiProviderForGithubModels\Provider;

use Nilambar\AiProviderForGithubModels\Metadata\GitHubModelsModelMetadataDirectory;
use Nilambar\AiProviderForGithubModels\Models\GitHubModelsTextGenerationModel;
use WordPress\AiClient\Common\Exception\RuntimeException;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiProvider;
use WordPress\AiClient\Providers\ApiBasedImplementation\ListModelsApiBasedProviderAvailability;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;
use WordPress\AiClient\Providers\DTO\ProviderMetadata;
use WordPress\AiClient\Providers\Enums\ProviderTypeEnum;
use WordPress\AiClient\Providers\Http\Enums\RequestAuthenticationMethod;
use WordPress\AiClient\Providers\Models\Contracts\ModelInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;

/**
 * Provider implementation.
 *
 * @since 1.0.0
 */
class GitHubModelsProvider extends AbstractApiProvider {

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function baseUrl(): string {
		return 'https://models.github.ai';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 *
	 * @param ModelMetadata    $model_metadata    Model metadata.
	 * @param ProviderMetadata $provider_metadata Provider metadata.
	 * @return ModelInterface
	 * @throws RuntimeException When model capabilities are unsupported.
	 */
	protected static function createModel(
		ModelMetadata $model_metadata,
		ProviderMetadata $provider_metadata
	): ModelInterface {
		$capabilities = $model_metadata->getSupportedCapabilities();
		foreach ( $capabilities as $capability ) {
			if ( $capability->isTextGeneration() ) {
				return new GitHubModelsTextGenerationModel( $model_metadata, $provider_metadata );
			}
		}

		throw new RuntimeException(
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			'Unsupported model capabilities: ' . implode( ', ', $capabilities )
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function createProviderMetadata(): ProviderMetadata {
		return new ProviderMetadata(
			'github-models',
			'GitHub Models',
			ProviderTypeEnum::cloud(),
			'https://github.com/marketplace/models',
			RequestAuthenticationMethod::apiKey(),
			__( 'Use GitHub Models as an AI provider.', 'ai-provider-for-github-models' ),
			AI_PROVIDER_FOR_GITHUB_MODELS_PLUGIN_DIR . 'assets/images/github-models.svg'
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function createProviderAvailability(): ProviderAvailabilityInterface {
		return new ListModelsApiBasedProviderAvailability(
			static::modelMetadataDirectory()
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0.0
	 */
	protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface {
		return new GitHubModelsModelMetadataDirectory();
	}
}
