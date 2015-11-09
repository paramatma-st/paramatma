-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema paramatma
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema paramatma
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `paramatma` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `paramatma` ;

-- -----------------------------------------------------
-- Table `paramatma`.`pa_config`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `paramatma`.`pa_config` (
  `path` VARCHAR(255) NOT NULL,
  `value` VARCHAR(255) NULL)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
