---
name: review
description: Check the work before it becomes a problem. Checks if implementation matches plan and architecture boundaries, returning severity-grouped issues without auto-fixing. Trigger with /review.
---

# /review

## What it does
`/review` checks if the implementation matched the plan, if it respects your architecture boundaries, and if it's production ready. It returns issues by severity: critical, important, and minor. It never auto-fixes. You stay in control.

## When to run it
After any feature that touches multiple systems, writes to the database, handles auth, or has logic that's easy to get subtly wrong. Also run it when something feels off but you can't pinpoint why.

## Protocol for the AI Agent
1. Compare the newly written code against the original implementation plan and the project context/architecture files.
2. Analyze for potential bugs, security gaps, and boundary violations.
3. Output a detailed report grouping issues by Severity:
   - **Critical**: Must fix before merging.
   - **Important**: Should fix, structural issues.
   - **Minor**: Nitpicks, style, or optimization.
4. **DO NOT AUTO-FIX.** Present the diagnosis and wait for the user to select which issues to address.

## Example Prompts
- `/review`
- `/review The filter is showing all items instead of filtering by score above 70.`
