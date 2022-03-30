#01/03/22
ALTER TABLE `crm_departments` ADD `deleted_at` TIMESTAMP NULL AFTER `updated_at`;

ALTER TABLE `payor_service_rates` ADD `base_rate_mileage` DOUBLE NULL AFTER `loaded_base_rate_bmm_out`;

ALTER TABLE `payor_service_rates` ADD `base_rate_per_mile` DOUBLE NULL AFTER `base_rate_mileage`;

ALTER TABLE `payor_service_rates` ADD `flat_rate_out` DOUBLE NULL AFTER `base_rate_per_mile`;

ALTER TABLE `payor_service_rates` CHANGE `unloaded_rate_per_min_base` `unloaded_rate_per_hr_base` DOUBLE NULL DEFAULT NULL;

ALTER TABLE `payor_service_rates` CHANGE `loaded_rate_per_min_base` `loaded_rate_per_hr_base` DOUBLE NULL DEFAULT NULL;

ALTER TABLE `payor_service_rates` CHANGE `unloaded_rate_per_min_base_out` `unloaded_rate_per_hr_base_out` DOUBLE NULL DEFAULT NULL;

ALTER TABLE `payor_service_rates` CHANGE `loaded_rate_per_min_base_out` `loaded_rate_per_hr_base_out` DOUBLE NULL DEFAULT NULL;


#02/03/22
ALTER TABLE `payor_contracts` ADD `default` TINYINT(2) NOT NULL DEFAULT '0' AFTER `payor_id`;

#03/03/22
ALTER TABLE `payor_service_rates` DROP `unloaded_rate_per_min`, DROP `loaded_rate_per_min`, DROP `unloaded_rate_per_mile_base`, DROP `loaded_rate_per_mile_base_out`, DROP `unloaded_rate_per_mile_base_out`, DROP `unloaded_base_rate`, DROP `loaded_base_rate`, DROP `loaded_rate_per_mile_bmm`, DROP `passenger_no_show`, DROP `late_cancellation`, DROP `base_rate_bmm`, DROP `loaded_base_rate_bmm`, DROP `unloaded_rate_per_min_out`, DROP `loaded_rate_per_min_out`, DROP `unloaded_base_rate_out`, DROP `loaded_base_rate_out`, DROP `passenger_no_show_out`, DROP `late_cancellation_out`, DROP `base_rate_bmm_out`, DROP `loaded_rate_per_mile_bmm_out`, DROP `loaded_base_rate_bmm_out`;

ALTER TABLE `driver_service_rates` ADD `unloaded_rate_per_hr_base` DOUBLE NULL DEFAULT NULL AFTER `loaded_base_rate_bmm_out`;

ALTER TABLE `driver_service_rates` ADD `loaded_rate_per_hr_base` DOUBLE NULL DEFAULT NULL AFTER `unloaded_rate_per_hr_base`;

ALTER TABLE `driver_service_rates` ADD `base_rate_mileage` DOUBLE NULL DEFAULT NULL AFTER `loaded_rate_per_hr_base`;

ALTER TABLE `driver_service_rates` ADD `base_rate_per_mile` DOUBLE NULL DEFAULT NULL AFTER `base_rate_mileage`;

ALTER TABLE `driver_service_rates` ADD `loaded_rate_per_mile_base` DOUBLE NULL DEFAULT NULL AFTER `base_rate_per_mile`;

ALTER TABLE `driver_service_rates` ADD `unloaded_rate_per_hr_base_out` DOUBLE NULL DEFAULT NULL AFTER `loaded_rate_per_mile_base`;

ALTER TABLE `driver_service_rates` ADD `loaded_rate_per_hr_base_out` DOUBLE NULL DEFAULT NULL AFTER `unloaded_rate_per_hr_base_out`;

ALTER TABLE `driver_service_rates` ADD `flat_rate_out` DOUBLE NULL DEFAULT NULL AFTER `loaded_rate_per_hr_base_out`;



#04/03/22
ALTER TABLE `crm` CHANGE `bank` `bank` VARCHAR(191) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;


#22/03/22
ALTER TABLE `trip_master_ut` ADD `member_sign` VARCHAR(250) NULL DEFAULT NULL AFTER `insurance_amount`;