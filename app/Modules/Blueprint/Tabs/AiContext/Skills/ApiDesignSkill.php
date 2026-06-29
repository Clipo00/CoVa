<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Skills;

class ApiDesignSkill extends AbstractSkill
{
    protected function skillName(): string
    {
        return 'api-design';
    }

    protected function skillContent(): string
    {
        return <<<'MARKDOWN'
## API Design

### RESTful Design Principles
- Use nouns for resources, verbs for HTTP methods
- Consistent URL structure: `/api/v1/resource/{id}`
- Use plural nouns for collection endpoints (`/users`, `/orders`)
- Nest resources to express relationships (`/users/{id}/orders`)

### OpenAPI / Swagger
- Document all endpoints with OpenAPI 3.0+
- Use `openapi-generator` for client SDK generation
- Keep spec in sync with implementation via contract testing
- Version the API spec separately from the implementation

### Versioning
- Use URL path versioning (`/api/v1/`, `/api/v2/`)
- Maintain backward compatibility within a major version
- Deprecate endpoints with `Sunset` and `Deprecation` headers
- Provide migration guides for breaking changes

### Error Handling
- Use consistent error response format: `{error, message, details, traceId}`
- Return appropriate HTTP status codes (400, 401, 403, 404, 422, 500)
- Include validation errors in `details` array
- Always include a trace ID for debugging

### Pagination
- Use cursor-based pagination for large datasets
- Include `next_cursor` and `has_more` in responses
- Default page size: 20, max: 100
- Return total count only when explicitly requested

### HATEOAS
- Include related resource links in responses
- Use `_links` or `links` key for link objects
- Guide clients with available actions through links
- Use `rel` for link relationship types
MARKDOWN;
    }
}
