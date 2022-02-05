/*  This file contains the qeuries used to generate the associated db.
    the other .sql file contains the db dump */

USE sgrlampr;

DROP TABLE IF EXISTS Signings;
DROP TABLE IF EXISTS Players;
DROP TABLE IF EXISTS Teams;

DROP PROCEDURE IF EXISTS insert_initial_data;
DROP PROCEDURE IF EXISTS update_avg_ages;
DROP TEMPORARY TABLE IF EXISTS player_ages;

/* Create the tables */
-- table to hold teams
CREATE TABLE Teams (
    id 			INT NOT NULL AUTO_INCREMENT UNIQUE,
    `name` 		TEXT NOT NULL,
    sport 		TEXT NOT NULL,
    average_age FLOAT,
    
    PRIMARY KEY (id)
);

-- table to hold players (fk link to teams)
CREATE TABLE Players (
	id 				INT  NOT NULL AUTO_INCREMENT UNIQUE,
    surname 		TEXT NOT NULL,
    forenames 		TEXT NOT NULL,
    nationality 	TEXT NOT NULL,
    date_of_birth 	DATE NOT NULL,

    team_id         INT  DEFAULT NULL,
    
    PRIMARY KEY (id),
    CONSTRAINT fk_teamid_Team		FOREIGN KEY (team_id) 		REFERENCES Teams(id)
);



/* Create the initial information for the assigment */
DELIMITER //
CREATE PROCEDURE insert_initial_data()

BEGIN
	/* Initlal Teams */
	INSERT INTO Teams VALUES
		(NULL, 'Liverpool', 'Football', null),
        (NULL, 'Manchester','Tennis',   null),
        (NULL, 'Liverpool', 'Rugby',    null);

	/* Initial Players */
    INSERT INTO Players VALUES 
		(NULL, 'Smith',     'John',     'UK', '1989-11-11', 1),
		(NULL, 'Teri',      'Phebe',    'UK', '1966-10-23', 1),
        (NULL, 'Jared',     'Sybil',    'UK', '2001-09-24', 1),
        (NULL, 'Lynnette',  'Sharmaine','UK', '1989-05-26', 2),
        (NULL, 'Cuthbert',  'Jenna',    'UK', '1978-04-23', 2),
        (NULL, 'Idella',    'Sunny',    'UK', '1995-03-12', 2),
        (NULL, 'Kayleah',   'Carley',   'UK', '1997-02-08', 3),
        (NULL, 'Grier',     'Nat',      'UK', '1999-01-15', 3),
        (NULL, 'Deven',     'Ben',      'UK', '2004-12-18', 3);

	
    /* Update all the the average ages for the teams */
    -- CALL update_avg_ages()
        
END //
DELIMITER ;


/* Calculate the average age of a team (based on player ages) */
DELIMITER //
CREATE PROCEDURE update_avg_ages(

    IN teamId INT
)
BEGIN
	-- Ages of all players in the team
	CREATE TEMPORARY TABLE player_ages
	SELECT
		Players.team_id,
		TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) AS age
    FROM 
        Players
	WHERE
        Players.team_id = teamId;

    -- Update Team table average age
    UPDATE Teams
    SET 
		average_age = 	
			-- Average age of the players
			(SELECT 
				avg(age) AS avg_age
			FROM player_ages
			LIMIT 1)
    WHERE
        Teams.id = teamId;

	DROP TEMPORARY TABLE IF EXISTS player_ages;
END //
DELIMITER ;


 
-- Run the initial insert
CALL insert_initial_data();

-- Update the average ages of the teams
CALL update_avg_ages(1);
CALL update_avg_ages(2);
CALL update_avg_ages(3);