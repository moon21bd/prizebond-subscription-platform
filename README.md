# Prize Bond Subscription Platform

A CodeIgniter backend for a Bangladesh Prize Bond tracking and subscription
service: a public API + mobile app backend that lets users register their
Prize Bond numbers, checks them automatically against official government
draw results, sends win notifications, and sells paid subscriptions/coupons
through bKash and SureCash — the two mobile financial service gateways used
for consumer payments in Bangladesh — behind an admin panel that manages the
whole thing.

**This is early-career work (2016-2018), published deliberately.** It's kept
in this portfolio for the same reason as the other CodeIgniter-era projects
here: the payment integration, the cron-driven external-data reconciliation,
and the push notification fan-out are all real problems that reappear later
in more disciplined form. It's the origin point of that line of work, not a
template to copy.

Built at a past employer (a small Dhaka-based web solutions company, named on
my CV/LinkedIn but omitted here). Generalised for publication: company
identity, live merchant credentials, SMS gateway passwords, an SMTP password,
a Google Cloud Messaging API key, and colleague names/emails have all been
removed or replaced with environment-variable placeholders. See
[Redactions](#redactions) below for the specifics.

## What it demonstrates

- **Two payment gateways behind one purchase flow** — `payment/Cbkash.php`
  and `payment/Csurecash.php` each wrap a mobile financial service's own
  transaction/verification API, with the subscription and coupon commerce
  layer (`controllers/subscription/*`) agnostic to which one was used.
- **Cron-driven reconciliation against an external, uncontrolled data
  source** — `Cron.php` (853 lines) and `admin/Draw.php` handle checking
  newly-published official Prize Bond draw results against every user's
  registered bond numbers, flagging winners, and fanning out notifications.
  This is the same "poll an external source, reconcile against internal
  state, notify" shape that shows up as a proper queue-backed job in later
  services in this portfolio.
- **Push notification fan-out across two platforms** — `libraries/Gcm.php`
  (Android/GCM) and `config/apn.php` (iOS/APN) are both wired into `Cron.php`
  for draw-result and app-update notifications, including handling stale/
  invalid device tokens.
- **A multi-provider SMS layer** — `libraries/{Grameenphone,Robi,Banglalink,
  Muthofun,Experttexting,Bitbirds,Smsrouter}.php`: half a dozen Bangladeshi
  telecom/SMS aggregator integrations behind a router, each with its own
  auth scheme and response format.
- **Coupon-based commerce** — `subscription/Coupon.php`,
  `Couponseries.php`, `Couponredeemedhistory.php`: coupon series generation,
  redemption tracking, and reporting, separate from the direct-purchase flow.
- **An admin panel covering the whole operation** — user management, draw
  result entry, sales/gateway reporting, SMS/push message logs
  (`controllers/admin/*`, `controllers/logs/*`, `controllers/report/*`).

## Structure

```
application/
  controllers/
    Api.php              4,450-line public API + mobile app backend (see note below)
    Auth.php  Main.php   session/auth base controller other controllers extend
    Prizebond.php        REST_Controller example endpoint
    Campaign.php         promotional campaign endpoints
    Cron.php             draw-result polling, push/SMS/email fan-out, cleanup jobs
    Export.php           data export
    Forgotpassword.php   password reset flow
    Maintenance.php      SSE-based long-running maintenance task runner
    admin/               admin panel: users, bonds, draw results, reports, messaging
    device/               device/campaign monitoring endpoints
    logs/                error and SMS/push log viewers
    payment/             Cbkash.php, Csurecash.php — gateway-specific purchase flows
    report/              sales/user reporting
    subscription/        coupon + product commerce: orders, coupons, gateway, sales
  models/                one model per controller area, plus admin/logs/report/subscription subtrees
  libraries/             REST_Controller (Kacerguis/Sturgeon), Gcm, Surecash, and
                         one class per SMS provider (Grameenphone, Robi, Banglalink,
                         Muthofun, Experttexting, Bitbirds, Smsrouter)
  helpers/               api_helper, dashboard_helper, global_helper, user_profile_info_helper
  config/                credentials.php (redacted), gcm.php, apn.php, rest.php, routes.php, ...
  views/                 a representative slice of admin/ and subscription/ views (~14 of 224)
index.php  .htaccess     CodeIgniter front controller
```

## Note on age and known rough edges

CodeIgniter 2.x, PHP 5-era patterns. Read for the shape of the integrations,
not as a template:

- **`Api.php` is 4,450 lines.** It's the single controller behind the public
  API and the mobile app backend — auth, subscriptions, device registration,
  push tokens, purchase flows, all in one file. This is exactly the kind of
  monolithic controller that later services in this portfolio split by
  responsibility; it's left as-is here as an honest example of where that
  problem comes from (one controller that grew with every feature request).
- Several models exist in more than one historical version in the original
  codebase (dated backups, a colleague's WIP copy); only the canonical,
  currently-wired-up model for each area is included here.
- Bangla-language UI strings and CI 2.x's `$this->db->query()` string-built
  SQL appear throughout — normal for a 2016-2018 Bangladesh-market CodeIgniter
  app, not something modernised for this republish.

## Redactions

This is real production code with the following removed or replaced before
publication:

- Every occurrence of the original employer's name and its internal API
  domain — replaced with `example.com` subdomains.
- Live-looking bKash merchant credentials in `config/credentials.php`
  (MSISDN, user, password, SureCash access key/partner code/client ID) —
  replaced with `getenv()` placeholders.
- A live-looking Google Cloud Messaging API key in `config/gcm.php`.
- The APN passphrase in `config/apn.php`.
- An SMTP password in `controllers/Main.php`.
- Per-provider SMS gateway usernames/passwords/API keys in every
  `libraries/<Provider>.php` file (Grameenphone, Robi, Banglalink, Muthofun,
  Experttexting, Bit Birds).
- Eight real colleague email addresses (`*@<employer>.com`) — replaced with
  role-based placeholders at `@example.com`.
- Real colleague first names in code comments (`// created by ...`) —
  replaced with a generic "a teammate" attribution.
- Sample/placeholder phone numbers that matched real-looking Bangladeshi
  mobile numbers used in test/demo code — replaced with an obviously
  fake `017000000xx` series.

The application's own product-facing domain (`prizebond-checker.com`, used
for outbound email `From` addresses and a public offer link) was left as-is —
it identifies the product, not the employer.
