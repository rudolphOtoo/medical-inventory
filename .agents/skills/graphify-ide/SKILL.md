---
name: graphify-ide
description: Custom graphify workflow that runs inline in the IDE without external API keys and generates Python scripts for the user to execute. Trigger with /graphify-ide.
---

# /graphify-ide

When the user types `/graphify-ide`, follow this specific workflow:

## 1. No API Keys & Inline Semantic Extraction
The user does **not** want to use external API keys (like `GEMINI_API_KEY` or `GOOGLE_API_KEY`) for semantic extraction.
Instead of skipping semantic extraction or dispatching background subagents, the Agent IDE performs semantic extraction inline. Review the core conceptual documents (e.g. architecture notes, decision logs) and inject a lightweight semantic JSON object into the build process.

## 2. Provide Scripts, Do Not Execute Automatically
Write a single, robust Python script (e.g., `run_graphify.py`) that handles the entire pipeline:
- Detection (`graphify.detect`)
- Structural/AST Extraction (`graphify.extract`)
- Merging the inline semantic JSON
- Building the graph, clustering, and generating HTML and Markdown reports.

After writing the script, give the user the terminal command to run it (e.g., `python3 run_graphify.py`).

## 3. Dependencies
Ensure the environment has `graphifyy` and `graphifyy[sql]`.
