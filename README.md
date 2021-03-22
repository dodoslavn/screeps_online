# screeps_online
srcreeps.online


crontab
* * * * * /path/to/screeps_online/backend/scan.sh


SQL
CREATE TABLE `server_availability` (
 `id_availability` int(3) NOT NULL AUTO_INCREMENT,
 `server_id` int(3) NOT NULL,
 `checks` int(10) NOT NULL,
 `successful` int(10) NOT NULL,
 PRIMARY KEY (`id_availability`),
 UNIQUE KEY `server_id` (`server_id`)
) ENGINE=InnoDB AUTO_INCREMENT=99475 DEFAULT CHARSET=utf8

CREATE TABLE `server_availability` (
 `id_availability` int(3) NOT NULL AUTO_INCREMENT,
 `server_id` int(3) NOT NULL,
 `checks` int(10) NOT NULL,
 `successful` int(10) NOT NULL,
 PRIMARY KEY (`id_availability`),
 UNIQUE KEY `server_id` (`server_id`)
) ENGINE=InnoDB AUTO_INCREMENT=99475 DEFAULT CHARSET=utf8

CREATE TABLE `server_list` (
 `id_server` int(3) NOT NULL AUTO_INCREMENT,
 `address` varchar(50) NOT NULL,
 PRIMARY KEY (`id_server`),
 UNIQUE KEY `address` (`address`),
 UNIQUE KEY `address_2` (`address`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8

CREATE TABLE `users` (
 `id_user` int(3) NOT NULL AUTO_INCREMENT,
 `name` varchar(20) NOT NULL,
 `email` varchar(50) NOT NULL,
 `password` varchar(50) NOT NULL,
 PRIMARY KEY (`id_user`),
 UNIQUE KEY `name` (`name`),
 UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8

CREATE TABLE `web_statistic` (
 `id` int(10) NOT NULL AUTO_INCREMENT,
 `user` varchar(15) NOT NULL,
 `browser` varchar(50) NOT NULL,
 `os` varchar(50) NOT NULL,
 `all` text NOT NULL,
 `date` varchar(10) NOT NULL,
 `time` varchar(10) NOT NULL,
 `ip` varchar(15) NOT NULL,
 `stime` varchar(255) NOT NULL,
 `sid` varchar(255) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `sid` (`sid`)
) ENGINE=MyISAM AUTO_INCREMENT=325 DEFAULT CHARSET=utf8
