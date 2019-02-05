/*
 * Note: future versions would not be possible, because time is always marching forward.
 * A better way to achieve future date constraints would be on the table level, requiring
 * date > added_at::date or something similar
 */

--past timestamp

CREATE DOMAIN TIMESTAMP_PAST AS TIMESTAMP(0)
	CONSTRAINT timestamp_past_check CHECK (VALUE <= CURRENT_TIMESTAMP(0));

--past date

CREATE DOMAIN DATE_PAST AS DATE
	CONSTRAINT date_past_check CHECK ( VALUE <= CURRENT_DATE );
