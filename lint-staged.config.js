/**
 * @filename: lint-staged.config.js
 * @type {import('lint-staged').Configuration}
 */
export default {
    "gpg-passfile.txt": [
        "echo '❌ gpg-passfile.txt must not be committed!' && exit 1",
    ],
    "secrets.*.json": (stagedFiles) => {
        return [
            `echo '❌ ${stagedFiles.join(
                ", "
            )} must not be committed!' && exit 1`,
        ];
    },
};
