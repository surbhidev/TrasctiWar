DO $$ 
BEGIN 
    IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'root') THEN
        CREATE ROLE root LOGIN PASSWORD 'surbhi@postgres' SUPERUSER;
    END IF;
END $$;