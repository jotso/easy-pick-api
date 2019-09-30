
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table 00_phpunit_easy_pick_codes
# ------------------------------------------------------------

CREATE TABLE `00_phpunit_easy_pick_codes` (
  `code` varchar(255) DEFAULT NULL,
  `used` tinyint(255) unsigned DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table 00_phpunit_easy_pick_text
# ------------------------------------------------------------

CREATE TABLE `00_phpunit_easy_pick_text` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table 00_phpunit_easy_pick_users
# ------------------------------------------------------------

CREATE TABLE `00_phpunit_easy_pick_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datum` date DEFAULT NULL,
  `tijd` time DEFAULT NULL,
  `serial` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `naam` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `optin` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
