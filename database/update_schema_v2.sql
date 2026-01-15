-- update_schema_v2.sql

-- Add category_id (nullable initially, but good to enforce later)
ALTER TABLE budget_allocations ADD COLUMN category_id INT DEFAULT NULL;
ALTER TABLE budget_allocations ADD CONSTRAINT fk_allocation_category FOREIGN KEY (category_id) REFERENCES categories(id);

-- Add is_deleted for soft deletes
ALTER TABLE budget_allocations ADD COLUMN is_deleted BOOLEAN DEFAULT 0;
