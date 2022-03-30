<?php

namespace App\Http\Resources;

use App\Http\Resources\LevelofServiceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
                'id' => $this->id,
                'mobile_no' => $this->mobile_no,
                'password' => $this->password,
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'suffix' => $this->suffix,
                'DOB' => $this->DOB,
                'ssn' => $this->ssn,
                'license_no' => $this->license_no,
                'license_class' => $this->license_class,
                'license_state' => $this->license_state,
                'license_expiry' => $this->license_expiry,
                'address' => $this->address,
                'second_address' =>$this->second_address ,
                'address_lng' =>$this->address_lng ,
                'address_lat' =>$this->address_lat ,
                'employee_id' => $this->employee_id ,
                'driver_type' => $this->driver_type ,
                'position' => $this->position ,
                'department_code' => $this->department_code ,
                'driving_experience' => $this->driving_experience ,
                'work_start_date' => $this->work_start_date ,
                'hire_date' => $this->hire_date ,
                'work_status' => $this->work_status ,
                'insurance_status' => $this->insurance_status ,
                'insurance_id' => $this->insurance_id ,
                'status' => $this->status ,
                'upload_signature_original' => $this->upload_signature_original ,
                'service_id' => (new LevelofServiceCollection($this->driverLevelservices)) ,
                'rates' => $this->driverServiceRate,
                'availity' => $this->availity ,
                'work_timing' => $this->work_timing,
                'zone' => $this->driverZones,
                'timezone' => $this->drivtimezoneerServiceRate,
                'allow_vehicle_home' => $this->allow_vehicle_home,
                'stretcher' => $this->stretcher,
                'can_overright' => $this->can_overright,
                'paralift' => $this->paralift,
                'weight_limitation' => $this->weight_limitation,
                'assistant' => $this->assistant,
                'training_video' =>  $this->training_video,
                'allowed_hours' =>  $this->allowed_hours,
                'ability_get_trips' =>  $this->ability_get_trips,
                'attendant' =>  $this->attendant,
                'central_registry' =>  $this->central_registry,
                'fingerprint' =>  $this->fingerprint,
                'enabled_cancel' => $this->enabled_cancel,
                'route_sequence' =>  $this->route_sequence,
                'decline_orders' =>  $this->decline_orders,
                'bbp' =>  $this->bbp,
                'exit_date' =>  $this->exit_date,
                'termination_reason' => $this->termination_reason,
                'notes' => $this->notes,
                
                'tlc_license' =>  $this->tlc_license,
                'tlc_license_expiry' => $this->tlc_license_expiry,
                'upload_tlc' =>  $this->tlc_upload,

                'hippa' =>  $this->hippa,
                'hippa_expiry' =>  $this->hippa_expiry,
                'hippa_upload' =>  $this->upload_hippa,

                'drug_test' =>  $this->drug_test,
                'drug_test_expiry' =>  $this->drug_test_expiry,
                'upload_drug' =>  $this->drug_test_result,

                'defensive' => $this->defensive,
                'defensive_expiry' =>  $this->defensive_expiry,
                'upload_defensive' =>  $this->defensive_upload,

                'oig' =>  $this->oig,
                'oig_expiry' => $this->oig_expiry,
                'oig_doc' =>  $this->oig,

                'sam_gov_name' =>  $this->sam_gov_name,
                'sam_gov_expiry' =>  $this->sam_gov_expiry,
                'sam_gov_doc' =>  $this->sam_gov,
                
                'national_criminal_bk_check_name' => $this->national_criminal_bk_check_name,
                'national_criminal_bk_check_expiry' =>  $this->national_criminal_bk_check_expiry,
                'national_criminal_bk_check_doc' =>  $this->national_criminal_bk_check,

                'sex_offender_report_name' =>  $this->sex_offender_report_name,
                'sex_offender_report_expiry' =>  $this->sex_offender_report_expiry,
                'sex_offender_report_doc' =>  $this->sex_offender_report,

                'motor_vehicle_record_name' =>  $this->motor_vehicle_record_name,
                'motor_vehicle_record_expiry' =>  $this->motor_vehicle_record_expiry,
                'motor_vehicle_record_doc' =>  $this->motor_vehicle_record,

                'child_abuse_clearance_name' =>  $this->child_abuse_clearance_name,
                'child_abuse_clearance_expiry' =>  $this->child_abuse_clearance_expiry,
                'child_abuse_clearance_doc' =>  $this->child_abuse_clearance,

                'cpr' =>  $this->cpr,
                'first_aid' =>  $this->first_aid,
                'mvr' =>  $this->mvr,

                'dynamic_identification_id' =>  $this->notes,
                'dynamic_identification_expiry_1' => $this->notes,
              
                'dynamic_identification_file_1' => $this->notes,
                
        ];
    }
}
