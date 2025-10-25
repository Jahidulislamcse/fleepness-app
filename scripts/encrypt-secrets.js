import { parseArgs } from "node:util";
import { createHash } from "node:crypto";
import { execSync } from "node:child_process";
import { readFileSync, writeFileSync, existsSync } from "node:fs";

function main() {
    // Define colors object using process.stdout.isTTY to enable/disable colors
    const colors = {
        red: process.stdout.isTTY ? "\x1b[31m" : "",
        green: process.stdout.isTTY ? "\x1b[32m" : "",
        yellow: process.stdout.isTTY ? "\x1b[33m" : "",
        reset: process.stdout.isTTY ? "\x1b[0m" : "",
    };

    // Parse command-line arguments with short and long options
    // Options include:
    // - preset: a predefined environment like 'production' or 'staging'
    // - source: the source file containing secrets to encrypt
    // - hash: the file storing the hash of the source file to detect changes
    // - encrypted: the output file path for the encrypted secrets
    const args = parseArgs({
        options: {
            preset: { type: "string", short: "p" },
            source: { type: "string", short: "S" },
            hash: { type: "string", short: "H" },
            encrypted: { type: "string", short: "E" },
            passfile: { type: "string", short: "P" },
        },
    });

    // Make passfile argument mandatory
    if (!args.values.passfile) {
        console.log(
            `${colors.red}Error: The passfile argument (-P) is required.${colors.reset}`
        );
        process.exit(1);
    }

    // Validate that if passfile is provided, the file exists
    if (args.values.passfile && !existsSync(args.values.passfile)) {
        console.log(
            `${colors.red}Error: Passfile "${args.values.passfile}" does not exist.${colors.reset}`
        );
        process.exit(1);
    }

    // Initialize variables to hold file paths for source, hash, and encrypted files
    let SOURCE_FILE;
    let SOURCE_HASH_FILE;
    let ENCRYPTED_FILE;

    // Determine the source file path based on provided arguments or preset
    if (args.values.source) {
        // If the user explicitly provided a source file, use it
        SOURCE_FILE = args.values.source;
    } else if (args.values.preset === "production") {
        // Use the production secrets file if preset is 'production'
        SOURCE_FILE = "secrets.production.json";
    } else if (args.values.preset === "staging") {
        // Use the staging secrets file if preset is 'staging'
        SOURCE_FILE = "secrets.staging.json";
    }

    // Determine the hash file path similarly, which stores the hash of the last encrypted source file
    if (args.values.hash) {
        // Use the user-provided hash file path if given
        SOURCE_HASH_FILE = args.values.hash;
    } else if (args.values.preset === "production") {
        // Default hash file for production preset
        SOURCE_HASH_FILE = ".secrets.production.hash";
    } else if (args.values.preset === "staging") {
        // Default hash file for staging preset
        SOURCE_HASH_FILE = ".secrets.staging.hash";
    }

    // Determine the output encrypted file path based on arguments or preset
    if (args.values.encrypted) {
        // Use user-provided encrypted file path if specified
        ENCRYPTED_FILE = args.values.encrypted;
    } else if (args.values.preset === "production") {
        // Default encrypted file path for production preset
        ENCRYPTED_FILE = "secrets/production/secrets.json.gpg";
    } else if (args.values.preset === "staging") {
        // Default encrypted file path for staging preset
        ENCRYPTED_FILE = "secrets/staging/secrets.json.gpg";
    }

    // Validate that all necessary file paths have been determined
    // If any are missing, print an error message and exit the script
    if (!SOURCE_FILE || !SOURCE_HASH_FILE || !ENCRYPTED_FILE) {
        console.log(
            `${colors.red}Error: You must specify either a preset (-p) or all of source (-S), hash (-H), and encrypted (-E) options.${colors.reset}`
        );
        process.exit(1);
    }

    // Check if the source file exists before proceeding
    // If not found, log a warning and skip encryption by returning early
    if (!existsSync(SOURCE_FILE)) {
        console.log(
            `${colors.yellow}⚠️  ${SOURCE_FILE} not found, skipping encryption.${colors.reset}`
        );
        return;
    }

    // Read the contents of the source file to compute its hash
    const content = readFileSync(SOURCE_FILE);

    // Compute the SHA-256 hash of the source file contents
    // This hash is used to detect changes and avoid unnecessary encryption
    const currentHash = createHash("sha256").update(content).digest("hex");

    // Initialize variable to hold the previous hash value (empty if none exists)
    let prevHash = "";
    if (existsSync(SOURCE_HASH_FILE))
        prevHash = readFileSync(SOURCE_HASH_FILE, "utf8");

    // Compare the current hash with the previous hash to determine if the source file changed
    if (currentHash !== prevHash) {
        // If hashes differ, a change is detected — proceed with encryption
        console.log(
            `${colors.green}Detected change — ${SOURCE_FILE} → ${ENCRYPTED_FILE}${colors.reset}`
        );

        // Execute the GPG command to symmetrically encrypt the source file using AES256 cipher
        // --yes and --batch options ensure non-interactive operation
        execSync(
            `gpg --yes --batch --symmetric --cipher-algo AES256 --pinentry-mode loopback --passphrase-file "${args.values.passfile}" --output "${ENCRYPTED_FILE}" "${SOURCE_FILE}"`,
            { stdio: "inherit" } // Pipe stdout and stderr to the console for visibility
        );

        // After successful encryption, update the hash file with the new hash value
        writeFileSync(SOURCE_HASH_FILE, currentHash);

        // Stage the encrypted file in Git to prepare for commit
        // This automates adding the updated encrypted secrets to version control
        execSync(`git add "${ENCRYPTED_FILE}"`, { stdio: "inherit" });
    } else {
        // If no changes detected, log a message and skip encryption
        console.log(
            `${colors.green}No change detected, skipping encryption.${colors.reset}`
        );
    }
}

main();
