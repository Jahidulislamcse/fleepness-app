#!/usr/bin/env bash

# Decrypt the file
mkdir -p "$HOME/secrets"
# --batch to prevent interactive command
# --yes to assume "yes" for questions
gpg --quiet --batch --yes --decrypt --passphrase="$LARGE_SECRET_PASSPHRASE" \
--output $HOME/secrets/secrets.json $SECRETS_GPG_PATH
