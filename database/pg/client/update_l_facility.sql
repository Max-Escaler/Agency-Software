INSERT INTO l_facility 
	(
    	facility_code,
    	description,
    	category,
    	facility_code_old,
    	housing_status
	)

VALUES
	('FOSTER','Foster care home or foster care group home','Foster Care','','HOUSED'),
	('JUV_DETEN','Juvenile Detention Center','Juvenile Detention Center','','INSTITUT'),
	('OUTSIDE','Street, greenbelt or other public place','Streets','','HOMELESS'),
	('VEHICLE','Car or other vehicle','Streets','','HOMELESS'),
	('BUILDING','Abandoned or unoccupied building','Streets','','HOMELESS'),
	('TRANS_SITE','Transportation Site','Streets','','HOMELESS'),
	('PUBLIC_HSG','Public Housing (operated by Housing Authority)','Permanent Housing','','HOUSED'),
	('SUBSIDIZED','Other subsidized housing','Permanent Housing','','HOUSED'),
	('HOMEOWNER','Homeownership','Permanent Housing','','HOUSED'),
	('STREET_OLD','Street, car or other public place - OLD','Streets','104','HOMELESS');