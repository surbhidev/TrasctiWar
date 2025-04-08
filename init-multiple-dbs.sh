#!/bin/bash
set -e

# Check if the environment variable is set
if [ -z "$POSTGRES_MULTIPLE_DATABASES" ]; then
    echo "ERROR: POSTGRES_MULTIPLE_DATABASES environment variable is not set."
    exit 1
fi

# Split the environment variable into individual database names
for db in $(echo "$POSTGRES_MULTIPLE_DATABASES" | tr ',' ' '); do
    echo "Checking if database $db exists..."
    if psql -U "$POSTGRES_USER" -d postgres -tAc "SELECT 1 FROM pg_database WHERE datname='$db'" | grep -q 1; then
        echo "Database $db already exists. Skipping creation."
    else
        echo "Creating database: $db"
        psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "postgres" <<-EOSQL
            CREATE DATABASE "$db";
EOSQL
    fi
done