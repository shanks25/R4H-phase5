ALTER TABLE `trip_payout_detail` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT, CHANGE `unloaded_rate_per_mile` `unloaded_rate_per_mile` DECIMAL(10,2) NULL DEFAULT '0', CHANGE `loaded_rate_per_mile` `loaded_rate_per_mile` DECIMAL(10,2) NOT NULL DEFAULT '0', CHANGE `unloaded_rate_per_min` `unloaded_rate_per_min` DECIMAL(10,2) NOT NULL DEFAULT '0', CHANGE `loaded_rate_per_min` `loaded_rate_per_min` DECIMAL(10,2) NOT NULL DEFAULT '0', CHANGE `unloaded_rate_per_hr` `unloaded_rate_per_hr` DECIMAL(10,2) NOT NULL DEFAULT '0', CHANGE `loaded_rate_per_hr` `loaded_rate_per_hr` DECIMAL(10,2) NULL DEFAULT '0', CHANGE `insurance_rate_per_mile` `insurance_rate_per_mile` DECIMAL(10,2) NOT NULL DEFAULT '0', CHANGE `base_rate` `base_rate` DECIMAL(10,2) NOT NULL DEFAULT '0', CHANGE `unloaded_base_rate` `unloaded_base_rate` DECIMAL(10,2) NOT NULL DEFAULT '0', CHANGE `loaded_base_rate` `loaded_base_rate` DECIMAL(10,2) NOT NULL DEFAULT '0', CHANGE `wait_time_per_hour` `wait_time_per_hour` DECIMAL(10,2) NULL DEFAULT '0', CHANGE `passenger_no_show` `passenger_no_show` DECIMAL(10,2) NOT NULL DEFAULT '0', CHANGE `late_cancellation` `late_cancellation` DECIMAL(10,2) NOT NULL DEFAULT '0', CHANGE `minimum_payout` `minimum_payout` DECIMAL(10,2) NOT NULL DEFAULT '0', CHANGE `deduction_amt` `deduction_amt` DECIMAL(10,2) NOT NULL DEFAULT '0', CHANGE `deduction_detail` `deduction_detail` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `reimbursement_amt` `reimbursement_amt` DECIMAL(10,2) NOT NULL DEFAULT '0', CHANGE `reimbursement_detail` `reimbursement_detail` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0', CHANGE `base_rate_bmm` `base_rate_bmm` DOUBLE NOT NULL DEFAULT '0', CHANGE `loaded_rate_per_mile_bmm` `loaded_rate_per_mile_bmm` DOUBLE NOT NULL DEFAULT '0', CHANGE `loaded_base_rate_bmm` `loaded_base_rate_bmm` DOUBLE NOT NULL DEFAULT '0', CHANGE `pay_type` `pay_type` INT(11) NULL DEFAULT '1' COMMENT '1-trip,2-hour', CHANGE `hourly_rate` `hourly_rate` FLOAT NULL DEFAULT NULL, CHANGE `over_time_rate` `over_time_rate` FLOAT NULL DEFAULT NULL, CHANGE `hours_per_week` `hours_per_week` FLOAT NULL DEFAULT NULL, CHANGE `week_start` `week_start` INT(11) NULL DEFAULT NULL COMMENT ' 1-sunday,2-monday ...', CHANGE `week_end` `week_end` INT(11) NULL DEFAULT NULL COMMENT ' 1-sunday,2-monday ...';

#13-2-2022
CREATE TABLE `timezone_masters` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(255) NOT NULL,
  `long_name` varchar(255) NOT NULL,
  `status` int(2) NOT NULL DEFAULT 1 COMMENT '1-active,2-deactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `timezone_masters` (`id`, `name`, `short_name`, `long_name`, `status`) VALUES
(1, 'America/New_York', 'EST', 'Eastern Standard Time (EST)', 1),
(2, 'America/Chicago', 'CST', 'Central Standard Time (CST)', 1),
(3, 'America/Los_Angeles', 'PST', 'Pacific Standard Time (PST)', 1),
(4, 'America/Denver', 'MST', 'Mountain Standard Time (MST)', 1),
(5, 'UTC+10', 'UTC+10', 'Chamorro Standard Time (UTC +10)', 1),
(6, 'AST', 'AST', 'Atlantic Standard Time (AST)', 1),
(7, 'America/Anchorage', 'AKST', 'Alaska Standard Time (AKST)', 1),
(8, 'America/Adak', 'HST', 'Hawaii-Aleutian Standard Time (HST)', 1),
(9, 'UTC-11', 'UTC-11', 'Samoa standard time (UTC-11)', 1);
ALTER TABLE `timezone_masters`
  ADD PRIMARY KEY (`id`);
  ALTER TABLE `timezone_masters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

  #14-2-2022
  ALTER TABLE `trip_status_logs` ADD `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;
  ALTER TABLE `trip_status_logs` CHANGE `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL;
  ALTER TABLE `trip_status_logs` ADD `is_updated` INT(2) NOT NULL DEFAULT '0' COMMENT '0-not updated,1-updated logs' AFTER `timezone`;
  ALTER TABLE `trip_logs` ADD `is_updated` INT(2) NOT NULL DEFAULT '0' COMMENT '0-not updated,1-updated logs ' AFTER `period3_endtime`;
  ALTER TABLE `trip_status_logs` DROP `timezone_name`;
  
  #16-2-2022
  ALTER TABLE `trip_status_logs` CHANGE `current_lat` `current_lat` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `current_lng` `current_lng` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `timezone` `timezone` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
  
  #18-2-2022

CREATE TABLE `log_activities` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `method` varchar(100) DEFAULT NULL,
  `ip` varchar(200) DEFAULT NULL,
  `agent` varchar(200) DEFAULT NULL,
  `request` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `log_activities`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `log_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  ALTER TABLE `log_activities` ADD `trip_id` INT(11) NULL DEFAULT NULL AFTER `id`;
  #22-2-2022
  ALTER TABLE `trip_master_ut` CHANGE `accept_reject` `onboard_status` INT(11) NOT NULL DEFAULT '1' COMMENT '0-pending,1-accept,2-reject,3-cancelled';
  ALTER TABLE `trip_master_ut` CHANGE `onboard_status` `onboard_status` INT(11) NULL DEFAULT '0' COMMENT '0-pending,1-accept,2-reject,3-cancelled,4-hold';
  ALTER TABLE `trip_master_ut` CHANGE `onboard_status` `onboard_status` INT(11) NULL DEFAULT '0' COMMENT '0-pending,1-accept,2-reject,3-cancelled,4-hold,5-expired';

  #14-3-2022

CREATE TABLE `driver_level_of_service` (
  `id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `level_of_service_id` int(11) DEFAULT NULL COMMENT 'belongs to master_level_of_service'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `driver_level_of_service`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `driver_level_of_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

  ALTER TABLE `trip_master_ut` ADD `pickup_county_id` INT(11) NULL DEFAULT NULL COMMENT 'belongs to county master' AFTER `drop_zip`, ADD `drop_county_id` INT(11) NULL DEFAULT NULL COMMENT 'belongs to county master' AFTER `pickup_county_id`;

#16-3-2021
CREATE TABLE `level_of_service_buffer_time` (
  `id` int(11) NOT NULL,
  `level_of_service_id` int(11) DEFAULT NULL,
  `buffer_time` varchar(10) DEFAULT NULL COMMENT 'in seconds',
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `level_of_service_buffer_time`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `level_of_service_buffer_time`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

  #29-3-2022
  ALTER TABLE `driver_master_ut` ADD `last_update` TIMESTAMP NULL DEFAULT NULL AFTER `lat`;