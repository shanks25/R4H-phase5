<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\TripListingCollection;
use App\Http\Requests\CommonFilterRequest;
use App\Traits\TripTrait;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use DateTime;
use PDF;
use View;
use App\Models\PayorType;
use App\Models\Crm;
use App\Models\Member;
use App\Models\TripMaster;

class PayorLogController extends Controller
{
    use TripTrait;
    public function index(CommonFilterRequest $request) 
    {
        $with_array = [
            'driver:id,name,vehicle_id',
            'vehicle:id,model_no,VIN',
            'status:id,status_description',
            'payorTypeNames:id,name',
            'payor:id,name,phone_number',
            'levelOfService:id,name',
            'baselocation:id,name',
            'log',
            'statuslog:id,driver_id,trip_id,status,timezone,date_time',
            'member:id,name',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
        ];
        try {
            $payorlog = $this->trips($request, $with_array)->latest()->paginate(config('Settings.pagination'));
            return (new TripListingCollection($payorlog));
        } catch (\Exception $e) {
            return metaData(false, $request, '4013', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function GeneratePdf(Request $request)
    {
        $id_arr =  $request->trip_id;
        
        if ($request->checkAll == "1") {
            $id = $this->getreport($request);
            $id_arr = explode(",", $id);
        }
        $with_array = [
            'driver:id,name,vehicle_id',
            'vehicle:id,model_no,VIN',
            'status:id,status_description',
            'payorTypeNames:id,name',
            'payor:id,name,phone_number',
            'levelOfService:id,name',
            'baselocation:id,name',
            'log',
            'statuslog:id,driver_id,trip_id,status,timezone,date_time',
            'member:id,name',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
        ];
        $data['pdf'] = $this->trips($request, $with_array)->get();
        $pdfarray = array();
        foreach ($data['pdf'] as $k => $p) {
            $cnt = count($data['pdf']) - 1;
            if ($cnt == $k) {
                $p->pagebreak = 'n';
            }
            if ($cnt != $k) {
                $p->pagebreak = 'y';
            }

            if ($p->Driver_id == '0' || $p->Driver_id == null || $p->Driver_id == '') {
                $driver_id = 0;
            } else {
                $driver_id = $p->Driver_id;
            }

            $pdfarray[$driver_id][$p->date_of_service][] = $p;
        }
        $all_payor = PayorType::get()->toArray();
        $all_payor_names = array_column($all_payor, 'name', 'id');
        $data['pdfarray'] = $pdfarray;
        $payor_type = $request->payor_type;
        if ($payor_type == 1) {
            $filename =  'Member_' . date('Ymd') . '_' . rand() . '.pdf';
        } elseif ($payor_type == 3) {
            $provider = ProviderMaster::select('id', 'provider_name', 'phone_number')->where('id', $request->payor_name[0])->first();
            $filename = str_replace(' ', '', $provider->provider_name) . '_' . date('Ymd') . '_' . rand() . '.pdf';
        } else {
            $payor_type_title = $all_payor_names[$payor_type] ?? '';
            $filename = str_replace(' ', '', $payor_type_title) . date('Ymd') . '_' . rand() . '.pdf';
        }
        $file_path =  storage_path() . '/app/public/uploads/provider_pdf/' . $filename;
        $filename_open =  url('/') . '/storage/uploads/provider_pdf/' . $filename;

        $html = '';
        // $data['timezone'] = getAuthTimeZone();
        $data['timezone'] = 'Asia/Kolkata';
        foreach ($pdfarray as $k => $p) {
            foreach ($p as $kk => $pp) {
                $payor_type = $pp[0]->payor_type;
                $payor_id = $pp[0]->payor_id;
                $payor_name = '';
                if ($payor_type == 1) {
                    $payor_type_title = 'Self';
                    $member = Member::select('id', 'name', 'mobile_no')->where('id', $payor_id)->first();
                    $payor_name = $member->name ?? '';
                } elseif ($payor_type == 3) {
                    $payor_type_title = 'Broker';
                    $provider = ProviderMaster::select('id', 'provider_name', 'phone_number')->where('id', $payor_id)->first();
                    $payor_name = $provider->provider_name ?? '';
                } else {
                    $payor_type_title = $all_payor_names[$payor_type] ?? '';
                    $crm = Crm::where('id', $payor_id)->withTrashed()->first();
                    $payor_name = $crm->name ?? '';
                }

                $pp[0]->payor_name = $payor_name;

                $dt1 = strtotime($pp[0]->date_of_service);
                $dt2 = date("l", $dt1);
                $dt3 = strtolower($dt2);
                if ($dt3 == "sunday") {
                    $date = new DateTime($pp[0]->date_of_service);
                    $data['custom_end_date'] =  $date->format('m/d/Y');
                } else {
                    $date = new DateTime($pp[0]->date_of_service);
                    $date->modify('next sunday');
                    $data['custom_end_date'] =  $date->format('m/d/Y');
                }

                $data['pdf'] = $pp;

                if ($payor_type == 1) {
                    $html .= (string)View::make('franchise.member_pdf_dom', $data);
                } elseif ($payor_type == 3) {

                    $template = '';
                    $find_payor_id = ProviderMaster::where('user_id', Auth::id())->where('template', "!=", "0")->where('id', $payor_id)->first();
                    if ($find_payor_id) {
                        $template = $find_payor_id->template;
                    }
                    
                    if ($template == 4) {
                        $html .= (string)View::make('Franchise.pdf.Logisticare_dom', $data);
                    } elseif ($template == 2) {
                        $html .= (string)View::make('Franchise.pdf.A2C_dom', $data);
                    } elseif ($template == 3) {
                        $html .= (string)View::make('Franchise.pdf.CTS_dom', $data);
                    } elseif ($template == 5) {
                        $html .= (string)View::make('Franchise.pdf.MTM_dom', $data);
                    } else {
                        $html .= (string)View::make('Franchise.pdf.provider_pdf_dom', $data);
                    }

                    
                } else {
                    $data['payor_type_title'] = $payor_type_title;
                    $html .= (string)View::make('Franchise.pdf.crm_pdf_dom', $data);
                }
            }
        }
        

        if ($html == '') {
            $html = 'Record not found.';
        }

        $pdf = PDF::loadhtml($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf_string =   $pdf->output();
        file_put_contents($file_path, $pdf_string);

        echo json_encode(array('filename' => $filename_open)); //'table' => base64_encode($html),
        exit;
    }

    public function memberSign(Request $request){
        $validator = Validator::make($request->all(), [
            'trip_id' => 'required|numeric|exists:trip_master_ut,id,deleted_at,NULL',
            
        ], [
            'trip_id.required' => 'trip_id is required.',
            'trip_id.exists' => 'Invalid trip_id',
            
        ]);
        
        if ($validator->fails()) {
            return metaData('false', $request, '4031', '', '502', '', $validator->messages());
        }
        try{
            $sign = TripMaster::eso()->select('member_sign')->where('id',$request->trip_id)->first();
            if($sign != null){
                $data['data']['url'] = $sign->member_sign;
            }else{
                $data['data']['url'] = '';
            }
            $metaData= metaData(true, $request, '4031', 'success', 200, '');
            return merge($metaData, $data);
        } catch (\Exception $e) {
            return metaData(false, $request, '4031', 502, errorDesc($e), 'Error occured in server side ');
        }

    }
}
