---

description: "Task list template for feature implementation"
---

# Tasks: [FEATURE NAME]

**Input**: Design documents from `/specs/[###-feature-name]/`

**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Automated tests are REQUIRED for every acceptance scenario. Applicable positive and
negative authorization, monetary boundary/rounding, full JWT lifecycle, operation audit, dashboard,
PostgreSQL migration, recovery, and database tests are also required. For a defect, add the failing regression
test before the correction task.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Laravel application**: `app/`, `database/`, `resources/`, `routes/`, and `tests/` at repository root
- Paths shown below assume the project Laravel structure; refine them from plan.md when needed

<!--
  ============================================================================
  IMPORTANT: The tasks below are SAMPLE TASKS for illustration purposes only.

  The /speckit.tasks command MUST replace these with actual tasks based on:
  - User stories from spec.md (with their priorities P1, P2, P3...)
  - Feature requirements from plan.md
  - Entities from data-model.md
  - Endpoints from contracts/

  Tasks MUST be organized by user story so each story can be:
  - Implemented independently
  - Tested independently
  - Delivered as an MVP increment

  DO NOT keep these sample tasks in the generated tasks.md file.
  ============================================================================
-->

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [ ] T001 Create project structure per implementation plan
- [ ] T002 Initialize [language] project with [framework] dependencies
- [ ] T003 [P] Configure linting and formatting tools

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

Examples of foundational tasks (adjust based on your project):

- [ ] T004 Setup PostgreSQL schema, versioned reversible migrations, FK constraints, and transactions
- [ ] T005 [P] Implement server-side authentication, Policies/Gates, and operator ownership authorization
- [ ] T006 [P] Configure JWT expiry, explicit renewal, rotating hashed refresh tokens, revocation, HTTPS, and throttling
- [ ] T007 Create domain models and operation type catalogs
- [ ] T008 Configure non-destructive operation correction and before/after sensitive-action audits
- [ ] T009 Setup secret-safe logs, health route, PostgreSQL backup/restore, secure environment
  credentials, and economic PHP-hosting-compatible services without coupling to optional Supabase services

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - [Title] (Priority: P1) 🎯 MVP

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 1 (REQUIRED) ⚠️

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [ ] T010 [P] [US1] Feature test for [acceptance scenario] in tests/Feature/[Name]Test.php
- [ ] T011 [P] [US1] Authorization test for [protected action] in tests/Feature/[Name]AuthorizationTest.php

### Implementation for User Story 1

- [ ] T012 [P] [US1] Create [Entity1] model in app/Models/[Entity1].php
- [ ] T013 [P] [US1] Create [Entity2] migration in database/migrations/[timestamp]_create_[entities]_table.php
- [ ] T014 [US1] Add validation in app/Http/Requests/[Action]Request.php
- [ ] T015 [US1] Implement server-rendered action in app/Http/Controllers/[Name]Controller.php
- [ ] T016 [US1] Add authorization in app/Policies/[Entity1]Policy.php
- [ ] T017 [US1] Add audit history for sensitive user story 1 actions

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 4: User Story 2 - [Title] (Priority: P2)

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 2 (REQUIRED) ⚠️

- [ ] T018 [P] [US2] Feature test for [acceptance scenario] in tests/Feature/[Name]Test.php
- [ ] T019 [P] [US2] Authorization test for [protected action] in tests/Feature/[Name]AuthorizationTest.php

### Implementation for User Story 2

- [ ] T020 [P] [US2] Create [Entity] model in app/Models/[Entity].php
- [ ] T021 [US2] Add validation in app/Http/Requests/[Action]Request.php
- [ ] T022 [US2] Implement [action/feature] in app/Http/Controllers/[Name]Controller.php
- [ ] T023 [US2] Integrate with User Story 1 components (if needed)

**Checkpoint**: At this point, User Stories 1 AND 2 should both work independently

---

## Phase 5: User Story 3 - [Title] (Priority: P3)

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 3 (REQUIRED) ⚠️

- [ ] T024 [P] [US3] Feature test for [acceptance scenario] in tests/Feature/[Name]Test.php
- [ ] T025 [P] [US3] Authorization test for [protected action] in tests/Feature/[Name]AuthorizationTest.php

### Implementation for User Story 3

- [ ] T026 [P] [US3] Create [Entity] model in app/Models/[Entity].php
- [ ] T027 [US3] Add validation in app/Http/Requests/[Action]Request.php
- [ ] T028 [US3] Implement [action/feature] in app/Http/Controllers/[Name]Controller.php

**Checkpoint**: All user stories should now be independently functional

---

[Add more user story phases as needed, following the same pattern]

---

## Phase N: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] TXXX [P] Documentation updates in docs/
- [ ] TXXX Code cleanup and refactoring
- [ ] TXXX Performance optimization across all stories
- [ ] TXXX [P] Verify operation pagination, filter indexes, server aggregation, and N+1 prevention
- [ ] TXXX [P] Validate health checks and documented PostgreSQL/file backup recovery
- [ ] TXXX [P] Complete acceptance, authorization, monetary, authentication, real PostgreSQL migration, and regression test coverage in tests/
- [ ] TXXX Security hardening
- [ ] TXXX Verify production assets are precompiled, debug is disabled, and only Laravel public is exposed
- [ ] TXXX Run quickstart.md validation

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3+)**: All depend on Foundational phase completion
  - User stories can then proceed in parallel (if staffed)
  - Or sequentially in priority order (P1 → P2 → P3)
- **Polish (Final Phase)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) - No dependencies on other stories
- **User Story 2 (P2)**: Can start after Foundational (Phase 2) - May integrate with US1 but should be independently testable
- **User Story 3 (P3)**: Can start after Foundational (Phase 2) - May integrate with US1/US2 but should be independently testable

### Within Each User Story

- Acceptance tests MUST be mapped to each scenario; defect regression tests MUST be written and fail before correction
- Models before services
- Services before endpoints
- Core implementation before integration
- Story complete before moving to next priority

### Parallel Opportunities

- All Setup tasks marked [P] can run in parallel
- All Foundational tasks marked [P] can run in parallel (within Phase 2)
- Once Foundational phase completes, all user stories can start in parallel (if team capacity allows)
- All tests for a user story marked [P] can run in parallel
- Models within a story marked [P] can run in parallel
- Different user stories can be worked on in parallel by different team members

---

## Parallel Example: User Story 1

```bash
# Launch all tests for User Story 1 together:
Task: "Feature test for [acceptance scenario] in tests/Feature/[Name]Test.php"
Task: "Authorization test for [protected action] in tests/Feature/[Name]AuthorizationTest.php"

# Launch all models for User Story 1 together:
Task: "Create [Entity1] model in app/Models/[Entity1].php"
Task: "Create [Entity2] migration in database/migrations/[timestamp]_create_[entities]_table.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL - blocks all stories)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: Test User Story 1 independently
5. Deploy/demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Test independently → Deploy/Demo (MVP!)
3. Add User Story 2 → Test independently → Deploy/Demo
4. Add User Story 3 → Test independently → Deploy/Demo
5. Each story adds value without breaking previous stories

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: User Story 1
   - Developer B: User Story 2
   - Developer C: User Story 3
3. Stories complete and integrate independently

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Verify tests fail before implementing
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Avoid: vague tasks, same file conflicts, cross-story dependencies that break independence
