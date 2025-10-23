#!/usr/bin/env bash
set -e

# Configuration
SECRETS_FILE="$HOME/secrets/firebase_credentials.json"
REMOTE_PATH="/var/www/backend_v2/firebase_credentials.json"

# Upload to remote server
echo "🚀 Uploading firebase_credentials.json to $SSH_HOST..."
sshpass -p "$SSH_PASSWORD" scp "$SECRETS_FILE" "$SSH_USER@$SSH_HOST:$REMOTE_PATH"

echo "✅ firebase_credentials.json uploaded successfully"