# Tasks: [FEATURE NAME]

**Input**: Design documents from `/specs/[###-feature-name]/`
**Prerequisites**: plan.md (required), research.md, data-model.md, contracts/

## Execution Flow (main)
```
1. Load plan.md from feature directory
   → If not found: ERROR "No implementation plan found"
   → Extract: tech stack, libraries, structure
2. Load optional design documents:
   → data-model.md: Extract entities → model tasks
   → contracts/: Each file → contract test task
   → research.md: Extract decisions → setup tasks
3. Generate tasks by category:
   → Setup: project init, dependencies, linting
   → Tests: contract tests, integration tests
   → Core: models, services, CLI commands
   → Integration: DB, middleware, logging
   → Polish: unit tests, performance, docs
4. Apply task rules:
   → Different files = mark [P] for parallel
   → Same file = sequential (no [P])
   → Tests before implementation (TDD)
5. Number tasks sequentially (T001, T002...)
6. Generate dependency graph
7. Create parallel execution examples
8. Validate task completeness:
   → All contracts have tests?
   → All entities have models?
   → All endpoints implemented?
9. Return: SUCCESS (tasks ready for execution)
```

## Format: `[ID] [P?] Description`
- **[P]**: Can run in parallel (different files, no dependencies)
- Include exact file paths in descriptions

## Path Conventions
- **Single project**: `src/`, `tests/` at repository root
- **Web app**: `backend/src/`, `frontend/src/`
- **Mobile**: `api/src/`, `ios/src/` or `android/src/`
- Paths shown below assume single project - adjust based on plan.md structure

## Phase 3.1: Setup
- [ ] T001 Create project structure per implementation plan
- [ ] T002 Initialize [language] project with [framework] dependencies
- [ ] T003 [P] Configure linting and formatting tools

## Phase 3.2: Tests First (TDD) ⚠️ MUST COMPLETE BEFORE 3.3
**CRITICAL: These tests MUST be written and MUST FAIL before ANY implementation**
- [ ] T004 [P] Contract test POST /text endpoint in tests/contract/test_text_post.py
- [ ] T005 [P] Contract test GET /quota/{key} endpoint in tests/contract/test_quota_get.py
- [ ] T006 [P] Contract test GET /status/{textId} endpoint in tests/contract/test_status_get.py
- [ ] T007 [P] Integration test SMS sending flow in tests/integration/test_sms_sending.py
- [ ] T008 [P] Integration test provider failure handling in tests/integration/test_provider_failures.py
- [ ] T009 [P] Integration test rate limiting in tests/integration/test_rate_limiting.py

## Phase 3.3: Core Implementation (ONLY after tests are failing)
- [ ] T010 [P] TextMessage model in src/models/text_message.py
- [ ] T011 [P] PhoneNumber model in src/models/phone_number.py
- [ ] T012 [P] SMS service with provider abstraction in src/services/sms_service.py
- [ ] T013 [P] Quota service in src/services/quota_service.py
- [ ] T014 POST /text endpoint implementation
- [ ] T015 GET /quota/{key} endpoint implementation
- [ ] T016 GET /status/{textId} endpoint implementation
- [ ] T017 Input validation and error handling
- [ ] T018 Rate limiting middleware

## Phase 3.4: Integration
- [ ] T019 Connect SMS service to database
- [ ] T020 API key authentication middleware
- [ ] T021 Request/response logging
- [ ] T022 Provider integration (Twilio, SNS, etc.)
- [ ] T023 Webhook signature verification
- [ ] T024 CORS and security headers

## Phase 3.5: Polish
- [ ] T025 [P] Unit tests for validation in tests/unit/test_validation.py
- [ ] T026 [P] Unit tests for provider abstractions in tests/unit/test_providers.py
- [ ] T027 Performance tests (rate limiting, response times)
- [ ] T028 [P] Update API documentation
- [ ] T029 Remove code duplication
- [ ] T030 Run manual testing scenarios

## Dependencies
- Tests (T004-T009) before implementation (T010-T018)
- T010 blocks T012, T019
- T011 blocks T012, T019
- T012 blocks T014, T022
- T013 blocks T015
- Implementation before polish (T025-T030)

## Parallel Example
```
# Launch T004-T009 together:
Task: "Contract test POST /text endpoint in tests/contract/test_text_post.py"
Task: "Contract test GET /quota/{key} endpoint in tests/contract/test_quota_get.py"
Task: "Contract test GET /status/{textId} endpoint in tests/contract/test_status_get.py"
Task: "Integration test SMS sending flow in tests/integration/test_sms_sending.py"
Task: "Integration test provider failure handling in tests/integration/test_provider_failures.py"
Task: "Integration test rate limiting in tests/integration/test_rate_limiting.py"
```

## Notes
- [P] tasks = different files, no dependencies
- Verify tests fail before implementing
- Commit after each task
- Avoid: vague tasks, same file conflicts

## Task Generation Rules
*Applied during main() execution*

1. **From API Contracts**:
   - Each endpoint → contract test task [P]
   - Each endpoint → implementation task
   - Error scenarios → error handling tests

2. **From Data Model**:
   - TextMessage entity → model creation task [P]
   - PhoneNumber entity → model creation task [P]
   - Relationships → service layer tasks

3. **From SMS User Stories**:
   - Send SMS flow → integration test [P]
   - Provider failures → error handling test [P]
   - Rate limiting → performance test [P]
   - Quota management → business logic test [P]

4. **From Constitution Requirements**:
   - Simple First → curl-able endpoint tests
   - Predictable Behavior → consistent response tests
   - Portable Architecture → provider abstraction tests
   - Honest Performance → rate limiting and quota tests
   - Test-First Development → TDD enforcement

5. **Ordering**:
   - Setup → Tests → Models → Services → Endpoints → Integration → Polish
   - Dependencies block parallel execution

## Validation Checklist
*GATE: Checked by main() before returning*

- [ ] All contracts have corresponding tests
- [ ] All entities have model tasks
- [ ] All tests come before implementation
- [ ] Parallel tasks truly independent
- [ ] Each task specifies exact file path
- [ ] No task modifies same file as another [P] task
