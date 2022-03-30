<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Traits\TripTrait;
use App\Models\Member;
use App\Models\ProviderMaster;
use App\Models\Crm;
use App\Models\Admin;
use App\Models\TripMaster;
use App\Models\InvoiceMaster;
use App\Models\RelInvoiceItem;
use App\Models\ProviderRemittanceTripsToAdmin;
use App\Models\Facility;
use App\Models\InvoiceItem;
use App\Http\Resources\exclusiveServiceCollection;
use App\Http\Resources\PayorInfoCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\TripListingCollection;
use App\Http\Resources\BillingInvoiceCollection;
use Illuminate\Http\Request;    
use Illuminate\Support\Facades\Mail;
use PDF;
use DB;
use App\Mail\CommonMail;

class BillingController extends Controller
{
   
    use TripTrait;
    public function index(Request $request) 
    {
       
        try {
            $returndata = InvoiceMaster::with('items', 'adminRemittanceToFranchise')->where('user_id', $request->eso_id);
            if ($request->filled('search')) {
                $search=$request->search;
                $returndata = $returndata->where('invoice_master.provider_name', 'LIKE', "%{$search}%")
                ->orWhere('invoice_master.invoice_no', 'LIKE', "%{$search}%");
            }
            if ($request->filled('payor_type')) {
                $payor_type=$request->payor_type;
                $returndata = $returndata->where('invoice_master.payor_type',$payor_type);
            }
            if ($request->filled('invoice_status')) {
                $invoice_status=$request->invoice_status;
                $returndata = $returndata->where('invoice_master.franchise_payment_status', $invoice_status);
            }
            if (@$request->filled('start_date')) {
                $start_date=$request->start_date;
                $returndata = $returndata->where('created_at', '>=', $start_date);
            }
            if (@$request->filled('end_date')) {
                $end_date=$request->end_date;
                $returndata = $returndata->where('created_at', '<=', $end_date);
            }
            
            $returndata =$returndata->paginate(config('Settings.pagination'));
            return (new BillingInvoiceCollection($returndata));
        } catch (\Exception $e) {
            return metaData(false, $request, 4016, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function downloadInvoice($id, Request $request)
    {
        try {
        $current_invoice_dtl['admin'] = Admin::first();
        $q = InvoiceMaster::with('provider')->where('id', $id)->with('invoiceDetail.trip', 'invoiceDetail.remittancelog')->where('user_id', $request->eso_id);
        

        $current_invoice_dtl['current_invoice_dtl'] = $q->first();


        // $current_invoice_dtl['franchise'] = $current_invoice_dtl['current_invoice_dtl']->provider;
        if ($current_invoice_dtl['current_invoice_dtl']->payor_type == 1) {
            // member
            $member = Member::with('address.state')->where('id', $current_invoice_dtl['current_invoice_dtl']->payor_id)->first();
            $member->provider_name = $member->name;
            $member->state = $member->address->state->name ?? '';
            $member->address = $member->address->street_address ?? '';
            $member->phone_number = $member->mobile_no;
            $member->email = $member->email ?? '';
            $current_invoice_dtl['franchise'] = $member;
        } elseif ($current_invoice_dtl['current_invoice_dtl']->payor_type == 3) {
            // broker
            $current_invoice_dtl['franchise'] = ProviderMaster::where('id', $current_invoice_dtl['current_invoice_dtl']->payor_id)->first();
        } else {
            $facility = Crm::where('id', $current_invoice_dtl['current_invoice_dtl']->payor_id)->first();
            $facility->provider_name = $facility->name;
            $facility->address = $facility->street_address ?? '';
            $facility->state = $facility->state->name ?? '';
            $facility->phone_number = $facility->crm_mobile_no;

            $current_invoice_dtl['franchise'] = $facility;
        }

        $pdf = PDF::loadView('Franchise.invoice.index', $current_invoice_dtl);

        $pdf->setPaper('A4', 'landscape');
         $filename = $current_invoice_dtl['current_invoice_dtl']->invoice_no . '.pdf';
        $file_path =  storage_path() . '/app/public/uploads/billing_Invoice_pdf/' . $filename;
        $filename_open =  url('/') . '/storage/uploads/billing_Invoice_pdf/' . $filename;
        $pdf_string =   $pdf->output();
        file_put_contents($file_path, $pdf_string);

        $main['data']  = array('filename' => $filename_open); //'table' => base64_encode($html),
       
           
        return   merge($main, metaData(true, $request, 4022, 'success', 200, '', ''));
        } catch (\Exception $e) {
            return metaData(false, $request, 4022, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function invoiceStatus(Request $request)
    {
        //  1-Pending,
        //  2-Paid,
    try{
        $main['data'] = ['', 'Unpaid', 'Paid', 'Partially Paid'];
        return   merge($main, metaData(true, $request, 4023, 'success', 200, '', ''));
    } catch (\Exception $e) {
        return metaData(false, $request, 4023, '', 502, errorDesc($e), 'Error occured in server side ');
    }
    }

    public function exServiceOpertor(Request $request, $id)
	{
		$invoice = InvoiceMaster::with('provider')->find($id);
		$invoice->read_flag = 2;
		$invoice->save();
		$items_to_fetch_franchise_driver =  InvoiceItem::with(['trip.franchise', 'trip.driver'])->where('invoice_id', $id)->get();
		$franchises = $items_to_fetch_franchise_driver->pluck('trip.franchise')->unique('id');
		$drivers = $items_to_fetch_franchise_driver->pluck('trip.driver')->unique('id');

		$items = 	(new InvoiceItem)->newQuery();
		$items =  $items->where('invoice_id', $id);


		if ($request->filled('franchise_id')) {
			$items->whereHas('trip.franchise', function ($q) use ($request) {
				$q->where('id', $request->franchise_id);
			});
		}

		if ($request->filled('driver_id')) {
			$items->whereHas('trip.driver', function ($q) use ($request) {
				$q->where('id', $request->driver_id);
			});
		}

		if ($request->filled('provider_status')) {
			$items->where('provider_remitances_status_id', $request->provider_status);
		}

		if ($request->filled('franchise_status')) {
			$items->where('admin_status_id', $request->franchise_status);
		}

		if ($request->filled('date_of_service')) {
			$items->whereHas('trip', function ($q) use ($request) {
				$q->whereDate('date_of_service', $request->date_of_service);
			});
		}


		$items = 	$items->with(['trip.franchise', 'trip.driver'])->paginate(config('Settings.pagination'));
        
        return (new exclusiveServiceCollection($items));
		
	}
    public function sendEmail(Request $request)
    {
        try{
        $postData = $request;
        $subject = $request->subject;
        $id = $request->id;
        $payor_type = $request->payor_type_invoice;
        $payor_id = $request->payor_id_invoice;
        $cc_email = $request->cc_email;
        if ($payor_type == 1) {
            $payor_type_title = 'Self';
            $member = Member::select('id', 'name', 'email', 'mobile_no')->where('id', $payor_id)->first();
            $email = $member->email ?? '';
        } elseif ($payor_type == 3) {
            $payor_type_title = 'Broker';
            $provider = ProviderMaster::select('id', 'provider_name', 'email', 'phone_number')->where('id', $payor_id)->first();
            $email = $provider->email ?? '';
        } else {
            $crm = Crm::where('id', $payor_id)->withTrashed()->first();
            $email = $crm->email ?? '';
        }
        if (empty($email)) {
            return response()->json(['status' => false, 'msg' => "Mail Id is Not Available!"]);
        }
        $msg = $request->body;
        // dd($msg);
        $data['bodytext'] = '' . $msg . ' ';
        $view = 'mails.admin.commonmailbody';
        $current_invoice_dtl['admin'] = Admin::first();
        $q = InvoiceMaster::with('provider')->where('id', $id)->with('invoiceDetail.trip', 'invoiceDetail.remittancelog')->where('user_id', $request->eso_id);
       
        $current_invoice_dtl['current_invoice_dtl'] = $q->first();
        // $current_invoice_dtl['franchise'] = $current_invoice_dtl['current_invoice_dtl']->provider;
        if ($current_invoice_dtl['current_invoice_dtl']->payor_type == 1) {
            // member
            $member = Member::with('address.state')->where('id', $current_invoice_dtl['current_invoice_dtl']->payor_id)->first();
            $member->provider_name = $member->name;
            $member->state = $member->address->state->name ?? '';
            $member->address = $member->address->street_address ?? '';
            $member->phone_number = $member->mobile_no;
            $member->email = $member->email ?? '';
            $current_invoice_dtl['franchise'] = $member;
        } elseif ($current_invoice_dtl['current_invoice_dtl']->payor_type == 3) {
            // broker
            $current_invoice_dtl['franchise'] = ProviderMaster::where('id', $current_invoice_dtl['current_invoice_dtl']->payor_id)->first();
        } else {
            $facility = Facility::where('id', $current_invoice_dtl['current_invoice_dtl']->payor_id)->first();
            $facility->provider_name = $facility->name;
            $facility->address = $facility->street_address ?? '';
            $facility->state = $facility->state->name ?? '';
            $facility->phone_number = $facility->crm_mobile_no;
            $current_invoice_dtl['franchise'] = $facility;
        }
        $name = $current_invoice_dtl['current_invoice_dtl']->invoice_no . '.pdf';
        $pdf = PDF::loadView('Franchise.invoice.index', $current_invoice_dtl);
        $file  = $pdf->output();
        $email = 'gurudas@apptechinnovations.com';
        if (empty($cc_email)) {
            Mail::to($email)->send(new CommonMail('', $view, $subject, $data, $file, $name));
        } else {
            Mail::to($email)->cc($cc_email)->send(new CommonMail('', $view, $subject, $data, $file, $name));
        }
        return metaData(true, $request, '4024', 'Mail Sent Successfully', 200);
    } catch (\Exception $e) {
        return metaData(false, $request, 4024, '', 502, errorDesc($e), 'Error occured in server side ');
    }

    }

    public function billingTrips(Request $request){
        $with_array = [
            'driver:id,name,vehicle_id',
            'vehicle:id,model_no,VIN,vehicle_model_type',
            'status:id,status_description',
            'payorTypeNames:id,name',
            'payor:id,name,phone_number',
            'levelOfService:id,name',
            'baselocation:id,name',
            'importFile:id,imported_file',
            'log',
            'statuslog:id,driver_id,trip_id,status,timezone,date_time',
            'member:id,name',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
        ];
        try {
            $payorlog =  $this->trips($request, $with_array)->latest()->paginate(config('Settings.pagination'));
            // return $payorlog;
            return (new TripListingCollection($payorlog));
        } catch (\Exception $e) {
            return metaData(false, $request, '4016', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function previewInvoice(REQUEST $request)
    {
    try{
        $postData = $request;
        $provtype =  $postData["provtype"];
        $id_arr =  $postData["id"];
        $rel_invoice = RelInvoiceItem::whereIn('trip_id', $id_arr)->whereIn('provider_invoice_status_id', [1, 3])->where('invoice_active_status', 1)->whereNull('is_deleted')->get();
        if (count($rel_invoice) > 0) {
            return array('status' => true, 'allReadyGenerateFlag' => 1, 'status_code' => 422, 'message' => 'Invoice Allready generated');
        }
        $start_date = date("Y-m-d", strtotime($request->start_date));
        $end_date = date("Y-m-d", strtotime($request->end_date));
        $with_array = [
            'driver:id,name,vehicle_id',
            'vehicle:id,model_no,VIN,vehicle_model_type',
            'status:id,status_description',
            'payorTypeNames:id,name',
            'payor:id,name,phone_number',
            'levelOfService:id,name',
            'baselocation:id,name',
            'importFile:id,imported_file',
            'log',
            'statuslog:id,driver_id,trip_id,status,timezone,date_time',
            'member:id,name',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
        ];
        $pdfData =  $this->trips($request, $with_array)->latest()->whereIn('id',$id_arr)->get();
        $invoice_check_id = InvoiceMaster::select('id')->orderBy('id', 'desc')->limit(1)->first();
        if ($invoice_check_id) {
            $invoice_no = $invoice_check_id->id + 1;
        } else {
            $invoice_no = 1;
        }

        $provider_start_name =  strtoupper(substr($pdfData[0]->provider_name, 0, 3));
        $invoice_five_digit = '0000'.$invoice_no;
        $invoice_no = 'INV' . $provider_start_name . $invoice_five_digit;
        $invMaster = array(); //new InvoiceMaster();
        $invMaster['id'] = $invoice_no;
        $invMaster['invoice_date'] = date('Y-m-d');
        $invMaster['invoice_no'] = $invoice_no;
        $invMaster['provider_name'] = $pdfData[0]->provider_name; //$invoice_no;
        $invMaster['start_date'] = $start_date; //$invoice_no;
        $invMaster['end_date'] = $end_date; //$invoice_no;
        $invMaster['payor_id'] = $pdfData[0]->payor_id; //$invoice_no;
        $invMaster['provider_id'] = $pdfData[0]->provider_id; //$invoice_no;
        $invMaster['payor_type'] = $pdfData[0]->payor_type; //$invoice_no;
        $total_amount = 0;
        $franchise_total_amount = 0;
        $rel_array = array();
        foreach ($pdfData as $dtl) {
        
            $relInvoice = array();

            $relInvoice['wait_time'] = 0;
            if ($dtl->return_pick_time_type == "Yes") {
                if ($dtl->wait_time_sec != '' && $dtl->wait_time_sec != '0') {
                    $relInvoice['wait_time'] = $dtl->wait_time_sec;
                }
            
            }
        
            $date_of_service =  modifyTripDate($dtl->date_of_service, $dtl->shedule_pickup_time);
            $relInvoice['invoice_id'] = $invMaster['id'];
            $relInvoice['trip_id'] = $dtl->trip_primary_id;
            $relInvoice['trip_no'] = $dtl->TripID;
            $relInvoice['level_of_service'] = $dtl->level_of_service;
            $relInvoice['member_name'] = $dtl->Member_name;
            $relInvoice['date_of_service'] = $date_of_service;
        
            $relInvoice['unloaded_miles'] = round($dtl->period2_miles, 4);
            $relInvoice['loaded_miles'] = round($dtl->period3_miles, 4);
            // Unloaded Duration
            // unloaded_minutes
            if ($dtl->unloaded_minutes == '') {
                $unloaded_minutes = 0;
                $relInvoice['unloaded_miles_duration'] = 0;
            } else {
                $unloaded_minutes = $dtl->unloaded_minutes;
                $relInvoice['unloaded_miles_duration'] = $unloaded_minutes;
            }
            // loaded minutes
            if ($dtl->loaded_minutes == '') {
                $loaded_minutes = 0;
                $relInvoice['loaded_miles_duration'] = 0;
            } else {
                $loaded_minutes = $dtl->loaded_minutes;
                $relInvoice['loaded_miles_duration'] = $loaded_minutes;
            }
            // get whole thing related invoice and store in invoice table for future not get back to trip master table
            // first check invoice is Rebill
            // return $relInvoice['trip_id']
            // DB::enableQueryLog();;
            $allready_invoice = RelInvoiceItem::where('trip_id', $relInvoice['trip_id'])->where('invoice_active_status', 1)->where('provider_invoice_status_id', 2)->whereNull('is_deleted')->first();

            $relInvoice['trip_amount'] = $dtl->total_trip;
            $relInvoice['price_adjustment'] = $dtl->price_adjustment;
            $relInvoice['total_amount'] = truncate_number($dtl->total_trip, 2); //truncate_number
            $relInvoice['price_adjustment'] = truncate_number($dtl->price_adjustment, 2);
            $relInvoice['price_adjustment_detail'] = $dtl->price_adjustment_detail;


            if ($allready_invoice) {
                $relInvoice['invoice_amount'] = $allready_invoice->remaining_amount;
               
            } else {
                $relInvoice['invoice_amount'] = $relInvoice['total_amount'];
            }
           
            $relInvoice['remaining_amount'] = $relInvoice['invoice_amount'];
            $relInvoice['commision'] = eso()->commission_rate; 
            $commission_amount = truncate_number(($relInvoice['commision'] / 100) * $relInvoice['invoice_amount'], 2);
            $relInvoice['commision_amount'] = $commission_amount;
           
            $relInvoice['franchise_amount'] = truncate_number($relInvoice['total_amount'] - $dtl->commision_amount, 2);
            $relInvoice['remaining_franchise_amount'] = $relInvoice['franchise_amount'];
            $relInvoice['provider_invoice_status_id'] = 1; // pending
            $relInvoice['provider_remitances_status_id'] = 1; // pending
            /////

            $providerRemmitanceLogTrips = ProviderRemittanceTripsToAdmin::whereNull('is_deleted')->where('trip_id', $relInvoice['trip_id'])->get();
            $relInvoice['providerRemmitanceLogTrips'] = $providerRemmitanceLogTrips; // pending


            // $relInvoice->admin_status_id = 1; // pending
            $total_amount += $relInvoice['invoice_amount'];
            $franchise_total_amount += $relInvoice['franchise_amount'];
            array_push($rel_array, $relInvoice);
            // $relInvoice->save();
        }
        // return $rel_array;
        $invMaster['provider_total_amount'] = $total_amount;
        $invMaster['provider_remaining_amount'] = $total_amount;
        $invMaster['franchise_total_amount'] = $franchise_total_amount;
        $invMaster['franchise_remaining_amount'] = $franchise_total_amount;
        $invMaster['created_at'] = date('Y-m-d');
        $current_invoice_dtl['current_invoice_dtl'] = $invMaster;
        $current_invoice_dtl['invoiceDetail'] = $rel_array;

        if ($pdfData[0]->payor_type == 1) {
            // member

            $member = Member::with('address.state')->where('id', $invMaster['payor_id'])->first();
            $member->provider_name = $member->name;
            $member->state = isset($member->address->state->name) ? $member->address->state->name : '';
            $member->address = $member->address->street_address ?? '';
            $member->phone_number = $member->mobile_no;
            $member->email = $member->email ?? '';
            $current_invoice_dtl['franchise'] = $member;
        } elseif ($pdfData[0]->payor_type == 3) {
            // broker
            $current_invoice_dtl['franchise'] = ProviderMaster::where('id', $invMaster['payor_id'])->first();
        } else {
        
            $facility = Crm::where('id', $invMaster['payor_id'])->first();
            $facility->provider_name = $facility->name;
            $facility->address = $facility->street_address ?? '';
            $facility->state = $facility->state->name ?? '';
            $facility->phone_number = $facility->crm_mobile_no;
          
            $current_invoice_dtl['franchise'] = $facility;
       
        }
        $current_invoice_dtl['admin'] = Admin::first();
        $metaData= metaData(true, $request, '4026', 'success', 200, '');
        return merge($current_invoice_dtl, $metaData);
    } catch (\Exception $e) {
        return metaData(false, $request, '4026', 502, errorDesc($e), 'Error occured in server side ');
    }
    }
    public function genInvoice(Request $request)
    {
    try{
        $postData = $request;
        $provtype =  $postData["provtype"];
        $id_arr =  $postData["id"];
        $rel_invoice = RelInvoiceItem::whereIn('trip_id', $id_arr)->whereIn('provider_invoice_status_id', [1, 3])->where('invoice_active_status', 1)->whereNull('is_deleted')->get();
        if (count($rel_invoice) > 0) {
            return array('status' => true, 'allReadyGenerateFlag' => 1, 'status_code' => 422, 'message' => 'Invoice Allready generated');
        }
        $start_date = date("Y-m-d", strtotime($request->start_date));
        $end_date = date("Y-m-d", strtotime($request->end_date));
        $with_array = [
            'driver:id,name,vehicle_id',
            'vehicle:id,model_no,VIN,vehicle_model_type',
            'status:id,status_description',
            'payorTypeNames:id,name',
            'payor:id,name,phone_number',
            'levelOfService:id,name',
            'baselocation:id,name',
            'importFile:id,imported_file',
            'log',
            'statuslog:id,driver_id,trip_id,status,timezone,date_time',
            'member:id,name',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
        ];
        $pdfData =  $this->trips($request, $with_array)->latest()->whereIn('id',$id_arr)->get();
        $invoice_check_id = InvoiceMaster::select('id')->orderBy('id', 'desc')->limit(1)->first();
        if ($invoice_check_id) {
            $invoice_no = $invoice_check_id->id + 1;
        } else {
            $invoice_no = 1;
        }

        $provider_start_name =  strtoupper(substr($pdfData[0]->provider_name, 0, 3));
        $invoice_five_digit = '0000'.$invoice_no;
        $invoice_no = 'INV' . $provider_start_name . $invoice_five_digit;
        $invMaster = array(); //new InvoiceMaster();
        $invMaster['id'] = $invoice_no;
        $invMaster['invoice_date'] = date('Y-m-d');
        $invMaster['invoice_no'] = $invoice_no;
        $invMaster['provider_name'] = $pdfData[0]->provider_name; //$invoice_no;
        $invMaster['start_date'] = $start_date; //$invoice_no;
        $invMaster['end_date'] = $end_date; //$invoice_no;
        $invMaster['payor_id'] = $pdfData[0]->payor_id; //$invoice_no;
        $invMaster['provider_id'] = $pdfData[0]->provider_id; //$invoice_no;
        $invMaster['payor_type'] = $pdfData[0]->payor_type; //$invoice_no;
        $total_amount = 0;
        $franchise_total_amount = 0;
        $rel_array = array();
        foreach ($pdfData as $dtl) {
        
            $relInvoice = array();

            $relInvoice['wait_time'] = 0;
            if ($dtl->return_pick_time_type == "Yes") {
                if ($dtl->wait_time_sec != '' && $dtl->wait_time_sec != '0') {
                    $relInvoice['wait_time'] = $dtl->wait_time_sec;
                }
            
            }
         
            $date_of_service =  modifyTripDate($dtl->date_of_service, $dtl->shedule_pickup_time);
            $relInvoice['invoice_id'] = $invMaster['id'];
            $relInvoice['trip_id'] = $dtl->trip_primary_id;
            $relInvoice['trip_no'] = $dtl->TripID;
            $relInvoice['level_of_service'] = $dtl->level_of_service;
            $relInvoice['member_name'] = $dtl->Member_name;
            $relInvoice['date_of_service'] = $date_of_service;
        
            $relInvoice['unloaded_miles'] = round($dtl->period2_miles, 4);
            $relInvoice['loaded_miles'] = round($dtl->period3_miles, 4);
            // Unloaded Duration
            // unloaded_minutes
            if ($dtl->unloaded_minutes == '') {
                $unloaded_minutes = 0;
                $relInvoice['unloaded_miles_duration'] = 0;
            } else {
                $unloaded_minutes = $dtl->unloaded_minutes;
                $relInvoice['unloaded_miles_duration'] = $unloaded_minutes;
            }
            // loaded minutes
            if ($dtl->loaded_minutes == '') {
                $loaded_minutes = 0;
                $relInvoice['loaded_miles_duration'] = 0;
            } else {
                $loaded_minutes = $dtl->loaded_minutes;
                $relInvoice['loaded_miles_duration'] = $loaded_minutes;
            }
            // get whole thing related invoice and store in invoice table for future not get back to trip master table
            // first check invoice is Rebill
            // return $relInvoice['trip_id']
            // DB::enableQueryLog();;
            $allready_invoice = RelInvoiceItem::where('trip_id', $relInvoice['trip_id'])->where('invoice_active_status', 1)->where('provider_invoice_status_id', 2)->whereNull('is_deleted')->first();

            $relInvoice['trip_amount'] = $dtl->total_trip;
            $relInvoice['price_adjustment'] = $dtl->price_adjustment;
            $relInvoice['total_amount'] = truncate_number($dtl->total_trip, 2); //truncate_number
            $relInvoice['price_adjustment'] = truncate_number($dtl->price_adjustment, 2);
            $relInvoice['price_adjustment_detail'] = $dtl->price_adjustment_detail;


            if ($allready_invoice) {
                $relInvoice['invoice_amount'] = $allready_invoice->remaining_amount;
               
            } else {
                $relInvoice['invoice_amount'] = $relInvoice['total_amount'];
            }
           
            $relInvoice['remaining_amount'] = $relInvoice['invoice_amount'];
            $relInvoice['commision'] = eso()->commission_rate; 
            $commission_amount = truncate_number(($relInvoice['commision'] / 100) * $relInvoice['invoice_amount'], 2);
            $relInvoice['commision_amount'] = $commission_amount;
           
            $relInvoice['franchise_amount'] = truncate_number($relInvoice['total_amount'] - $dtl->commision_amount, 2);
            $relInvoice['remaining_franchise_amount'] = $relInvoice['franchise_amount'];
            $relInvoice['provider_invoice_status_id'] = 1; // pending
            $relInvoice['provider_remitances_status_id'] = 1; // pending
            /////

            $providerRemmitanceLogTrips = ProviderRemittanceTripsToAdmin::whereNull('is_deleted')->where('trip_id', $relInvoice['trip_id'])->get();
            $relInvoice['providerRemmitanceLogTrips'] = $providerRemmitanceLogTrips; // pending


            // $relInvoice->admin_status_id = 1; // pending
            $total_amount += $relInvoice['invoice_amount'];
            $franchise_total_amount += $relInvoice['franchise_amount'];
            array_push($rel_array, $relInvoice);
            // $relInvoice->save();
        }
        // return $rel_array;
        $invMaster['provider_total_amount'] = $total_amount;
        $invMaster['provider_remaining_amount'] = $total_amount;
        $invMaster['franchise_total_amount'] = $franchise_total_amount;
        $invMaster['franchise_remaining_amount'] = $franchise_total_amount;
        $invMaster['created_at'] = date('Y-m-d');
        $current_invoice_dtl['current_invoice_dtl'] = $invMaster;
        $current_invoice_dtl['invoiceDetail'] = $rel_array;

        if ($pdfData[0]->payor_type == 1) {
            // member

            $member = Member::with('address.state')->where('id', $invMaster['payor_id'])->first();
            $member->provider_name = $member->name;
            $member->state = isset($member->address->state->name) ? $member->address->state->name : '';
            $member->address = $member->address->street_address ?? '';
            $member->phone_number = $member->mobile_no;
            $member->email = $member->email ?? '';
            $current_invoice_dtl['franchise'] = $member;
        } elseif ($pdfData[0]->payor_type == 3) {
            // broker
            $current_invoice_dtl['franchise'] = ProviderMaster::where('id', $invMaster['payor_id'])->first();
        } else {
        
            $facility = Crm::where('id', $invMaster['payor_id'])->first();
            $facility->provider_name = $facility->name;
            $facility->address = $facility->street_address ?? '';
            $facility->state = $facility->state->name ?? '';
            $facility->phone_number = $facility->crm_mobile_no;
          
            $current_invoice_dtl['franchise'] = $facility;
       
        }
        $current_invoice_dtl['admin'] = Admin::first();
        $pdf = PDF::loadView('Franchise.invoice.preview', $current_invoice_dtl);

        $pdf->setPaper('A4', 'landscape');
        $filename = $invoice_no . '.pdf';
        $file_path =  storage_path() . '/app/public/uploads/billing_Invoice_pdf/' . $filename;
        $filename_open =  url('/') . '/storage/uploads/billing_Invoice_pdf/' . $filename;
        $pdf_string =   $pdf->output();
        file_put_contents($file_path, $pdf_string);

        $main['data']  = array('filename' => $filename_open); //'table' => base64_encode($h
        // $html = view('Franchise.invoice.preview', $current_invoice_dtl)->render();
        // return $html;
        $metaData= metaData(true, $request, '4026', 'success', 200, '');
        return merge($main, $metaData);
    } catch (\Exception $e) {
        return metaData(false, $request, '4027', 502, errorDesc($e), 'Error occured in server side ');
    }
    }
    public function deleteInvoiceView(Request $request){

    try {
        $id = $request->id;
        $current_invoice_dtl['admin'] = Admin::first();
        $q = InvoiceMaster::with('provider')->where('id', $id)->with('invoiceDetail.trip', 'invoiceDetail.remittancelog')->where('user_id', $request->eso_id);
        

        $current_invoice_dtl['current_invoice_dtl'] = $q->first();


        // $current_invoice_dtl['franchise'] = $current_invoice_dtl['current_invoice_dtl']->provider;
        if ($current_invoice_dtl['current_invoice_dtl']->payor_type == 1) {
            // member
            $member = Member::with('address.state')->where('id', $current_invoice_dtl['current_invoice_dtl']->payor_id)->first();
            $member->provider_name = $member->name;
            $member->state = $member->address->state->name ?? '';
            $member->address = $member->address->street_address ?? '';
            $member->phone_number = $member->mobile_no;
            $member->email = $member->email ?? '';
            $current_invoice_dtl['franchise'] = $member;
        } elseif ($current_invoice_dtl['current_invoice_dtl']->payor_type == 3) {
            // broker
            $current_invoice_dtl['franchise'] = ProviderMaster::where('id', $current_invoice_dtl['current_invoice_dtl']->payor_id)->first();
        } else {
            $facility = Crm::where('id', $current_invoice_dtl['current_invoice_dtl']->payor_id)->first();
            $facility->provider_name = $facility->name;
            $facility->address = $facility->street_address ?? '';
            $facility->state = $facility->state->name ?? '';
            $facility->phone_number = $facility->crm_mobile_no;

            $current_invoice_dtl['franchise'] = $facility;
        }


        $main['data']  = $current_invoice_dtl ; //'table' => base64_encode($html),
       
           
        return   merge($main, metaData(true, $request, 4028, 'success', 200, '', ''));
        } catch (\Exception $e) {
            return metaData(false, $request, 4022, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    public function deleteInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:invoice_master,id,deleted_at,NULL',
            
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);
        
        if ($validator->fails()) {
            return metaData('false', $request, '4029', '', '502', '', $validator->messages());
        }
         
        try{
        $id = $request->id;
        $all_relation = RelInvoiceItem::where('invoice_id', $id)->get();
        foreach ($all_relation as $rel) {
            $data_set['invoice_status'] = 0;
            TripMaster::where('id', $rel->trip_id)->update($data_set);
        }
        RelInvoiceItem::where('invoice_id', $id)->where('user_id',$request->eso_id)->delete();
        InvoiceMaster::where('id',$id)->where('user_id',$request->eso_id)->delete();

        return  merge(['data'=>['deleted_id'=>$request->id]],   metaData(true, $request, 4028, 'success', 200, '', ''));
    } catch (\Exception $e) {
        return metaData(false, $request, '4029', '', 502, errorDesc($e), 'Error occured in server side ');
    }
    }
    public function deleteInvoiceTrip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => 'required|exists:rel_invoice_items,id,deleted_at,NULL',
            
        ],[
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ] );
        
        if ($validator->fails()) {
            return metaData('false', $request, '4030', '', '502', '', $validator->messages());
        }
        $ids = $request->trip_id;
        $total_amount = 0;
        $franchise_total_amount = 0;
        $provider_remaining_amount = 0;
        $franchise_remaining_amount = 0;

        try {
            DB::beginTransaction();
            foreach ($ids as $invoice_ids) {
                $rel_invoice_item = RelInvoiceItem::where('id', $invoice_ids)->first();
                // print_r($rel_invoice_item);die;
                $commission_amount = $rel_invoice_item->commision_amount;
                $total_amount += $rel_invoice_item->invoice_amount;
                $provider_remaining_amount += $rel_invoice_item->remaining_amount;
                $franchise_total_amount += $rel_invoice_item->franchise_amount;
                $franchise_remaining_amount += $rel_invoice_item->remaining_franchise_amount;
                $rel_invoice_item->is_deleted = 1;
                $rel_invoice_item->save();

                $data_set['invoice_status'] = 0;
                TripMaster::where('id', $rel_invoice_item->trip_id)->update($data_set);
            }

            $invMaster = InvoiceMaster::where('id', $rel_invoice_item->invoice_id)->first();
            $invMaster->provider_total_amount = $invMaster->provider_total_amount - $total_amount;
            $invMaster->provider_remaining_amount = $invMaster->provider_remaining_amount - $provider_remaining_amount;
            $invMaster->franchise_total_amount = $invMaster->franchise_total_amount - $franchise_total_amount;
            $invMaster->franchise_remaining_amount = $invMaster->franchise_remaining_amount - $franchise_remaining_amount;
            $invMaster->save();
            DB::commit();
            return metaData(true, $request, 4030, 'success', 200, '', '');
        } catch (\Exception $e) {
            return metaData(false, $request, '4030', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
        