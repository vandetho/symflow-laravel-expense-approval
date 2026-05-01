# Symflow Expense Approval

A runnable showcase for [`vandetho/symflow-laravel`](https://github.com/vandetho/symflow-laravel) — a Symfony-compatible workflow engine for Laravel. This app implements a multi-stage expense approval flow that exercises **Petri net semantics** (parallel review tracks), **role-based guards**, **transition middleware**, **event listeners**, and **live diagram rendering**.

## What it demonstrates

| Engine feature | Where you see it |
|---|---|
| Petri-net AND-split | `submit` fans `draft` → `legal_review`, `finance_review`, `manager_review` in one transition |
| Petri-net AND-join | `finalize` consumes a token from each `*_approved` place and produces one in `approved` |
| Guards | `approve_legal`, `approve_finance`, `approve_manager`, `pay` are role-gated via `RoleGuardEvaluator` |
| `GuardResult` with reason/code | UI surfaces *why* a transition is blocked ("Requires the legal role.") |
| Middleware | `AuditLogMiddleware` writes a full before/after marking record with actor + reason for every fired transition |
| Workflow event listeners | `WorkflowEventType::Entered` listener logs each hop (see `WorkflowServiceProvider::boot`) |
| Live diagram | `MermaidExporter` output is augmented with `classDef` so active places light up in real time |

## Quick start

Requires PHP 8.2+, Composer, Node 20+, and a clone of [`symflow-laravel`](https://github.com/vandetho/symflow-laravel) sitting at `../symflow-laravel` (the package is consumed via a Composer **path repository**).

```bash
git clone https://github.com/vandetho/symflow-laravel.git           # sibling, required by the path repo
git clone https://github.com/vandetho/symflow-laravel-expense-approval.git

cd symflow-laravel-expense-approval
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Open <http://localhost:8000>, then use the **role switcher** in the top-right to sign in as a demo user. Different roles unlock different transitions on each expense detail page.

### Seeded users

| Role | Name | Email | Password |
|---|---|---|---|
| Employee | Ada Lovelace | `ada@acme.test` | `password` |
| Employee | Grace Hopper | `grace@acme.test` | `password` |
| Manager | Linus Torvalds | `linus@acme.test` | `password` |
| Finance | Marie Curie | `marie@acme.test` | `password` |
| Legal | Hedy Lamarr | `hedy@acme.test` | `password` |

## The workflow

Defined in [`config/laraflow.php`](config/laraflow.php):

```
                             ┌─ legal_review ──── approve_legal ────┐
                             │   reject_legal ─┐                    ▼
draft ── submit ─────────────┼─ finance_review ── approve_finance ──┤
                             │   reject_finance ┐                   ▼
                             └─ manager_review ── approve_manager ──┘
                                                                    │
        ┌──────────── (any reject) ──────────────► rejected         ▼
        │                                                        finalize
        │                                                            │
        │                                                            ▼
        │                                                         approved
        │                                                            │
        │                                                            ▼
        └────────────────────────────────────────────────────────── pay ──► paid
```

It's a `workflow` (Petri net) — not a state machine — because `submit` and `finalize` operate on multiple tokens simultaneously.

## Architecture

```
app/
├── Enums/Role.php                          # employee | legal | finance | manager
├── Models/
│   ├── ExpenseRequest.php                  # uses HasWorkflowTrait, marking stored as JSON
│   ├── ExpenseAuditLog.php                 # written to by AuditLogMiddleware
│   └── User.php
├── Workflow/
│   ├── RoleGuardEvaluator.php              # implements GuardEvaluatorInterface, parses "role:X"
│   ├── AuditLogMiddleware.php              # captures before/after marking on every transition
│   └── WorkflowReasonContext.php           # tiny request-scoped store for transition reasons
├── Providers/
│   └── WorkflowServiceProvider.php         # rebinds the registry with our guard + middleware
└── Livewire/
    ├── Components/
    │   ├── RoleSwitcher.php                # demo-mode "sign in as" dropdown
    │   └── WorkflowDiagram.php             # Mermaid output + classDef highlighting
    └── Pages/
        ├── Dashboard.php                   # kanban / table view
        ├── ExpenseCreate.php               # new draft form
        └── ExpenseShow.php                 # detail page with action panel + audit timeline
```

### How the registry is wired

The package's `LaraflowServiceProvider` registers a default `WorkflowRegistryInterface` singleton that constructs `Workflow` objects without a guard evaluator. We override it in [`app/Providers/WorkflowServiceProvider.php`](app/Providers/WorkflowServiceProvider.php) so each workflow is built with our `RoleGuardEvaluator`, then attach `AuditLogMiddleware` and a logging listener in `boot()`.

### How a transition fires

1. Livewire button → `ExpenseShow::fire('approve_legal')`
2. `Workflow::can()` runs the guard. With no auth, returns `not_authenticated`. With wrong role, returns `wrong_role` and the UI shows "Requires the legal role."
3. `WorkflowReasonContext::set($this->reason)` stashes the optional note
4. `Workflow::apply()` runs the engine: emits guard / leave / transition / enter / entered / completed / announce events, walking through the configured middleware
5. `AuditLogMiddleware` captures `(actor, transition, marking_before, marking_after, reason)` to the `expense_audit_logs` table
6. `WorkflowEventType::Entered` listener logs the hop via `Log::info`
7. `PropertyMarkingStore::write` updates the in-memory `marking` attribute
8. Livewire calls `$expense->save()` to persist the new marking

### Diagram highlighting

`MermaidExporter::export($definition)` produces the base diagram. The `WorkflowDiagram` Livewire component appends `classDef` rules and `class <place> active` lines so whatever places are currently marked light up in real time — `*_approved` uses a sky tone, `rejected` flashes rose, `paid` glows emerald.

## Files of interest

- **Workflow definition:** [`config/laraflow.php`](config/laraflow.php)
- **Guard evaluator:** [`app/Workflow/RoleGuardEvaluator.php`](app/Workflow/RoleGuardEvaluator.php)
- **Audit middleware:** [`app/Workflow/AuditLogMiddleware.php`](app/Workflow/AuditLogMiddleware.php)
- **Registry override:** [`app/Providers/WorkflowServiceProvider.php`](app/Providers/WorkflowServiceProvider.php)
- **Diagram component:** [`app/Livewire/Components/WorkflowDiagram.php`](app/Livewire/Components/WorkflowDiagram.php)
- **Action panel:** [`resources/views/livewire/pages/expense-show.blade.php`](resources/views/livewire/pages/expense-show.blade.php)

## Deploy free on Fly.io

This repo ships with a `Dockerfile` (FrankenPHP-based, multi-stage, Node for assets) and a `fly.toml` configured for a small machine + a 1 GB persistent volume mounted at `/data` for SQLite.

The Dockerfile rewrites `composer.json` at build time to swap the local **path repo** (used for development against `../symflow-laravel`) for the Packagist release of `vandetho/symflow-laravel` — so deploys don't need the sibling clone.

```bash
# Once, on your machine:
brew install flyctl   # or curl -L https://fly.io/install.sh | sh
fly auth login

# In this directory:
fly launch --no-deploy --copy-config       # picks up the existing fly.toml
fly volumes create expense_data --size 1   # the volume mount referenced in fly.toml
fly secrets set APP_KEY="base64:$(openssl rand -base64 32)"
fly deploy
```

The `docker/entrypoint.sh` runs `migrate --force` on every boot and `db:seed` only when the SQLite file is empty — so the first deploy lights up with the demo data, subsequent deploys keep whatever state users leave behind. Wipe by running `fly ssh console -C "rm /data/database.sqlite"` then redeploying.

`auto_stop_machines = "stop"` keeps the demo idle when nobody is using it, so it consumes ~zero of Fly's free allowance. First request after sleep is ~2 s slower while the machine boots.

## Edit the workflow visually

This workflow is also published on [symflowbuilder.com](https://symflowbuilder.com) — a React Flow-based visual editor by the same author that exports Symfony-compatible YAML.

- **View the canvas:** https://symflowbuilder.com/w/9e50940e6f0e0d02 (read-only public share)
- **Round-trip:** drag/edit nodes there, export YAML, paste the workflow block into [`config/laraflow.php`](config/laraflow.php). Or the other way around — `workflow.yaml` in this repo is already in the import format symflowbuilder expects.

## Sibling demos

- [`symflow-laravel-issue-tracker`](https://github.com/vandetho/symflow-laravel-issue-tracker) — a mini Jira-style tracker with parallel code-review + qa-review before merge. Same workflow engine, different domain shape.

## License

MIT.
