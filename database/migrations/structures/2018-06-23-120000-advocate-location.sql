-- Extensions needs to be created by privileged user on the database (as it depends on system installed package)
-- Version >= 2.3 is expected.
-- CREATE EXTENSION postgis;

-- Add column location
ALTER TABLE advocate_info ADD COLUMN location POINT NULL;
CREATE INDEX advocate_info_location_index  ON advocate_info USING GIST(location);

-- Add data for geocoding advocates job
INSERT INTO job (name, description, database_user_id) VALUES ('App\Commands\GeocodeAdvocates', 'Geocoding of advocate addresses.', 1);
