IF NOT EXISTS(SELECT * FROM sys.databases WHERE name = 'myDB')
  BEGIN
    CREATE DATABASE [myDB]
    END
    GO
       USE [myDB]
    GO
--You need to check if the table exists

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='user_roles' and xtype='U')
BEGIN
    CREATE TABLE user_roles(id INT NOT NULL CONSTRAINT user_roles_pk PRIMARY KEY NONCLUSTERED, role_name VARCHAR(64) NOT NULL);
    INSERT INTO user_roles VALUES (0, 'Admin'), (1, 'User');
END

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='users' and xtype='U')
BEGIN
    CREATE TABLE users(id VARCHAR(36) NOT NULL CONSTRAINT user_pk PRIMARY KEY NONCLUSTERED, linkblue VARCHAR(36) NOT NULL, role INT DEFAULT 1 NOT NULL, CONSTRAINT user_role_id_fk FOREIGN KEY (role) REFERENCES user_roles (id) ON DELETE CASCADE ON UPDATE CASCADE)
END

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='user_sessions' and xtype='U')
BEGIN
    CREATE TABLE user_sessions(session_id VARCHAR(36) NOT NULL CONSTRAINT user_sessions_pk PRIMARY KEY NONCLUSTERED, user_id VARCHAR(36) NOT NULL CONSTRAINT user_sessions_users_id_fk REFERENCES users ON DELETE CASCADE, last_seen   DATETIME DEFAULT getdate() NOT NULL, remember_me BIT DEFAULT 0 NOT NULL)
END

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID('users') AND name='user_linkblue_uindex')
BEGIN
	CREATE UNIQUE INDEX user_linkblue_uindex ON users (linkblue)
END


IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='devices' and xtype='U')
BEGIN
	CREATE TABLE devices (
		dev_name VARCHAR(32) NOT NULL, 
		dev_desc VARCHAR(64) NOT NULL, 
		dev_eui CHAR(16) NOT NULL PRIMARY KEY, 
		join_mode VARCHAR(4) NOT NULL,
		app_key CHAR(32), 
		dev_addr CHAR(8), 
		netskey CHAR(32), 
		appskey CHAR(32), 
		created_by VARCHAR(36), 
		created_at DATETIME DEFAULT GETDATE(), 
		last_updated VARCHAR(36), 
		last_updated_time DATETIME DEFAULT GETDATE()
	); 
END

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='uplink_packets' and xtype='U')
BEGIN
	CREATE TABLE uplink_packets (
		id INT IDENTITY(1,1) PRIMARY KEY,
		application_id INT NOT NULL,
		application_name VARCHAR(255),
		dev_eui CHAR(16) NOT NULL,
		rx_info NVARCHAR(MAX) NOT NULL,
		tx_info NVARCHAR(MAX) NOT NULL,
		fCnt INT NOT NULL,
		fPort INT NOT NULL,
		data VARCHAR(MAX) NOT NULL,
		time DATETIME NOT NULL,
		CONSTRAINT FK_deveui FOREIGN KEY (dev_eui)
		REFERENCES devices(dev_eui) ON DELETE CASCADE ON UPDATE CASCADE
	);
END