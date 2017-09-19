# Architecture

## Component diagram

```
  mobile app / public client ──▶ Api.php  (4,450-line API + mobile backend)
                                     │
                    ┌────────────────┼────────────────┐
                    ▼                ▼                ▼
              Auth.php          Prizebond.php    subscription/Api.php
           (session/token)     (REST_Controller     (subscription-side
                                   example)           API surface)
                    │
                    ▼
            api_model / global_model / device_model   ← persistence

  purchase flow ──▶ subscription/Purchase.php ──▶ payment/Cbkash.php
                                                ──▶ payment/Csurecash.php
                                                        │
                                          gateway-specific verify/callback
                                                        │
                                                        ▼
                                          subscription_product_purchase_list
                                          payment_bkash_queue

  scheduled (cron) ──▶ Cron.php
        │        │
        │        ├──▶ Cron_model            reads new official draw results
        │        ├──▶ determinePrizebondWinners()   diff against user bond numbers
        │        ├──▶ libraries/Gcm.php     Android push to winners/all users
        │        ├──▶ config/apn.php        iOS push
        │        ├──▶ libraries/{Grameenphone,Robi,Banglalink,Muthofun,
        │        │      Experttexting,Bitbirds}.php   SMS fan-out
        │        └──▶ housekeeping: stale devices, expired orders, log purge

  admin panel ──▶ admin/{Dashboard,Users,Bonds,Draw,Reports,Message}.php
                          │
                          ▼
                   admin_model / report models   (sales, users, gateway reports)
```

## Why it's shaped this way

**One controller per concern at the edges, one giant one in the middle.**
`Api.php` is the public API and mobile backend combined — authentication,
device registration, subscription state, purchase history, push token
management — all reachable through one `REST_Controller`-derived class. That
made sense as the fastest way to ship a mobile backend against a fixed app
release cycle: every endpoint the app team asked for landed in the same
file rather than costing a new route registration and deployment
coordination. The cost shows up later: 4,450 lines with no enforced internal
boundary, tested only by hitting endpoints from the live app. It's kept
in this repo unmodified as the thing to point at when explaining why later
services in this portfolio split controllers by responsibility from day one.

**Gateway-specific controllers, not a gateway abstraction.** `Cbkash.php` and
`Csurecash.php` each own their provider's transaction verification and
callback shape directly, rather than going through a common `Gateway`
interface. bKash and SureCash were the only two payment options this market
needed at the time, so the abstraction wasn't built — a smaller-scale version
of the same problem `payment-gateway-platform-2018` and
`multi-provider-order-orchestrator` solve properly elsewhere in this
portfolio.

**Draw-result checking is a cron job because the data source is external and
unreliable.** The government publishes Prize Bond draw results on its own
schedule, in its own format, with no webhook or push mechanism. `Cron.php`
polls for new results, reconciles them against every user's registered bond
numbers, and pushes/SMS/emails winners — the same "poll an uncontrolled
external source, reconcile against internal state" shape used later, more
carefully, in `scheduled-export-pipeline`.

**Push notification is two independent code paths, not one abstraction.**
GCM (Android) and APN (iOS) have nothing in common at the protocol level —
different auth, different payload shape, different failure modes (GCM
returns per-device error codes in the response body; APN drops the
connection). `Cron.php` calls each directly rather than through a shared
notification interface. `web-push-notification-system` elsewhere in this
portfolio is the version of this idea built as a proper service.

**SMS is multi-provider because delivery reliability in Bangladesh depends on
which telecom the recipient is on.** Each of the six SMS libraries
(`Grameenphone`, `Robi`, `Banglalink`, `Muthofun`, `Experttexting`,
`Bitbirds`) wraps one aggregator's HTTP or XML API with its own auth. There's
a light `Smsrouter.php` on top, but no shared interface — each caller in
`Cron.php` picks a provider explicitly.

**Coupons are a separate commerce path from direct purchase.** Coupon series
generation (`Couponseries.php`), redemption (`Coupon.php`,
`Couponredeemedhistory.php`) and reporting live apart from the
subscription/purchase flow because they have a different lifecycle: a coupon
is generated once, distributed offline (often through cash-on-delivery
orders — see `CODActionsHelper` in `helpers/global_helper.php`), and redeemed
later by a possibly different user.

## Prize Bond win-checking flow

```
government publishes new draw result
        ▼
Cron.php polls / admin manually enters result (admin/Draw.php)
        ▼
Cron_model stores draw result
        ▼
determinePrizebondWinners()
        │  cross-references result against every user's registered
        │  bond numbers in user_prizebond_list
        ▼
winners flagged
        │
        ├──▶ sendGCMPushToWinnerDevice()   Android push
        ├──▶ APN push (config/apn.php)     iOS push
        └──▶ SMS / email fallback
```

## What this shows, given its age

CodeIgniter 2.x, PHP 5-era code, built 2016-2018 at a past employer. The
value in reading it is the integrations it contains — two mobile-money
payment gateways, six SMS aggregators, dual-platform push, and a cron-driven
reconciliation loop against a government data source with no API — and the
honest cost of shipping them all through one growing API controller. Every
one of those problems reappears, solved with more separation, in
`payment-gateway-platform-2018`, `web-push-notification-system`,
`scheduled-export-pipeline` and `multi-provider-order-orchestrator`
elsewhere in this portfolio. Kept here as the origin point of that line of
work, not as a template to copy.
