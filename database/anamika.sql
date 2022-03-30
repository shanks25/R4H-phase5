#08-03-22
ALTER TABLE `provider_master` CHANGE `provider_name` `provider_name` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE `provider_master` CHANGE `provider_fullname` `provider_fullname` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;

#14-03-22
ALTER TABLE `clerical_form` CHANGE `18+` `eighteen_plus` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;


#17-03-22
ALTER TABLE `zone_counties` ADD `deleted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `zone_id`;
#23-03-22

ALTER TABLE `zone_zips` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `zone_id`;


ALTER TABLE `zone_zips` ADD `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `updated_at`;

ALTER TABLE `zone_zips` ADD `deleted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_at`;

ALTER TABLE `zone_states` ADD `deleted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `zone_id`;

#23/03/22
ALTER TABLE `clerical_form` CHANGE `eligible_proof` `eligible_proof` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

ALTER TABLE `clerical_form` CHANGE `date3` `date3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'step4 agreement';

ALTER TABLE `zone_zips` ADD `deleted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_at`;

ALTER TABLE `zone_cities` ADD `deleted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `zone_id`;


23-03-22

ALTER TABLE `zone_master` ADD `state_id` INT(11) NOT NULL AFTER `name`;

ALTER TABLE `zone_master` ADD `county_id` INT(11) NULL DEFAULT NULL AFTER `state_id`;

ALTER TABLE `zone_master` ADD `city_id` INT(11) NULL DEFAULT NULL AFTER `county_id`;

ALTER TABLE `zone_master` ADD `zipcode` INT(11) NOT NULL AFTER `city_id`;


ALTER TABLE `zone_master` CHANGE `state_id` `state_id` INT(11) NULL;
ALTER TABLE `zone_master` CHANGE `zipcode` `zipcode` INT(11) NULL;

28-03-22

ALTER TABLE `complaint_driver` ADD `select_driver` INT(11) NOT NULL AFTER `id`;
ALTER TABLE `zone_counties` CHANGE `deleted_at` `deleted_at` TIMESTAMP NULL DEFAULT NULL;

29-03-2022
ALTER TABLE `complaint_driver` CHANGE `deleted_at` `delete_at` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `zone_cities` CHANGE `deleted_at` `deleted_at` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `zone_states` CHANGE `deleted_at` `deleted_at` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `zone_zips` CHANGE `deleted_at` `deleted_at` TIMESTAMP NULL DEFAULT NULL;