---
name: session-handoff
description: End-of-session compact handoff for human skim and next agent: relevance filter removes noise, keeps decisions/state/next actions. Trigger with /session-handoff or when stopping mid-task.
---

# Session Handoff

## What

A **relevance-filtered snapshot** of the session so the next human or agent can continue **without context pollution**—not a transcript dump.

## Relevance filter (strict)

### INCLUDE
- **Objective** — what we’re trying to finish (one sentence).
- **Locked decisions** — from alignment brief or Rabit.
- **Current state** — branch, ticket id, % done, what works vs not.
- **Files touched** — paths only, grouped by role.
- **Tests** — last command, pass/fail, failing test names.
- **Blockers** — env, API keys, approval, CI.
- **Next actions** — ordered, max 5 actionable items.
- **Explicit rejects** — creep items deferred.

### EXCLUDE (noise)
- Full tool call logs, schema dumps, search dumps.
- Failed approaches (unless informing next step).
- Large code blocks (use file:line pointers).

---

## Output Template

```markdown
## HANDOFF — {project} — {ticket or topic}
**Status:** {in progress | blocked | ready for PR | done}
**Branch:** {name or unknown}

### Objective
{one sentence}

### Locked decisions
- ...

### Current state
- {what was implemented}
- {what was not}

### Files (high signal)
- `path` — {why}

### Tests
- Last: `{command}` → {pass | fail}

### Blockers
- {none | list}

### Next session (do these first)
1. ...
2. ...
3. ...

### Out of scope / rejected this session
- ...
```
