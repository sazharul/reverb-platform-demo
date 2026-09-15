# Demo Accounts

## Super Admin

| Field | Value |
|-------|-------|
| URL | http://localhost:8000/login |
| Email | `admin@pulsewire.demo` |
| Password | `DemoAdmin123!` |

Admin area: `/admin`

## Demo User (pre-seeded app)

| Field | Value |
|-------|-------|
| Email | `demo@pulsewire.demo` |
| Password | `DemoUser123!` |

Pre-created app credentials for API examples:

| Field | Value |
|-------|-------|
| App Key | `key123456789012345678901234567890` |
| App Secret | `demosecretdemosecretdemosecretdemosecretdemosecretdemosecretdemo12` |
| Channels | `orders`, `notifications` |

## DEMO_MODE payments

When `DEMO_MODE=true` (default in `.env.example`):

- Free plan: use "Subscribe" on the plans page
- Paid plans: checkout activates instantly without SSLCommerz redirect

## Example prompts

1. Log in as `demo@pulsewire.demo`
2. Open **My Apps** → view "Demo Store App"
3. Open **Events** → see seeded event logs
4. Trigger a new event via the API example in README.md
