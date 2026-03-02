# Module Generation Template — Shule Yetu

When generating ANY module, Codex must produce the following sections
in this order.

---

## SECTION 1 — Migrations

- All tables prefixed with shule_
- UUID primary keys
- school_id present everywhere
- Proper foreign keys and indexes
- Unique constraints for integrity

---

## SECTION 2 — Models

- Extend BaseShuleModel
- Define relationships
- Enforce cross-school validation
- No bypass of ScopedToSchool

---

## SECTION 3 — Business Logic

- Service class under module Services/
- Enforce invariants (no silent fixes)
- Throw on invalid state
- No assumptions about default values

---

## SECTION 4 — Filament Resources

- Resource per core model
- Minimal but functional forms and tables
- RBAC permission checks
- No global queries

---

## SECTION 5 — RBAC Permissions

- Explicit permission names
- Seed permissions if missing
- Do not auto-assign roles

---

## SECTION 6 — Tests

- Isolation test
- Rule enforcement test
- Duplicate prevention test
- Context missing test (fail-closed)

---

## SECTION 7 — Output Summary

- List of migrations created
- List of models created
- List of Filament resources created
- Commands to run after generation