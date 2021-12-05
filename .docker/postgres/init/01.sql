SELECT 'CREATE DATABASE symfony_test'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'symfony_test')\gexec