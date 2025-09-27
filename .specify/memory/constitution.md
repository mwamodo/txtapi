<!--
Sync Impact Report:
Version change: N/A → 1.0.0
Modified principles: N/A (initial creation)
Added sections: API Design Standards, Error Handling, Performance Standards
Removed sections: N/A
Templates requiring updates: ✅ plan-template.md, ✅ spec-template.md, ✅ tasks-template.md
Follow-up TODOs: None
-->

# txtAPI Constitution

## Core Principles

### I. Simple First
Every API endpoint MUST be curl-able in one line. Request/response shapes MUST be stable and predictable. Complex features that cannot be simplified MUST be rejected or deferred. The API surface MUST remain minimal and focused on core SMS functionality.

### II. Predictable Behavior
All endpoints MUST return consistent response shapes with explicit error codes. Error responses MUST follow the standard format: `{success: false, error: "machine_code", message: "human_readable", hint: "optional_tip"}`. Rate limits and quotas MUST be clearly documented and enforced consistently.

### III. Portable Architecture
The system MUST support pluggable SMS backends (mock, Twilio, SNS, SMTP-gateway). Configuration MUST allow local development and self-hosting. Provider-specific logic MUST be abstracted behind clean interfaces. No vendor lock-in dependencies.

### IV. Honest Performance
All performance limits MUST be explicitly documented. Soft rate limits (~1 msg/sec per key) MUST be enforced with proper HTTP status codes. Delivery expectations MUST be realistic and communicated clearly. System MUST handle provider failures gracefully.

### V. Test-First Development (NON-NEGOTIABLE)
TDD mandatory: Tests written → User approved → Tests fail → Then implement. Red-Green-Refactor cycle strictly enforced. All API endpoints MUST have contract tests. Integration tests MUST cover provider interactions and error scenarios.

## API Design Standards

### Request/Response Format
- Primary format: application/x-www-form-urlencoded for simplicity
- JSON support: Optional but consistent when used
- Idempotency: Support via `Idempotency-Key` header (24h window)
- Content-Type: Explicit handling for both formats

### Error Handling
- Standard error response shape across all endpoints
- Machine-readable error codes for programmatic handling
- Human-readable messages for debugging
- Optional hints for remediation
- Proper HTTP status codes (4xx for client errors, 5xx for server errors)

### Security & Authentication
- API key authentication via parameter or Authorization header
- HMAC signature verification for webhooks
- Rate limiting with Retry-After headers
- Phone number allowlist/denylist per key
- Country gating support

## Performance Standards

### Rate Limits
- Soft limit: ~1 request/sec per key
- Burst allowance: 5 requests
- Return 429 with Retry-After header when exceeded
- Quota tracking: Daily allowance per key
- Refund quota on provider hard failures

### Message Constraints
- Max length: 160 GSM-7 characters
- Reject longer messages with `message_too_long` error
- Unicode segmentation edge cases: Out of scope (documented non-goal)

### Delivery Expectations
- Document realistic delivery times per provider
- Handle provider unavailability gracefully
- Track delivery status with clear state transitions
- Provide status lookup endpoint for transparency

## Development Workflow

### Code Review Requirements
- All PRs MUST verify constitution compliance
- API changes MUST include contract tests
- Provider integrations MUST include integration tests
- Performance impact MUST be documented
- Breaking changes MUST follow versioning policy

### Testing Gates
- Contract tests MUST pass before implementation
- Integration tests MUST cover provider interactions
- Error scenarios MUST be tested
- Performance tests MUST validate rate limits
- Manual testing MUST follow quickstart scenarios

### Deployment Process
- Staging environment MUST mirror production
- Provider configurations MUST be environment-specific
- Database migrations MUST be backward compatible
- Feature flags MUST control new functionality
- Rollback plan MUST be documented

## Governance

This constitution supersedes all other development practices. Amendments require documentation of rationale, approval from project maintainers, and migration plan for existing code. All development work MUST comply with these principles.

**Version**: 1.0.0 | **Ratified**: 2025-01-27 | **Last Amended**: 2025-01-27
