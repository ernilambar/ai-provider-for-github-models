# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Quality Gate

Every task must end with:

1. `composer format` — auto-fix style issues first
2. `composer lint` — 0 errors, 0 warnings

No JS build step — `assets/js/settings.js` is plain static JS.

## Common Commands

```bash
# PHP
composer lint        # PHP syntax check + PHPCS
composer phpcs       # PHPCS only
composer format      # Auto-fix with phpcbf

# Translations
composer pot         # Regenerate POT from source
composer po          # Update .po files from POT
composer mo          # Compile .mo binaries

# Package & deploy
pnpm run deploy      # Build plugin zip via packtor into deploy/
pnpm run wpdeploy    # Release to WordPress.org (runs deploy first)
```

## Version Bumping

1. Manually update `"version"` in `package.json`.
2. Run `pnpm run version` — propagates the version to the main PHP file (`Version` header + `AI_PROVIDER_FOR_GITHUB_MODELS_VERSION` constant) and `readme.txt` (`Stable tag`). Rules are in `easy-replace.json`.
3. Add a new entry to `== Changelog ==` in `readme.txt`.

## Architecture

This plugin integrates GitHub Models as a provider for the **WordPress AI Client** (`WordPress\AiClient`) framework. The entire plugin is a thin adapter — no business logic beyond auth and path-rewriting lives here.

**Registration flow:**
`Bootstrap::init()` → `wp init` action → `Bootstrap::register_provider()` → `AiClient::defaultRegistry()->registerProvider(GitHubModelsProvider::class)`

**Key classes:**

- **`GitHubModelsProvider`** (`src/Provider/`) — Extends `AbstractApiProvider`. Declares base URL (`https://models.github.ai`), provider metadata (slug `github-models`, API key auth), and wires up the model and metadata directory classes. All methods are static; the framework instantiates via class name.

- **`GitHubModelsTextGenerationModel`** (`src/Models/`) — Extends `AbstractOpenAiCompatibleTextGenerationModel`. Two responsibilities:
  1. Rewrites request paths: `v1/chat/completions` → `/inference/chat/completions`.
  2. Applies the plugin's saved default model (`Settings::OPTION_NAME`) **only when the caller has not already specified a model** — so `using_model()` / `using_model_preference()` from the AI Client always win.
  Also converts `max_tokens` → `max_completion_tokens` for reasoning models (o1, o3, gpt-5).

- **`GitHubModelsModelMetadataDirectory`** (`src/Metadata/`) — Extends `AbstractApiBasedModelMetadataDirectory`. Fetches the model list from GitHub API (`v1/models`) and maps each entry to `ModelMetadata` with text-generation + chat-history capabilities.

- **`Settings`** (`src/Settings.php`) — Admin settings page at Settings → GitHub Models. The model `<select>` is rendered disabled and populated client-side via AJAX (`wp_ajax_ai_provider_github_models_get_models`) after the page loads, so it only shows models when a valid API key is configured.

## Coding Standard

PHPCS uses `.phpcs.xml.dist`: WordPress Core/Docs/Extra + `NilambarCodingStandard` + Slevomat rules, targeting PHP 7.4 / WordPress 7.0. Excludes `build/`, `deploy/`, `languages/`, `node_modules/`, `vendor/`.
