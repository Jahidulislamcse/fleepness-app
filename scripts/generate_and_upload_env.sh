#!/usr/bin/env bash
set -e

# Configuration
ENV_FILE=".env"
SECRETS_FILE="$HOME/secrets/secrets.json"
REMOTE_PATH="/var/www/backend_v2/.env"

# Generate .env from secrets.json
echo "🔧 Generating .env from $SECRETS_FILE..."
jq -r 'to_entries | map("\(.key)=\(.value)") | .[]' "$SECRETS_FILE" > "$ENV_FILE"

echo "✅ .env generated successfully"

# Upload to remote server
echo "🚀 Uploading .env to $SSH_HOST..."
sshpass -p "$SSH_PASSWORD" scp "$ENV_FILE" "$SSH_USER@$SSH_HOST:$REMOTE_PATH"

echo "✅ .env uploaded successfully"