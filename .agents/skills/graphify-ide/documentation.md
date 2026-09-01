# Graphify IDE Workflow Documentation

This document captures the design decisions and workflow preferences established for running the `graphify` pipeline.

## Core Decisions

1. **API Key Avoidance**:
   Bypass external LLM API keys for bulk semantic extraction. Leverage the native context window: read important documentation files directly and construct the semantic knowledge graph JSON inline.

2. **User-Controlled Execution**:
   Generate a self-contained Python script (`run_graphify.py`) orchestrating detection, extraction, and building. User retains control by running it manually.

3. **Dependencies**:
   `graphifyy` and `graphifyy[sql]`.
