CREATE OR REPLACE VIEW l_service_cd AS
SELECT service_code AS service_cd_code,
description
FROM l_service WHERE is_current AND used_by_cd;
