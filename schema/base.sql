/*
MySQL Data Transfer
Source Host: localhost
Source Database: quickpiweather
Target Host: localhost
Target Database: review
Date: 23/11/2020 10:27:40
*/

-- ----------------------------------------
-- Table structure for temperatures cache
-- ----------------------------------------
DROP TABLE IF EXISTS `temperatures`;
CREATE TABLE `temperatures (
    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `town` VARCHAR(50) NOT NULL,
    `temp` FLOAT(6) NOT NULL,
    `last_update` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
