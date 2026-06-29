<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Presets;

class DockerPreset extends AbstractPreset
{
    protected function presetName(): string
    {
        return 'docker';
    }

    protected function presetContent(): string
    {
        return <<<'MARKDOWN'
## Docker Best Practices

### Dockerfile Structure
- Use multi-stage builds to reduce image size
- Prefer specific base image tags (e.g., `php:8.3-cli`) over `latest`
- Combine RUN commands to minimize layers
- Use `.dockerignore` to exclude unnecessary files

### Security
- Never run containers as root — use `USER` directive
- Use read-only root filesystem when possible: `--read-only`
- Scan images for vulnerabilities with `docker scout` or `trivy`
- Avoid storing secrets in environment variables — use Docker secrets or mounted files

### docker-compose
- Use version 3.8+ for compose files
- Define health checks for services
- Use named volumes for persistent data
- Set resource limits (memory, CPU) for containers

### Multi-stage Builds
- Stage 1: Build dependencies and compile assets
- Stage 2: Runtime with minimal dependencies
- Copy only what's needed: `COPY --from=build /app/vendor /app/vendor`
MARKDOWN;
    }
}
