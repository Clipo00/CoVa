# covar CLI

A command-line tool to list and fetch CoVaR blueprints from your terminal.

## Requirements

- **PHP 8.3** or higher
- A **CoVaR API token** (generated from your CoVaR profile settings)

## Installation

### Option 1: Download the PHAR (recommended)

```bash
# Download the CLI tool
curl -L -o covar https://CoVaR.dev/downloads/covar.phar

# Make it executable
chmod +x covar

# Move it to a directory in your PATH
sudo mv covar /usr/local/bin/
```

### Option 2: Run from source

```bash
# Clone the repository and navigate to the CLI directory
cd cli

# Install dependencies
composer install

# Run the CLI
php covar help
```

## Quick Start

### 1. Set your API key

```bash
covar config:set-key cova_your_api_token_here
```

The CLI validates the key immediately by connecting to the CoVaR API. If the key is valid, it's saved to `~/.config/covar/config.json` with restricted permissions.

### 2. List your blueprints

```bash
covar vault:list
```

Shows a table of all blueprints you have access to:

```
+------+-------------------+
| Slug | Title             |
+------+-------------------+
| foo  | My Blueprint      |
| bar  | Another One       |
+------+-------------------+
```

Include descriptions:

```bash
covar vault:list -g
```

```
+------+-------------------+-----------------------------+
| Slug | Title             | Description                 |
+------+-------------------+-----------------------------+
| foo  | My Blueprint      | A Laravel API starter       |
+------+-------------------+-----------------------------+
```

### 3. Fetch a blueprint

```bash
covar vault:fetch <slug>
```

Scaffolds the blueprint files in your current directory: `.agent.md`, VSCode extensions, MCP configuration, and `.env`.

If the blueprint contains secret variables, you will be prompted for a password to decrypt them.

## Available Commands

| Command | Description |
|---------|-------------|
| `config:set-key <key>` | Set and validate your CoVaR API key |
| `vault:list` | List accessible blueprints |
| `vault:fetch <slug>` | Fetch and scaffold a blueprint |
| `help` | Show help and available commands |

## Troubleshooting

### "Authentication failed"

Your API key is invalid or expired. Generate a new one from your CoVaR profile settings and run:

```bash
covar config:set-key cova_your_new_token
```

### "API access requires Pro or Enterprise plan"

Your account plan does not include API access. Upgrade your plan from your CoVaR account settings.

### "Network error: unable to reach the CoVaR API"

Check your internet connection and ensure the CoVaR API is accessible. If you're behind a corporate proxy, you may need to configure your environment.

### "Config file not found"

You haven't set an API key yet. Run:

```bash
covar config:set-key cova_your_token_here
```

### Using a custom API base URL

If you're using a staging or self-hosted version of CoVaR:

```bash
covar config:set-key cova_your_token --base-url=https://your-instance.CoVaR.app
```

## Security

- Your API key is stored in `~/.config/covar/config.json` with `0600` permissions (Unix only)
- The key is sent as a Bearer token in the `Authorization` header for all API requests
- All communication uses HTTPS
- Secret variables are never displayed in plain text — password verification is required to decrypt them

## Building from Source

```bash
cd cli
composer install
php covar app:build
```

The PHAR will be created in `builds/`.
