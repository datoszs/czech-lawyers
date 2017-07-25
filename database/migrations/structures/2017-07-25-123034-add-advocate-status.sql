
-- Prepare new enum type
ALTER TYPE advocate_status RENAME TO advocate_status_old;

CREATE TYPE advocate_status AS ENUM (
  'active', /* Advocate is active. */
  'suspended', /* Advocates activity is suspended. */
  'removed', /* Advocate was removed or is inactive. */
  'created' /* We don't know */
);

ALTER TABLE advocate_info ALTER COLUMN status TYPE advocate_status USING (status::text)::advocate_status;

-- Remove old (and now unused enum type)
DROP TYPE advocate_status_old;

ALTER TABLE advocate_info ADD COLUMN company TEXT;
ALTER TABLE advocate_info ADD COLUMN data_box TEXT;
ALTER TABLE advocate_info ADD COLUMN ex_offo TEXT;
ALTER TABLE advocate_info ADD COLUMN way_of_practicing_advocacy TEXT;

COMMENT ON COLUMN advocate_info.data_box IS 'ID of data-box of advocate';
COMMENT ON COLUMN advocate_info.ex_offo IS 'Information about ex-offo provision';
COMMENT ON COLUMN advocate_info.way_of_practicing_advocacy IS 'The way of practicing advocacy (samostatný advokát, ve sdružení apod.)';
COMMENT ON COLUMN advocate_info.company IS 'Company where advocate working'
