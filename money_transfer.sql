

-- Create users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(200) NOT NULL,
    balance DECIMAL(10,2) NOT NULL DEFAULT 100.00 CHECK (balance >= 0),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    biography TEXT,
    profile_image BYTEA
);

-- Create transactions table
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    sender_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    receiver_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    amount DECIMAL(18,2) NOT NULL CHECK (amount > 0),
    comment TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Create user_activity_logs table
CREATE TABLE user_activity_logs (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    webpage VARCHAR(255) NOT NULL,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NOT NULL
);

-- Insert initial data into users table
INSERT INTO users (username, first_name, last_name, email, phone, password, balance, created_at, biography, profile_image)
VALUES
    ('user1', 'John', 'Doe', 'john.doe@example.com', '+1234567890', 'hashed_password', 100.00, NOW(), NULL, NULL),
    ('user2', 'Jane', 'Smith', 'jane.smith@example.com', '+9876543210', 'hashed_password', 100.00, NOW(), NULL, NULL);

-- Insert initial data into transactions table
INSERT INTO transactions (sender_id, receiver_id, amount, comment, created_at)
VALUES
    (1, 2, 10.00, 'Test transaction 1', NOW()),
    (2, 1, 5.00, 'Test transaction 2', NOW());

-- Insert initial data into user_activity_logs table
INSERT INTO user_activity_logs (username, webpage, timestamp, ip_address)
VALUES
    ('user1', '/profile.php', NOW(), '127.0.0.1'),
    ('user2', '/profile.php', NOW(), '127.0.0.1');

-- Create a function to transfer money (replaces MySQL procedure)
CREATE OR REPLACE FUNCTION transfer_money(
    sender_id INT,
    receiver_id INT,
    amount DECIMAL(18,2),
    transfer_comment TEXT
) RETURNS VOID AS $$
BEGIN
    -- Deduct money from sender if balance is enough
    UPDATE users 
    SET balance = balance - amount 
    WHERE id = sender_id AND balance >= amount;

    -- Check if the update was successful (i.e., sufficient balance)
    IF FOUND THEN
        -- Add money to receiver
        UPDATE users 
        SET balance = balance + amount 
        WHERE id = receiver_id;
        
        -- Insert into transaction history
        INSERT INTO transactions (sender_id, receiver_id, amount, comment) 
        VALUES (sender_id, receiver_id, amount, transfer_comment);
    ELSE
        -- Raise an exception if insufficient balance
        RAISE EXCEPTION 'Insufficient balance for sender_id %', sender_id;
    END IF;
END;
$$ LANGUAGE plpgsql;