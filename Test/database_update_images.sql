-- Update database to support product images
-- Run this SQL in phpMyAdmin to add image support

-- Add image_url column if it doesn't exist
ALTER TABLE products ADD COLUMN image_url VARCHAR(255) NULL AFTER description;

-- Update existing products with placeholder image paths (optional)
UPDATE products SET image_url = NULL WHERE image_url IS NULL;