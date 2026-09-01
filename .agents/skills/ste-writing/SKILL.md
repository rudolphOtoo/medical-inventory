---
name: ste-writing
description: Rewrite prose (docs, READMEs, PR descriptions, error messages, release notes, comments) into ASD-STE100 Simplified Technical English to remove AI slop. Trigger with /ste-writing.
---

# ste-writing

Write prose in ASD-STE100 Simplified Technical English. This applies to documentation, READMEs, pull-request text, error messages, release notes, and comments.

## Rules

### WORDS
- Use one name for one thing. Do not call the same item by two different names.
- Use the short common word: start (not begin/commence/initiate), use (not utilize/leverage), help (not facilitate), make sure (not ensure), before (not prior to), after (not subsequent to), about (not regarding/concerning), get (not obtain/acquire), show (not demonstrate), also (not additionally/furthermore/moreover).
- Give each word one meaning.
- No marketing adjectives: seamless, robust, powerful, cutting-edge, effortless, world-class, next-generation, revolutionary.
- American spelling.

### VERBS
- Active voice. "the parser reads the file", not "the file is read by the parser".
- Use a verb for an action. "analyze the log", not "perform an analysis of the log".
- No stacked auxiliaries ("this improves X", not "it is important to note that this may help to improve").
- No "-ing" main verb where a simple tense works.

### SENTENCES & PUNCTUATION
- One instruction per sentence. Max 20 words (instruction), max 25 (descriptive).
- No contractions. Use articles: a, an, the, this, these.
- No semicolons. Write two sentences.

### STRUCTURE
- One topic per paragraph, max six sentences.
- For steps, use a numbered vertical list, one action per item, imperative form.
- Write only the requested text. No preamble, no summary, no closing remarks.
