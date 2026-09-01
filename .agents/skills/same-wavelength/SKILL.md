---
name: same-wavelength
description: Pre-work alignment grill to lock requirements, architecture tradeoffs, non-goals, risk appetite, and definition of done before coding. User-invoked only via /same-wavelength or "same wavelength".
---

# Same Wavelength

## What

A **structured alignment session** before implementation—like a rigorous “grill me,” but oriented toward **shipping safely**, not debate for its own sake.

Output is **written locks**: scope, non-goals, decisions, open questions (resolved or explicitly deferred), and what “done” looks like.

## When

- **Only when the user invokes** — e.g. “same wavelength,” “align with me,” or `/same-wavelength`.
- **Do not** run on every message or every plan mode entry.
- **Suggested** before: new features, AI surfaces, auth/payments, migrations, multi-file refactors.

**After alignment:** switch to **senior-stable-delivery** for Rabit plan table + implementation pipeline.

---

## How — session flow

### 1. Mirror (1 paragraph)

Restate the user’s goal in plain language. Ask: **“Is this the problem we’re solving?”**

Stop if wrong; do not proceed.

### 2. Grill blocks

Ask in **batches of 3** until these blocks are filled. Max ~10 questions total unless user wants deeper.

| Block | Force answers on |
|-------|------------------|
| **Scope** | In / out; MVP vs later; single ticket boundary |
| **Users & context** | Who, environment, constraints (mobile, public, admin) |
| **Architecture** | Data flow, boundaries, what must not couple |
| **Failure & security** | AuthZ, abuse, invalid input, fail-open vs closed |
| **Done** | Verifiable checklist; tests; smoke steps |
| **Persona** | Stability vs speed; creep policy; review depth |

### 3. Challenge pass (senior dev voice)

Push on:
- Hidden assumptions (“we’ll add cache later” → Reject or ticket?)
- Duplicate systems (second way to do the same thing)
- AI/tool temptation without harness boundaries
- Missing non-goals (what stakeholders might *assume* is included)

Use **Ship / Reject / Defer** per idea—not implementation yet.

### 4. Alignment brief (required output)

```markdown
## Alignment brief — {title}
**Date:** {date}
**Problem:** {one sentence}

### Locked IN
- ...

### Locked OUT (non-goals)
- ...

### Decisions
| Topic | Decision | Rationale |
|-------|----------|-----------|

### Risks & mitigations
| Risk | Mitigation |
|------|------------|

### Definition of done
- [ ] ...

### Open questions
- {None — or list with owner}

### Persona sync
- Stability: {high/med}
- Feature creep: {defer unless explicit expand}
- Next skill: senior-stable-delivery → Rabit plan table → implement
```

### 5. Explicit consent

End with: **“Confirm this brief (or edit), then I’ll proceed under senior-stable-delivery.”**

Do **not** write production code until the user confirms.
