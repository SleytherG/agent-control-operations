# Implementation Plan: [FEATURE]

**Branch**: `[###-feature-name]` | **Date**: [DATE] | **Spec**: [link]

**Input**: Feature specification from `/specs/[###-feature-name]/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command; its definition describes the execution workflow.

## Summary

[Extract from feature spec: primary requirement + technical approach from research]

## Technical Context

<!--
  ACTION REQUIRED: Replace the content in this section with the technical details
  for the project. The structure here is presented in advisory capacity to guide
  the iteration process.
-->

**Language/Version**: [e.g., Python 3.11, Swift 5.9, Rust 1.75 or NEEDS CLARIFICATION]

**Primary Dependencies**: [e.g., FastAPI, UIKit, LLVM or NEEDS CLARIFICATION]

**Storage**: [if applicable, e.g., PostgreSQL, CoreData, files or N/A]

**Time & Money**: [storage convention, America/Lima presentation, period boundaries, decimal money types]

**Authentication & Session**: [JWT lifetime, refresh rotation/revocation, explicit renewal flow]

**Testing**: [e.g., pytest, XCTest, cargo test or NEEDS CLARIFICATION]

**Target Platform**: [e.g., Linux server, iOS 15+, WASM or NEEDS CLARIFICATION]

**Project Type**: [e.g., library/cli/web-service/mobile-app/compiler/desktop-app or NEEDS CLARIFICATION]

**Performance Goals**: [domain-specific, e.g., 1000 req/s, 10k lines/sec, 60 fps or NEEDS CLARIFICATION]

**Constraints**: [domain-specific, e.g., <200ms p95, <100MB memory, offline-capable or NEEDS CLARIFICATION]

**Scale/Scope**: [domain-specific, e.g., 10k users, 1M LOC, 50 screens or NEEDS CLARIFICATION]

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Specification completeness**: Approved scope, user stories, business rules, acceptance
  scenarios, edge cases, explicit exclusions, problem, actors, and rationale are present; this plan
  contains the technology and deployment decisions rather than the specification.
- **Increment classification**: The request is classified as specification correction,
  implementation correction, new capability, architectural change, or non-functional technical
  work; new capability has its own independently demonstrable specification.
- **Security and privacy**: `ADMINISTRADOR_PROPIETARIO` and `OPERADOR` access is enforced on the
  server, including negative ownership tests; prohibited customer, banking, secret, and log data
  are absent.
- **Session safety**: Five-minute configurable JWT access tokens, server-provided expiry, explicit
  renewal, hashed rotating refresh tokens, revocation, logout, and invalid-token cleanup are
  designed and testable.
- **Operation integrity**: Required operation fields, non-destructive correction, and audit records
  containing actor, timestamp, action, entity, before/after values, and reason are designed.
- **Deployment compatibility**: Conventional PHP hosting with MySQL/MariaDB remains viable;
  production requires no prohibited infrastructure or Node.js request runtime, uses HTTPS, and
  exposes only Laravel `public`.
- **Minimal interface**: Blade server rendering, semantic HTML, custom CSS, modular page-scoped
  JavaScript, minimal auditable dependencies, and computer/tablet/phone usability are preserved.
- **Money and time**: Decimal money, distinct operational aggregates, no gross-as-profit display,
  `America/Lima`, and explicit period boundaries are covered by design and boundary tests.
- **Performance**: Operation pagination, indexes for frequent filters, server-side dashboard
  aggregation, N+1 prevention, and shared-hosting resource limits are addressed.
- **Observability and recovery**: Secret-safe logs, health route, production debug disabled,
  database/file backups, and reversible migrations or justified exceptions are planned.
- **Testing**: Every acceptance scenario and applicable authorization, monetary, authentication,
  audit, migration, recovery, and regression obligation has automated coverage planned.
- **System boundary**: The feature does not claim bank processing confirmation, official banking
  data, accounting status, or bank integration.

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)
<!--
  ACTION REQUIRED: Replace the placeholder tree below with the concrete layout
  for this feature. Delete unused options and expand the chosen structure with
  real paths (e.g., apps/admin, packages/something). The delivered plan must
  not include Option labels.
-->

```text
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/
└── Policies/

database/
├── factories/
├── migrations/
└── seeders/

resources/views/
routes/web.php

tests/
├── Feature/
└── Unit/
```

**Structure Decision**: [Document the selected structure and reference the real
directories captured above]

## Exception Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Principle | Reason | Risk | Alternatives | Compensating Measure | Approver |
|-----------|--------|------|--------------|----------------------|----------|
| [e.g., III] | [current need] | [specific risk] | [options evaluated] | [risk reduction] | [responsible] |
