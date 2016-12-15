-- Fix "null" strings in "case".official_data
UPDATE "case" SET official_data = NULL WHERE official_data = 'null';