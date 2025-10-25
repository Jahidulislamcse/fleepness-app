# Fleepness App – Developer Onboarding Guide

## Table of Contents

1. [Project Overview](#project-overview)
2. [Repository Structure](#repository-structure)
3. [Secret Management](#secret-management)
4. [Pre-commit Hooks](#pre-commit-hooks)
5. [Lint-staged](#lint-staged)
6. [Encryption Script](#encryption-script)
7. [Cross-platform Notes](#cross-platform-notes)
8. [Git Workflow](#git-workflow)
9. [Troubleshooting](#troubleshooting)

---

## Project Overview

Fleepness App is a Laravel (PHP) project with Husky pre-commit hooks and `lint-staged` integration to automate code quality checks and secure secret management.  
The project ensures encrypted secrets are stored safely and never committed in plaintext.

---

## Repository Structure

```plaintext
project-root/
├─ .husky/               # Husky pre-commit and other hooks
├─ scripts/              # Utility scripts (encrypt-secrets.js)
├─ secrets/              # Encrypted secrets
│  ├─ staging/
│  └─ production/
├─ secrets.staging.json  # Plaintext secrets for staging (local only, in project root)
├─ secrets.production.json # Plaintext secrets for production (local only, in project root)
├─ gpg-passfile.txt      # Local GPG passphrase file (never committed)
├─ package.json
├─ lint-staged.config.js # lint-staged configuration
└─ src/                  # Main source code
```

---

## Secret Management

-   Plaintext secrets (`secrets.staging.json` and `secrets.production.json`) are stored **locally in the project root** and **never committed**.
-   Encrypted secrets are stored inside the `secrets/` directory under `staging/` and `production/` respectively.
-   Deployment environments in CI are generated from the encrypted `secrets.json.gpg` files, which are created locally from `secrets.staging.json` / `secrets.production.json`.
-   `gpg-passfile.txt` stores the passphrase for GPG encryption and is also **never committed**.
-   Husky prevents accidental commits of any plaintext secret files.

---

## Pre-commit Hooks

-   **Managed by Husky v10+**.
-   Pre-commit hook runs:
    1. `lint-staged` to check staged files for formatting, linting, and forbidden files (`gpg-passfile.txt` and plaintext secrets).
    2. `encrypt-secrets.js` for both staging and production, using the GPG passfile.
-   POSIX-compliant and cross-platform friendly (Unix/macOS/Windows via Git Bash/WSL).

---

## lint-staged

-   Automatically runs commands on staged files.
-   Configured in `lint-staged.config.js`.
-   Example rules:
    -   Prevent `gpg-passfile.txt` or plaintext secrets from being committed.
    -   Optional: Run formatters or linters on code files.
-   Executor detection: prefers `bunx`, then `npx`, then `deno`.

---

## Encryption Script: `encrypt-secrets.js`

-   Handles symmetric encryption of secrets using GPG AES256.
-   Accepts the following command-line arguments:
    -   `-p, --preset`: `staging` or `production` preset paths.
    -   `-S, --source`: source JSON file (overrides preset).
    -   `-H, --hash`: hash file path (overrides preset).
    -   `-E, --encrypted`: output encrypted file path (overrides preset).
    -   `-P, --passfile`: **required** GPG passphrase file.
-   Script features:
    -   Computes SHA256 hash of source file.
    -   Only re-encrypts if content changed.
    -   Automatically stages both the encrypted file and the hash file in Git.
    -   Colored console outputs for errors, warnings, and success messages.

**Example usage:**

```bash
node scripts/encrypt-secrets.js -p staging -P gpg-passfile.txt
node scripts/encrypt-secrets.js -S secrets.custom.json -H .custom.hash -E secrets/custom.gpg -P gpg-passfile.txt
```
