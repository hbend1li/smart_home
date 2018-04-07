# smart_home


## MySQL

```  
SET NAMES utf8;
SET time_zone = '+00:00';

CREATE TABLE `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(50) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `idle` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cmd` varchar(50) NOT NULL,
  `msg` text,
  KEY `id` (`id`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`id`) REFERENCES `devices` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```  
  
### test 
>  a0d697c6-754b-4f68-b7db-278f088b29e5  
