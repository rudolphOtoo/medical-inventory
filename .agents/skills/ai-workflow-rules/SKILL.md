---
name: ai-workflow-rules
description: Spec-driven incremental AI coding, scoping, and invariant preservation rules. Trigger with /ai-workflow-rules.
---

# /ai-workflow-rules

## Approach
Build this project incrementally using a spec-driven workflow. Context files define what to build, how to build it, and the current state of progress. Always implement against these specs — do not infer or invent behavior from scratch.

## Scoping Rules
- Work on one feature unit at a time.
- Prefer small, verifiable increments over large speculative changes.
- Do not combine unrelated system boundaries in a single implementation step.

## When to Split Work
Split an implementation step if it combines:
- UI changes and background task changes.
- Multiple unrelated API routes.
- Behavior not clearly defined in the context files.

*If a change cannot be verified end-to-end quickly, the scope is too broad — split it.*

## Handling Missing Requirements
- Do not invent product behavior not defined in the context files.
- If a requirement is ambiguous, resolve it before implementing.
- If a requirement is missing, surface it as an open question before continuing.

## Invariant Checks Before Moving to Next Unit
1. The current unit works end to end within its defined scope.
2. No invariant defined in architecture was violated.
3. Test suite passes.
4. Assets compile cleanly (`npm run build`).
