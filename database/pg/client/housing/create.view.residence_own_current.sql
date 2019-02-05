CREATE OR REPLACE VIEW residence_own_current AS
SELECT * from residence_own
WHERE residence_date <= target_date()
AND COALESCE(residence_date_end,target_date()) >= target_date();

