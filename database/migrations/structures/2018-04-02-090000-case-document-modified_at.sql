ALTER TABLE "case" ADD COLUMN modified TIMESTAMP NULL;

UPDATE "case" SET modified = inserted WHERE official_data IS NOT NULL OR proposition_date IS NOT NULL OR decision_date IS NOT NULL;

ALTER TABLE "document" ADD COLUMN modified TIMESTAMP NULL;

