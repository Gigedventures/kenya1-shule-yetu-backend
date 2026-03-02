# Post-Generation Validation Checklist

After Codex finishes generating a module, DO NOT proceed until ALL
steps below pass.

---

## Step 1 — Migrations
- Run: php artisan migrate:fresh
- Confirm no migration errors
- Confirm tables exist with correct schema

---

## Step 2 — Tests
- Run: php artisan test
- All tests must pass
- No skipped or incomplete tests

---

## Step 3 — Manual Verification
- Open Filament
- Create minimal records
- Verify isolation across schools
- Attempt invalid cross-school action (must fail)

---

## Step 4 — Security Sanity
- Confirm no unscoped queries
- Confirm RBAC blocks unauthorized actions
- Confirm context required everywhere

---

## Step 5 — Commit
- Commit code only after checklist passes