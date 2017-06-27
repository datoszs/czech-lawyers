ALTER TABLE "case_disputation" ADD COLUMN tagging_case_result_disputed BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE "case_disputation" ADD COLUMN tagging_advocate_disputed BOOLEAN NOT NULL DEFAULT FALSE;

UPDATE "case_disputation" SET tagging_case_result_disputed = tagging_case_result_id IS NOT NULL;
UPDATE "case_disputation" SET tagging_advocate_disputed = tagging_advocate_id IS NOT NULL;

ALTER TABLE "case_disputation" ADD CHECK (tagging_case_result_disputed OR tagging_advocate_disputed);
