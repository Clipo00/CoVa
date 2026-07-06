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

When integrating Stripe payments, follow these patterns:

### Security
- Always verify webhook signatures before processing events — check `stripe-signature` header
- Never expose secret keys client-side — use server-side only
- Use environment variables for all API keys: `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`
- Store only the minimum required data: charge ID, customer ID, subscription ID
- Never store full card numbers, CVV, or raw API responses
- Use Stripe's official PHP SDK (`stripe/stripe-php`), never raw HTTP calls to the API
- Implement idempotency keys for all mutation requests (`Idempotency-Key` header)

### Payment Flow
- Create PaymentIntent on the server, confirm on the client (PCI compliance)
- Use Stripe Elements or Checkout for collecting payment details
- Handle all PaymentIntent statuses: `requires_payment_method`, `processing`, `succeeded`, `requires_action`
- Implement 3D Secure (SCA) support for European customers
- Always have a fallback for users who cannot complete payment (email invoice, manual payment)
- Test with Stripe test card numbers: `4242...` (success), `4000...` (decline)

### Webhook Handling
- Return 200 immediately before processing — webhooks time out after 30 seconds
- Process heavy webhook handling in a queue job for reliability
- Implement graceful degradation if Stripe API is unreachable
- Handle all event types your application subscribes to; log unknown events for manual review
- Use idempotency keys to prevent duplicate webhook processing
- Verify event timestamps to prevent replay attacks
- Listen for events: `checkout.session.completed`, `customer.subscription.updated`, `invoice.payment_succeeded`

### Customer Management
- Store Stripe customer ID in your users table (`stripe_id`)
- Sync customer metadata (name, email) with Stripe on profile updates
- Handle customer deletion: cancel all active subscriptions before deleting customer
- Use Stripe's Tax API or manual tax calculation for EU VAT compliance
- Implement proper proration when customers upgrade/downgrade plans

### Testing
- Use Stripe CLI for local webhook forwarding: `stripe listen --forward-to localhost:8000/stripe/webhook`
- Use Stripe test mode keys for development and CI
- Always test failure scenarios: expired cards, insufficient funds, 3D Secure challenges
- Test webhook retries: Stripe retries up to 3 times over 3 days
- Write integration tests with Stripe test token/customer creation
MARKDOWN;
    }
}
