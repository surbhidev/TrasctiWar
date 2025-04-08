#!/bin/bash

# Prompt for PostgreSQL binary path
echo -n "Enter the full path to the PostgreSQL psql binary (e.g., /usr/bin/psql): "
read -r POSTGRES_PATH


# Database credentials
DB_USER="root"
DB_PASSWORD="surbhi@postgres"  # If you have a password, set it here or use PGPASSWORD
DB_NAME="money_transfer"
DB_PORT="5432"       # Default PostgreSQL port

# Number of accounts to create
NUM_ACCOUNTS=10

# Generate accounts
for ((i = 1; i <= NUM_ACCOUNTS; i++)); do
    USERNAME="user$RANDOM"
    FIRST_NAME="FirstName$i"
    LAST_NAME="LastName$i"
    EMAIL="user$RANDOM@example.com"
    PHONE="+91$(shuf -i 1000000000-9999999999 -n 1)"
    PASSWORD=$(openssl rand -base64 12) # Generate secure random password
    BALANCE=100

    # Insert into PostgreSQL database
    PGPASSWORD=$DB_PASSWORD "$POSTGRES_PATH" -U "$DB_USER" -d "$DB_NAME" -p "$DB_PORT" -c "
INSERT INTO users (username, first_name, last_name, email, phone, password, balance) 
VALUES ('$USERNAME', '$FIRST_NAME', '$LAST_NAME', '$EMAIL', '$PHONE', crypt('$PASSWORD', gen_salt('bf')), $BALANCE);
"

    echo "Account created: Username=$USERNAME, Password=$PASSWORD, Balance=$BALANCE, Email=$EMAIL"
done

echo "$NUM_ACCOUNTS accounts created successfully."