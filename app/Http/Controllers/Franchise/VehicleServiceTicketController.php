<?php

namespace App\Http\Controllers\Franchise;

use DB;
use stdClass;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\VehicleMaintenance;
use App\Rules\ValidatePayorIdRule;
use App\Http\Controllers\Controller;
use App\Models\VehicleServiceInvoice;
use Facade\FlareClient\Http\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\VehicleServiceItmeCharge;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\VehicleServiceInvoiceRequest;
use App\Http\Resources\VehicleServiceInvoiceResource;
use App\Http\Resources\VehicleServiceTicketCollection;

class VehicleServiceTicketController extends Controller
{
    
    /*---------------------Ticket List---------------- */

    public function index(Request $request)
    {
        try {
            $query = VehicleMaintenance::with('vehicleMaintenanceService:id,name')->eso();
            $ticket= VehicleMaintenance::filterTicket($request, $query);
            $ticket=$ticket->latest()->paginate(config('Settings.pagination'));
            return  new VehicleServiceTicketCollection($ticket);
        } catch (\Exception $e) {
            return metaData(false, $request, 30046, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    /*---------------------End Ticket List---------------- */
    
    /*--------------------- Create Invoice--------------- */

    public function createInvoice(VehicleServiceInvoiceRequest $request)
    {
          
         try {
             
            $invoice=VehicleServiceInvoice::Create($request->except('item','details','qty','amount','total'));
            $metaData= metaData(true, $request, '30047', 'success', 200, '');
           
            $dataArray=array();
            foreach($request->item as $k=>$item)
            {
             $dataArray=[
                 'invoice_id'=>$invoice->id,
                 'item'=>$item,
                 'details'=>$request->details[$k],
                 'qty'=>$request->qty[$k],
                 'amount'=>$request->amount[$k],
                 'total'=>$request->item_total[$k],
                 ];
            }
        
                VehicleServiceItmeCharge::insert($dataArray);
                $invoice=VehicleServiceInvoice::with('ServiceItemCharge:id,item,details,qty,amount,total,invoice_id')->find($invoice->id);
                
                return (new VehicleServiceInvoiceResource($invoice))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30002, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    /*---------------------End Create Invoice---------------- */
    
}
