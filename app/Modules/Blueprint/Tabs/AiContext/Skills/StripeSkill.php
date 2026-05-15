<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Skills;

class StripeSkill extends AbstractSkill
{
    protected function skillName(): string
    {
        return 'stripe';
    }

    protected function skillContent(): string
    {
        return <<<'MARKDOWN'
## Stripe Integration

When integrating Stripe payments:

### Security
- Always verify webhook signatures before processing events
- Never expose secret keys client-side
- Use environment variables for API keys

### Best Practices
- Use idempotency keys for retry safety on payment operations
- Store minimal data: charge ID, customer ID, subscription ID
- Handle all known event types; log unknown events for review
- Use Stripe's official SDK, not raw API calls

### Webhook Handling
- Return 200 immediately, process async
- Use a queue for heavy processing
- Implement graceful degradation if Stripe is down
- Check `stripe-signature` header before any processing

### Testing
- Use Stripe CLI for local webhook testing
- Use `stripe test` commands and test card numbers
- Always test failure scenarios, not just success
MARKDOWN;
    }
}
