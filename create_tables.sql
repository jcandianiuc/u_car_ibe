START TRANSACTION;

CREATE TABLE `user` (
	`id`		int(11) UNSIGNED NOT NULL,
	`password`	varchar(32) NOT NULL,
	`phone`		varchar(14) NOT NULL,
	`name`		varchar(50) NOT NULL,
	`token`		char(32) NOT NULL,
	`verified`	boolean NOT NULL DEFAULT false,
	PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `trip` (
	`id`		int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id`	int(5) UNSIGNED NOT NULL,
	`role`		varchar(20) NOT NULL,
	`datetime`	datetime(6) NOT NULL,
	`direction`	varchar(40) NOT NULL,
	PRIMARY KEY(`id`),
	FOREIGN KEY(`user_id`) REFERENCES `user`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `marker` (
	`id`		int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`trip_id`	int(11) UNSIGNED NOT NULL,
	`latitude`	DOUBLE NOT NULL,
	`longitude`	DOUBLE NOT NULL,
	PRIMARY KEY(`id`),
	FOREIGN KEY(`trip_id`) REFERENCES `trip`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `match` (
	`driver_trip_id`	int(11) UNSIGNED NOT NULL,
	`passenger_trip_id`	int(11) UNSIGNED NOT NULL,
	`driver_status`		tinyint UNSIGNED NOT NULL DEFAULT 0,
	`passenger_status`	tinyint UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY(`driver_trip_id`,`passenger_trip_id`),
	FOREIGN KEY(`driver_trip_id`) REFERENCES `trip`(`id`),
	FOREIGN KEY(`passenger_trip_id`) REFERENCES `trip`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

COMMIT;