-- Add overall case metadata, for now only optional
ALTER TABLE "case" ADD COLUMN proposition_date DATE NULL;
ALTER TABLE "case" ADD COLUMN decision_date DATE NULL;

UPDATE "case" SET year = SUBSTRING(registry_sign FROM '/(\d{4})$')::INTEGER;
UPDATE "case" SET year = 2000 + SUBSTRING(registry_sign FROM '/(\d{2})$')::INTEGER WHERE SUBSTRING(registry_sign FROM '/(\d{2})$')::INTEGER < 92;
UPDATE "case" SET year = 1900 + SUBSTRING(registry_sign FROM '/(\d{2})$')::INTEGER WHERE SUBSTRING(registry_sign FROM '/(\d{2})$')::INTEGER >= 92;

ALTER TABLE "case" ALTER COLUMN year SET NOT NULL;

COMMENT ON COLUMN "case".proposition_date IS 'Date (if available) of proposition of a case to the court.';
COMMENT ON COLUMN "case".decision_date IS 'Date (if available) of decision of a case by the court.';