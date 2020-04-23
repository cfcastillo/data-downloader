CREATE TABLE business (
	businessId BINARY(16) NOT NULL,
	businessName VARCHAR(128) NOT NULL,
	businessYelpId VARCHAR(32) NOT NULL,
	businessYelpUrl VARCHAR(255) NULL,
	businessLat DECIMAL(10,8) NULL,
	businessLong DECIMAL(11,8) NULL,
	PRIMARY KEY(businessId),
	INDEX(businessName)
)