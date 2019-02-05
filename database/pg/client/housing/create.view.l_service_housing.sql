CREATE OR REPLACE VIEW l_service_housing AS
SELECT service_code AS service_housing_code,
description
FROM l_service WHERE is_current AND used_by_housing;
