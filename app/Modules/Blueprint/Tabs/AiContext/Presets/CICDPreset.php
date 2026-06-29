<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Presets;

class CICDPreset extends AbstractPreset
{
    protected function presetName(): string
    {
        return 'cicd';
    }

    protected function presetContent(): string
    {
        return <<<'MARKDOWN'
## CI/CD Pipeline Design

### General Principles
- Build once, deploy many: create immutable artifacts
- Fail fast: run linting and static analysis before tests
- Parallelize independent jobs to reduce pipeline time
- Cache dependencies between runs (Composer, npm, pip)

### GitHub Actions
- Use reusable workflows for shared logic
- Pin action versions with full commit SHA for supply chain security
- Use matrix builds for multi-version testing
- Store secrets in GitHub Secrets, never in code

### Testing Strategies
- Run unit tests on every push
- Run integration tests with ephemeral services (Docker)
- Run E2E tests on staging before production deployment
- Enforce code coverage thresholds

### Deployment Patterns
- Blue-green deployment for zero-downtime releases
- Canary releases for gradual rollout
- Feature flags for toggling without deployment
- Rollback strategy: always keep previous working artifact

### Code Quality Gates
- Static analysis (PHPStan, ESLint, Pylance)
- Code formatting checks (PHP CS Fixer, Prettier)
- Security scanning (Dependabot, Snyk)
- License compliance checks
MARKDOWN;
    }
}
