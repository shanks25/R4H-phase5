-- 

ALTER TABLE `base_location_master` CHANGE `location` `name` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `members_master` ADD `payable_type` VARCHAR(50) NULL AFTER `mode_of_transport`; 

UPDATE members_master set payable_type = 'App\\Models\\Member' where primary_payor_type = 1  
UPDATE members_master set payable_type = 'App\\Models\\ProviderMaster' where primary_payor_type = 3   
UPDATE members_master set payable_type = 'App\\Models\\Crm' where primary_payor_type not in (1,3) 
UPDATE `members_master` SET primary_payor_id = id WHERE primary_payor_type = 1 
UPDATE `members_master` SET secondary_payor_id = id WHERE secondary_payor_type = 1

ALTER TABLE `trip_master_ut` CHANGE `member_type_pickup` `member_type_pickup` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'primary key of member_addresses or CRM name e.g \"Facility\"';  

ALTER TABLE `trip_master_ut` CHANGE `member_type_name_pickup` `member_type_name_pickup` VARCHAR(55) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'this belongs to member_addresses table and address_type column ';  
ALTER TABLE `trip_master_ut` CHANGE `crm_pickup` `crm_pickup` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'primary id of crm table'; 

ALTER TABLE `trip_master_ut` CHANGE `crm_name_pickup` `crm_name_pickup` VARCHAR(55) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'this is name column of crm table'; 

ALTER TABLE `trip_master_ut` CHANGE `crm_pickup_dept` `crm_pickup_dept` VARCHAR(55) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'this is department name of facility selected from facility_departments table';

ALTER TABLE `trip_master_ut`
  DROP `member_type_pickup_facility`,
  DROP `member_type_pickup_facility_dept`,
  DROP `facility_pickup_contact_no`,
  DROP `facility_pickup`,
  DROP `facility_name_pickup`,
  DROP `facility_pickup_dept`,
  DROP `facility_dept_name_pickup`,
  DROP `member_type_drop_facility`,
  DROP `member_type_drop_facility_dept`,
  DROP `facility_drop_contact_no`,
  DROP `facility_drop`,
  DROP `facility_name_drop`,
  DROP `facility_drop_dept`,
  DROP `facility_dept_name_drop`;

  ALTER TABLE `trip_master_ut` CHANGE `na_apply` `na_apply` INT(11) NOT NULL DEFAULT '0' COMMENT 'na checkbox on appointment time';

 

ALTER TABLE `trip_master_ut` CHANGE `trip_start_address` `trip_start_address` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'this is updated when called from app';

 


estimated_mileage_frombase_location - this is unloaded mileage (miles)

 

estimated_duration_frombase_location - this is estimated trip duration

 ALTER TABLE `trip_master_ut` ADD `estimated_duration_frombase_location` VARCHAR(90) NULL AFTER `appointment_time`;


-- ALTER TABLE `trip_master_ut` CHANGE `estimated_duration_frombase_location` `estimated_duration_frombase_location` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'this is unused column drop this';
 

-- ALTER TABLE `trip_master_ut` DROP `estimated_duration_frombase_location`; 

ALTER TABLE `trip_master_ut` CHANGE `trip_price` `trip_price` DOUBLE NULL DEFAULT NULL COMMENT 'original price';  

ALTER TABLE `trip_master_ut` CHANGE `return_pick_time_type` `return_pick_time_type` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'No' COMMENT 'this is to check if trip has wait time';
 
