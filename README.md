# Fleepness

Fleepness is an **online e-commerce platform with multi-vendor features**, allowing sellers to promote products via livestreams and short videos.

## Repository Structure

This repository contains the core application code along with configuration files and secrets management setup:

-   **Plaintext secrets:** Stored in the `./secrets` directory. These files contain sensitive information in plaintext format and should be handled with care.
-   **Encrypted secrets:** Stored in the `./secrets.enc` directory. These files are encrypted versions of the plaintext secrets and are safe to commit to the repository.

## Secret Management

To ensure security and streamline development, Fleepness uses a secret management workflow that includes:

-   Keeping sensitive data encrypted in the repository.
-   Decrypting secrets locally for development and deployment.
-   Automating secret encryption and validation before commits.

## Pre-commit Automation

This project uses [Husky](https://typicode.github.io/husky) and [lint-staged](https://github.com/okonet/lint-staged) to automate checks and formatting before code is committed. This helps maintain code quality and consistency by:

-   Running linters on staged files.
-   Validating secret files to ensure encryption standards.
-   Preventing commits that do not meet the project’s quality requirements.

## Cross-platform Notes

Developers working on different operating systems (Linux, macOS, Windows) should ensure that:

-   Node.js and npm/yarn are installed to support Husky and lint-staged.
-   Necessary PHP extensions and Laravel dependencies are properly configured.
-   Environment variables and secret decryption steps are followed according to the platform-specific guidelines.

---

Thank you for contributing to Fleepness! For further details on setup and development, please refer to the project documentation or contact the maintainers.
