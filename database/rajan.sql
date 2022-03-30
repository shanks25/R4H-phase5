ALTER TABLE `vehicle_master_ut` CHANGE `mileage` `odometer` VARCHAR(191) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'odometer on vehicle created on R4H platform';
ALTER TABLE `auto_sets` ADD `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `payable_type`;
ALTER TABLE `auto_sets` CHANGE `buffer_time` `auto_set_time` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `base_location_master` ADD `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `user_id`;
ALTER TABLE `driver_master_ut` CHANGE `second_phone` `second_phone` VARCHAR(12) NULL DEFAULT NULL;
ALTER TABLE `driver_master_ut` ADD `work_status` INT(2) NOT NULL AFTER `idependent_contractor_work_in_past`, ADD `position` VARCHAR(255) NOT NULL AFTER `work_status`;
ALTER TABLE `driver_master_ut` ADD `insurance_status` INT NULL COMMENT '1=Active,0=Expire' AFTER `position`, ADD `insurance_id` VARCHAR(255) NULL AFTER `insurance_status`, ADD `work_timing` TIME NULL AFTER `insurance_id`, ADD `stretcher` VARCHAR(255) NULL AFTER `work_timing`, ADD `notes` TEXT NULL AFTER `stretcher`;
CREATE TABLE `driver_level_of_service` (
  `id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `level_of_service_id` int(11) NOT NULL COMMENT 'belongs to master_level_of_service'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `driver_level_of_service`
  ADD PRIMARY KEY (`id`),
  ADD KEY `level_of_service_id` (`level_of_service_id`),
  ADD KEY `vehicle_id` (`driver_id`);
ALTER TABLE `driver_level_of_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

ALTER TABLE `service_master_ut` CHANGE `service_name` `name` VARCHAR(255) NOT NULL;

RENAME TABLE `ride4helth`.`service_master_ut` TO `ride4helth`.`vehile_service_master_ut`;