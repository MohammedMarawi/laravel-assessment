---
description: How to test Stripe webhooks locally
---

## Testing Stripe Webhooks on Localhost

Since Stripe cannot send webhooks to `localhost`, use the Stripe CLI:

### Setup

1. Install Stripe CLI: https://stripe.com/docs/stripe-cli
2. Login to Stripe:
    ```powershell
    stripe login
    ```

### Forward Webhooks

3. Forward webhooks to your local server:

    ```powershell
    stripe listen --forward-to http://localhost:8000/api/webhook/stripe
    ```

4. The CLI will output a webhook signing secret like:

    ```
    > Ready! Your webhook signing secret is whsec_xxxxx
    ```

5. Update your `.env` with this secret:
    ```
    STRIPE_WEBHOOK_SECRET=whsec_xxxxx
    ```

### Testing

6. In another terminal, trigger a test event:

    ```powershell
    stripe trigger checkout.session.completed
    ```

7. Or complete a real checkout and the webhook will be forwarded.

### Alternative: Use the Success Endpoint Fallback

If you don't want to set up Stripe CLI, the app now includes a fallback mechanism:

1. After completing checkout at the Stripe URL
2. Visit `/api/payment/success?session_id={SESSION_ID}`
3. The app will verify payment with Stripe and activate the subscription automatically
