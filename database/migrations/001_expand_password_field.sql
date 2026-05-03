-- Expand password field to accommodate modern password hashes
-- Modern hashes (bcrypt, argon2) are 60-255 characters
-- Old crypt() hashes were only ~13 characters

ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL;
