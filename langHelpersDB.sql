CREATE TABLE User (
    userID int AUTO_INCREMENT not null,
    userEmail varchar(100) not null,
    userNickname varchar(30) not null,
    userPassword varchar(30) not null,
    userFirstName varchar(20) not null,
    userLastName varchar(20) not null,
	hash varchar(32) not null,
    active boolean not null,
	pickedLanguage boolean not null,
    primary key (userID),
    unique key (userEmail, userNickname)
    ) Engine = InnoDB;
	
CREATE TABLE Language (
    languageID int AUTO_INCREMENT not null,
    languageName varchar(20) not null,
    primary key (languageID)
    ) Engine = InnoDB;
	
CREATE TABLE UserLanguage (
    userID int,
    languageID int,
	primary key (userID),
    foreign key (userID) references User (userID) on delete cascade,
    foreign key (languageID) references Language (languageID) on delete cascade
    ) Engine = InnoDB;
	
CREATE TABLE Question (
    questionID int auto_increment not null,
    questionTitle varchar(50) not null,
    questionContent varchar(500) not null,
	questionDateTime datetime not null,
    closed boolean not null,
    languageID int,
    primary key (questionID),
    foreign key (languageID) references Language (languageID)
    ) Engine = InnoDB;
	
CREATE TABLE UserQuestion (
    userID int,
    questionID int,
	primary key (userID, questionID),
    foreign key (userID) references User (userID) on delete cascade,
    foreign key (questionID) references Question (questionID) on delete cascade
    ) Engine = InnoDB;
	
CREATE TABLE Answer (
	answerID int auto_increment not null,
	answerContent varchar(500) not null,
	answerDateTime datetime not null,
	questionID int,
	primary key (answerID),
	foreign key (questionID) references Question (questionID) on delete cascade
	) Engine = InnoDB;
	
CREATE TABLE UserAnswer (
	userID int,
	answerID int,
	primary key (userID, answerID),
	foreign key (userID) references User (userID) on delete cascade,
	foreign key (answerID) references Answer (answerID) on delete cascade
	) Engine = InnoDB;