ALTER TABLE `trip_master_ut` CHANGE `returd_trip` `returd_trip` ENUM('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT 'this is for return with time';
 
ALTER TABLE `trip_master_ut` CHANGE `is_primary_recurring_leg` `is_primary_recurring_leg` INT(11) NOT NULL DEFAULT '0' COMMENT 'this useless for now'; 

ALTER TABLE `trip_master_ut` CHANGE `baselocation_as_driver_address` `baselocation_as_driver_address` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'this is updated when called from app ';

 

ALTER TABLE `trip_master_ut` CHANGE `trip_id_for_base_location_address` `trip_id_for_base_location_address` INT(11) NULL DEFAULT NULL COMMENT 'this is updated when called from app '; 
ALTER TABLE `trip_master_ut` DROP `pickup_address_detail`;  
ALTER TABLE `trip_master_ut` DROP `drop_address_detail`;
DROP TABLE ` cities_zipcods `;


-- 16.02.2022

SET GLOBAL innodb_strict_mode = 0
ALTER TABLE `trip_master_ut` CHANGE `timezone` `long_timezone` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
 
ALTER TABLE `trip_master_ut` ADD `timezone` VARCHAR(60) NULL AFTER `confirm_status`;
ALTER TABLE `trip_master_ut` CHANGE `member_type_pickup` `member_address_id` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'primary key of member_addresses or CRM name e.g \"Facility\"';

ALTER TABLE `trip_master_ut` CHANGE `member_address_id` `pickup_member_address_id` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'primary key of member_addresses or CRM name e.g \"Facility\"';

ALTER TABLE `trip_master_ut` CHANGE `member_type_drop` `drop_member_address_id` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `trip_master_ut` DROP `Dropoff_cont_no`;
ALTER TABLE `trip_master_ut` CHANGE `price_adjustment` `adjusted_price` DOUBLE NULL DEFAULT NULL;
ALTER TABLE `trip_master_ut` CHANGE `total_trip` `total_price` DOUBLE NULL DEFAULT NULL;

-- 17.02.2022
ALTER TABLE `trip_master_ut` CHANGE `Driver_id` `driver_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `trip_master_ut` DROP `wheelchair_yn`;
ALTER TABLE `trip_master_ut` DROP `service_id`;
ALTER TABLE `trip_master_ut` CHANGE `Member_name` `member_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `trip_master_ut` DROP `provider_name`;
ALTER TABLE `trip_master_ut` DROP `provider_cont_no`;
ALTER TABLE `trip_master_ut` DROP `other_address`;
ALTER TABLE `trip_master_ut` DROP `client_or_facility_name`;
ALTER TABLE `trip_master_ut` DROP `client_phone_no`;
ALTER TABLE `trip_master_ut` DROP `return_pickup_date`;
ALTER TABLE `trip_master_ut` DROP `return_pickup_address`;
ALTER TABLE `trip_master_ut` DROP `return_drop_off_address`;
ALTER TABLE `trip_master_ut` DROP `provide_fax`;
ALTER TABLE `trip_master_ut` DROP `member_dob`;
ALTER TABLE `trip_master_ut` DROP `reason_text`;
ALTER TABLE `trip_master_ut` DROP `confr_no`;

-- 18.02.2022

ALTER TABLE `trip_master_ut` DROP `trip_status_for_base_location`;
ALTER TABLE `trip_master_ut` DROP `reason_flag`;
ALTER TABLE `trip_master_ut` DROP `pregnant`;
ALTER TABLE `trip_master_ut` DROP `date_of_birth`;
ALTER TABLE `trip_master_ut` DROP `price_adjustment_detail`;
ALTER TABLE `trip_master_ut` DROP `MOB`;
ALTER TABLE `trip_master_ut` DROP `sp`;
ALTER TABLE `trip_master_ut` DROP `provider_email`;
ALTER TABLE `trip_master_ut` DROP `discount`;
ALTER TABLE `trip_master_ut` DROP `height`;
ALTER TABLE `trip_master_ut` DROP `provider_id`;
ALTER TABLE `trip_master_ut` DROP `facility_id`;
ALTER TABLE `trip_master_ut` DROP `vendor_id`;
ALTER TABLE `trip_master_ut` DROP `appointment_id`;
 
 ALTER TABLE `trip_master_ut` DROP `vehicle_model`, DROP `vehicle_manufacturer`, DROP `vehicle_color`, DROP `license`, DROP `driver_phone`, DROP `key`, DROP `pay`, DROP `collect`, DROP `primary_diagnosis`, DROP `call_priority`, DROP `comment_regarding_trip`, DROP `case_number`, DROP `other_special_need`, DROP `LOS`, DROP `AEsc`, DROP `DO_Dir`, DROP `Seats`, DROP `PCA`, DROP `PU_Dir`, DROP `CEsc`


  -- other_driver_id

/*   ALTER TABLE `trip_master_ut` DROP `other_driver_id`, DROP `onward_trip_id`, DROP `member_type_pickup_category`, DROP `member_type_drop_category`, DROP `payor_category`; */

-- following columns could be useable in import trip

ALTER TABLE `trip_master_ut` DROP `alt_phone_no`, DROP `member_age`, DROP `member_sign`, DROP `flat_rate`, DROP `medicaid`, DROP `gender`, DROP `crutches_walker_cane`, DROP `no_of_car_seats`, DROP `special_needs`, DROP `weigth`, DROP `Message`, DROP `member_language`, DROP `dropoff_phone_no`;

ALTER TABLE `trip_master_ut` CHANGE `level_of_service` `level_of_service` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'this will be child level of service get in import trip';


-- this will be for gmr api seperate table
ALTER TABLE `trip_master_ut` DROP `real_time_eta`, DROP `special_requirement`, DROP `leg_comment`, DROP `TransportLegId`, DROP `requester_name`, DROP `notify_phone`, DROP `notify_email`, DROP `patient_id`, DROP `PatientIsOver300Pounds`, DROP `driver_earning`;


ALTER TABLE `trip_master_ut` CHANGE `TripID` `TripID` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;



-- 19.02.2022

ALTER TABLE `trip_master_ut` CHANGE `request_id` `request_id` INT(11) NULL COMMENT 'gmr column'; 

-- 21.02.2022
ALTER TABLE `trip_master_ut` CHANGE `shedule_drop_time` `shedule_drop_time` TIME NULL DEFAULT NULL COMMENT 'it is written in code but maybe useless';
ALTER TABLE `trip_master_ut` CHANGE `commision` `commision` FLOAT NULL COMMENT '%';
ALTER TABLE `trip_master_ut` CHANGE `legid` `leg_no` INT(11) NOT NULL DEFAULT '1';
ALTER TABLE `trip_master_ut` CHANGE `drop_of_time` `drop_of_time` TIME NULL DEFAULT NULL COMMENT 'used in mobile app';
ALTER TABLE `trip_master_ut` DROP `trip_type`;
ALTER TABLE `trip_master_ut` DROP `returd_trip`;
ALTER TABLE `trip_master_ut` CHANGE `member_type_name_pickup` `pickup_address_type_name` VARCHAR(55) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'this belongs to member_addresses table and address_type column ';
ALTER TABLE `trip_master_ut` DROP `member_type_pickup_crm`;
ALTER TABLE `facility_departments` CHANGE `facility_id` `crm_id` INT(11) NOT NULL; 
RENAME table facility_departments TO crm_departments
ALTER TABLE `crm` DROP `address_type`;

-- 22.02.2022
ALTER TABLE `trip_master_ut` DROP `member_type_pickup_crm_dept`;
ALTER TABLE `trip_master_ut` DROP `member_type_drop_crm_dept`;
ALTER TABLE `trip_master_ut` DROP `crm_name_pickup`;
ALTER TABLE `trip_master_ut` DROP `crm_pickup_dept`;
ALTER TABLE `trip_master_ut` CHANGE `crm_pickup_contact_no` `pickup_crm_contact_no` VARCHAR(55) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `trip_master_ut` DROP `crm_pickup`;
ALTER TABLE `trip_master_ut` DROP `return_pick_time_type`; 
ALTER TABLE `trip_master_ut` DROP `crm_dept_name_pickup`;
ALTER TABLE `trip_master_ut` CHANGE `member_type_name_drop` `drop_address_type_name` VARCHAR(55) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `trip_master_ut` CHANGE `drop_address_type_name` `drop_address_type_name` VARCHAR(55) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'this belongs to member_addresses table and address_type column ';
ALTER TABLE `trip_master_ut` DROP `member_type_drop_crm`;
ALTER TABLE `trip_master_ut` CHANGE `crm_drop_contact_no` `drop_crm_contact_no` VARCHAR(55) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `trip_master_ut` DROP `crm_drop`;
ALTER TABLE `trip_master_ut` DROP `crm_name_drop`;
ALTER TABLE `trip_master_ut` DROP `crm_drop_dept`;
ALTER TABLE `trip_master_ut` DROP `crm_dept_name_drop`;
ALTER TABLE `trip_master_ut` DROP `will_call`;
ALTER TABLE `trip_master_ut` ADD `pickup_location_type` VARCHAR(50) NULL COMMENT 'facility non-facility' AFTER `week_end`;
ALTER TABLE `trip_master_ut` ADD `drop_location_type` VARCHAR(50) NULL COMMENT 'facility non-facility' AFTER `drop_member_address_id`
ALTER TABLE `trip_master_ut` DROP `pickup_address_type_name`;
ALTER TABLE `trip_master_ut` DROP `drop_address_type_name`;
ALTER TABLE `trip_master_ut` CHANGE `shedule_drop_time` `shedule_drop_time` TIME NULL DEFAULT NULL;
ALTER TABLE `trip_master_ut` CHANGE `trip_duration` `estimated_trip_duration` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT 'trip time duration get from google maps';
ALTER TABLE `member_addresses` ADD `department_name` VARCHAR(100) NULL AFTER `department`;
ALTER TABLE `member_addresses` CHANGE `country` `country` VARCHAR(181) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'USA';
ALTER TABLE `trip_master_ut` CHANGE `dropoff_zip` `drop_zip` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

-- 23.02.2022
DELETE FROM trip_master_ut WHERE pickup_member_address_id ='facility';
DELETE FROM trip_master_ut WHERE drop_member_address_id ='facility';
ALTER TABLE `trip_master_ut` CHANGE `TripID` `trip_no` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
-- 24.02.2022

ALTER TABLE `member_addresses` CHANGE `address_type` `address_name` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;


ALTER TABLE `trip_master_ut` CHANGE `group_id` `group_id` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'unique id for all leg of trips';
   
ALTER TABLE `members_master` DROP `location_type`;


-- 03.03.2022
RENAME TABLE `phase5`.`trip_recurring_master_ut` TO `phase5`.`trip_recurring_master`;
ALTER TABLE `trip_recurring_master` CHANGE `number_of_trip` `trip_count` INT(11) NOT NULL;
ALTER TABLE `trip_recurring_master` DROP `user_id`;
ALTER TABLE `trip_recurring_master` ADD `user_id` VARCHAR(100) NOT NULL AFTER `trip_count`;
ALTER TABLE `trip_recurring_master` CHANGE `primary_trip_id` `primary_trip_id` INT(11) NULL;
ALTER TABLE `trip_recurring_master` CHANGE `trip_count` `trip_count` INT(11) NULL;
ALTER TABLE `trip_recurring_master`  ADD `weekdays` VARCHAR(100) NOT NULL  AFTER `id`;

-- 04.02.2022
ALTER TABLE `trip_master_ut` CHANGE `recurring_master_key` `recurring_master_id` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

-- 07.03.2022
ALTER TABLE `trip_master_ut` CHANGE `parent_id` `parent_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'the primary key of very first leg of the trips';
ALTER TABLE `trip_master_ut` ADD `week_day` INT NOT NULL COMMENT 'week day of trip monday to sunday ' AFTER `appointment_time`;
ALTER TABLE `trip_master_ut` CHANGE `week_day` `week_day` VARCHAR(50) NOT NULL COMMENT 'week day of trip monday to sunday ';
-- till this line sql query is runned on server

-- 14.03.2022 
ALTER TABLE `members_master` CHANGE `primary_payor_type` `primary_payor_type` INT(11) NOT NULL DEFAULT '1' COMMENT '1-self, 2 facility , 3 provider';

-- 28.03.2022
ALTER TABLE `vehicle_maintenance_requests` CHANGE `maintenance_request_id` `ticket_id` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `vehicle_maintenance_requests` ADD `garage_id` INT NULL AFTER `vehicle_id`;

