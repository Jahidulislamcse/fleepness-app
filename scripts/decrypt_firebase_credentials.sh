#!/usr/bin/env bash

# Decrypt the file
mkdir -p $HOME/secrets

gpg --quiet --batch --yes --decrypt --passphrase="$LARGE_SECRET_PASSPHRASE" \
--output $HOME/secrets/firebase_credentials.json $FIREBASE_CREDS_GPG_PATH
