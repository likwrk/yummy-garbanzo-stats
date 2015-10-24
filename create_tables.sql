CREATE TABLE `answers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `student_id` INT(11) NOT NULL,
    `question` INT(11) NOT NULL,
    `answer` INT(11) NOT NULL,
    INDEX `id` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `points` (
    `student_id` INT(11) NOT NULL,
    `points_total` INT(11) NOT NULL,
    `points_1_10` INT(11) NOT NULL,
    `points_11_15` INT(11) NOT NULL,
    PRIMARY KEY (`student_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `students` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `class` VARCHAR(20) NOT NULL,
    `sex` VARCHAR(20) NOT NULL,
    PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
