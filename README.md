# AI Provider for GitHub Models

WordPress plugin that registers [GitHub Models](https://github.com/marketplace/models) as an AI provider for the [WordPress AI Client](https://make.wordpress.org/core/2025/03/13/ai-client-for-wordpress/).

## Requirements

- WordPress 7.0+
- PHP 7.4+
- A GitHub Personal Access Token with Models access

## Installation

1. Install and activate the plugin.
2. Go to **Settings → Connectors** and enter your GitHub Personal Access Token.
3. Configure the AI Client under **Settings → AI**.

## Development

```bash
composer install
pnpm install
```

Run linting:

```bash
composer lint
```

## License

GPL-2.0-or-later — see [LICENSE](https://spdx.org/licenses/GPL-2.0-or-later.html).
