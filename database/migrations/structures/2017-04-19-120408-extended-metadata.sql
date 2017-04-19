-- Add column year to Case
ALTER TABLE 'case' ADD COLUMN year INT;
COMMENT ON COLUMN 'case'.year IS 'A year that should be included in the registry_mark';

-- Add columns on Document supreme administrative court
ALTER TABLE document_supreme_administrative_court ADD COLUMN sides TEXT NULL;
ALTER TABLE document_supreme_administrative_court ADD COLUMN prejudicate TEXT NULL;
ALTER TABLE document_supreme_administrative_court ADD COLUMN complaint TEXT NULL;

COMMENT ON COLUMN document_supreme_administrative_court.sides IS 'Sides of case';
COMMENT ON COLUMN document_supreme_administrative_court.prejudicate IS 'Prejudicates';
COMMENT ON COLUMN document_supreme_administrative_court.complaint IS 'Complaint';

-- Add columns on Document law court
ALTER TABLE document_law_court ADD COLUMN paralel_reference_laws TEXT NULL;
ALTER TABLE document_law_court ADD COLUMN paralel_reference_judgements TEXT NULL;
ALTER TABLE document_law_court ADD COLUMN popular_title TEXT NULL;
ALTER TABLE document_law_court ADD COLUMN decision_date DATE NULL;
ALTER TABLE document_law_court ADD COLUMN delivery_date DATE NULL;
ALTER TABLE document_law_court ADD COLUMN filing_date DATE NULL;
ALTER TABLE document_law_court ADD COLUMN publication_date DATE NULL;
ALTER TABLE document_law_court ADD COLUMN proceedings_type TEXT NULL;
ALTER TABLE document_law_court ADD COLUMN importance INT NULL;
ALTER TABLE document_law_court ADD COLUMN proposer JSONB NULL;
ALTER TABLE document_law_court ADD COLUMN institution_concerned JSONB NULL;
ALTER TABLE document_law_court ADD COLUMN justice_rapporteur TEXT NULL;
ALTER TABLE document_law_court ADD COLUMN contested_act JSONB NULL;
ALTER TABLE document_law_court ADD COLUMN concerned_laws JSONB NULL;
ALTER TABLE document_law_court ADD COLUMN concerned_other JSONB NULL;
ALTER TABLE document_law_court ADD COLUMN dissenting_opinion JSONB NULL;
ALTER TABLE document_law_court ADD COLUMN proceedings_subject JSONB NULL;
ALTER TABLE document_law_court ADD COLUMN subject_index JSONB NULL;
ALTER TABLE document_law_court ADD COLUMN ruling_language TEXT NULL;
ALTER TABLE document_law_court ADD COLUMN note TEXT NULL;
ALTER TABLE document_law_court ADD COLUMN names JSONB NULL;

COMMENT ON COLUMN document_law_court.paralel_reference_laws IS 'Paralel Reference (Collection of Laws)';
COMMENT ON COLUMN document_law_court.paralel_reference_judgements IS 'Paralel Reference (Collection of Judgements and Decisions)';
COMMENT ON COLUMN document_law_court.popular_title IS 'Popular title';
COMMENT ON COLUMN document_law_court.decision_date IS 'Decision date';
COMMENT ON COLUMN document_law_court.delivery_date IS 'Delivery date';
COMMENT ON COLUMN document_law_court.filing_date IS 'Filing date';
COMMENT ON COLUMN document_law_court.publication_date IS 'Publication date';
COMMENT ON COLUMN document_law_court.proceedings_type IS 'Proceedings type';
COMMENT ON COLUMN document_law_court.importance IS 'Importance';
COMMENT ON COLUMN document_law_court.proposer IS 'Proposer';
COMMENT ON COLUMN document_law_court.institution_concerned IS 'Institution Concerned';
COMMENT ON COLUMN document_law_court.justice_rapporteur IS 'Justice Rapporteur';
COMMENT ON COLUMN document_law_court.contested_act IS 'Contested Act';
COMMENT ON COLUMN document_law_court.concerned_laws IS 'Constitutional Laws and International Agreements Concerned';
COMMENT ON COLUMN document_law_court.concerned_other IS 'Other Regulations Concerned';
COMMENT ON COLUMN document_law_court.dissenting_opinion IS 'Dissenting Opinion';
COMMENT ON COLUMN document_law_court.proceedings_subject IS 'Proceedings Subject';
COMMENT ON COLUMN document_law_court.subject_index IS '	Subject Index';
COMMENT ON COLUMN document_law_court.ruling_language IS 'Ruling Language';
COMMENT ON COLUMN document_law_court.note IS 'Note';
COMMENT ON COLUMN document_law_court.names IS 'Names of lawyers from text of decision';


