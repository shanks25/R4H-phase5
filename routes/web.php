<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Franchise\GarageController;
use App\Http\Controllers\Franchise\VehicleMaintenanceRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['nodeToken'])->namespace('Franchise')->group(function () {
    // dev1
    Route::get('members', 'MemberController@index');
    Route::get('searchMember', 'MemberController@searchMember');
    Route::get('member-address', 'MemberController@memberAddress');

    Route::get('master-level-of-services', 'LevelofServiceController@index');
    Route::get('payor-types', 'PayorTypeController@index');
    Route::get('payor-names', 'PayorsController@index');
    Route::get('crm-with-departments', 'CrmController@crmWithDepartments');
    Route::get('crm-details', 'CrmController@crmDetails');

    Route::post('add-trip', 'AddTripController@index');
    Route::get('get-trip-no', 'AddTripController@getTripNo');
    Route::get('edit-trip', 'EditTripController@edit');
    Route::post('update-trip', 'EditTripController@update');

    Route::post('add-recurring-trip', 'AddRecurringTripController@index');
    Route::post('import-trip', 'ImportTripController@index');
    Route::post('edit-recurring-trip', 'EditRecurringTripController@index');
    Route::get('edit-recurring-trip', 'RecurringManagementController@show');
    Route::get('get-recurring-trips', 'RecurringManagementController@index');
    Route::delete('delete-recurring-trips', 'RecurringManagementController@destroy');


    Route::get('brokers', 'BrokerController@index');
    Route::get('profile', 'EsoController@index');
    Route::post('profile', 'EsoController@updateProfile');





    //dev 2 ars
    Route::get('get-trips', 'TripListingController@index'); //2001
    Route::get('onboard-trips', 'OnboardController@index'); //2015
    Route::post('onboard-update-status', 'OnboardController@updateStatus'); //2016
    Route::get('view-trips', 'TripController@view'); //2002
    Route::get('get-driver-names', 'DriverController@get');
    Route::get('get-payout-names', 'PayoutController@index');
    Route::post('update-trip-status', 'TripController@updateTripStatus');
    Route::post('trip-update-base-location', 'TripController@updateBaseLocation');
    Route::post('delete-bulk-trip', 'TripController@Bulkdestroy');
    Route::post('assign-driver-bulk', 'AssignController@assignBulk');
    Route::post('update-logs', 'UpdateLogsController@update');
    Route::get('edit-logs', 'UpdateLogsController@edit');
    Route::get('import-broker-names', 'PayorsController@importNames');
    Route::get('get-timezone', 'TimezoneController@index');
    Route::get('trip-history-logs', 'LogActivityController@index');
    Route::get('trip-export', 'TripExportController@index');

    //dispatch
    Route::get('dispatch-status-wise', 'DispatchController@tripListStatusWise'); //2017
    Route::post('unassign-driver', 'AssignController@unAssignBulk'); //2018
    Route::get('dispatch-date-wise', 'DispatchController@index'); //2019
    Route::get('dispatch-driver-details', 'DispatchController@driverDetails'); //2020
    Route::get('dispatch-auto-assign', 'DispatchAutoAssignController@index'); //2021
    Route::post('dispatch-accept-auto-assign', 'DispatchAutoAssignController@acceptAutoAssign'); //2022
    Route::get('dispatch-driver-assign-trips', 'DispatchController@assignTrips'); //2023
    //dispatch location
    Route::get('dispatch-live-trips', 'DispatchLocationController@index'); //2024
    Route::get('get-driver-route', 'DispatchRouteController@index'); //2025
    Route::get('get-vehicle-with-driver', 'VehicleWithDriverCotroller@index'); //2026
    Route::post('send-push-notification', 'SendNotificationController@index'); //2027









    /// rajan

    Route::get('vehicle-list', 'VehicleController@index');
    Route::post('add-vehicle', 'VehicleController@store');
    Route::get('edit-vehicle', 'VehicleController@edit');
    Route::post('update-vehicle', 'VehicleController@update');
    Route::delete('delete-vehicle', 'VehicleController@destroy');
    Route::post('update-vehicle-odometer', 'VehicleController@updateOdometer');

    Route::get('auto-set-list', 'AutoSetController@index');
    Route::post('add-auto-set', 'AutoSetController@store');
    Route::get('edit-auto-set', 'AutoSetController@edit');
    Route::post('update-auto-set', 'AutoSetController@update');
    Route::delete('delete-auto-set', 'AutoSetController@destroy');
    Route::get('payor-wise-autoset-time', 'AutoSetController@payorWiseAutoSet');

    Route::get('get-state-list', 'CommonApiController@getState');
    Route::get('get-city-by-state', 'CommonApiController@getCity');
    Route::get('get-county-by-city', 'CommonApiController@getCounty');
    Route::get('get-zipcode-by-city', 'CommonApiController@getZipcode');
    Route::get('get-vehicle-services', 'CommonApiController@getVehicleServices');

    Route::get('base-locations', 'BaseLocationController@index');
    Route::get('base-locations-list', 'BaseLocationController@beseLocationList');
    Route::post('add-base-locations', 'BaseLocationController@store');
    Route::get('edit-base-locations', 'BaseLocationController@edit');
    Route::post('update-base-locations', 'BaseLocationController@update');
    Route::delete('delete-base-locations', 'BaseLocationController@destroy');

    Route::get('member-list', 'MemberController@memberList');
    Route::get('member-show', 'MemberController@show');
    Route::post('add-member', 'MemberController@store');
    Route::get('edit-member', 'MemberController@edit');
    Route::post('update-member', 'MemberController@update');
    Route::delete('delete-member', 'MemberController@destroy');
    Route::get('auto-fill-address', 'MemberController@autoFillAddress');

    Route::get('driver-list', 'DriverController@index');
    Route::get('get-insurance-types', 'DriverController@getInsuranceType');
    Route::post('add-driver', 'DriverController@store');
    Route::get('edit-driver', 'DriverController@edit');
    Route::get('view-driver', 'DriverController@view');
    Route::post('update-driver', 'DriverController@update');
    Route::post('add-driver-professional', 'DriverController@professionalStore');
    Route::post('add-driver-work-profile', 'DriverController@workProfileStore');
    Route::post('add-driver-creadentials', 'DriverController@creadentialsStore');
    Route::post('add-driver-availability', 'DriverController@availabilityStore');
    Route::post('assign-driver-vehicle', 'DriverController@assignVehicle');
    Route::post('change-driver-status', 'DriverController@changeStatus');
    Route::get('driver-leaves', 'DriverController@driverLeaves');
    Route::delete('delete-driver', 'DriverController@destroy');

    Route::get('hiring-driver-list', 'HiringDriverController@index');
    Route::get('hiring-request-detail', 'HiringDriverController@detail');
    Route::post('add-hiring-note', 'HiringDriverController@adddriverrequestnote');
    Route::post('change-hiring-approvel-status', 'HiringDriverController@ChangeDriverApproveStatus');

    Route::get('clerical-archived', 'ClericalArchivedJobsController@index');
    Route::get('clerical-archived-list', 'ClericalArchivedJobsController@getList');

    Route::get('rules', 'MaintenanceRulesController@index');
    Route::get('edit-rules', 'MaintenanceRulesController@edit');
    Route::post('add-rules', 'MaintenanceRulesController@store');
    Route::post('update-rules', 'MaintenanceRulesController@update');
    Route::delete('rules-delete', 'MaintenanceRulesController@destroy');

    Route::get('service-ticket', 'VehicleServiceTicketController@index');
    Route::post('create-vehicle-service-invoice', 'VehicleServiceTicketController@createInvoice');


    /// suraj
    Route::get('crm-list', 'CrmController@index'); //4001
    Route::post('store-crm', 'CrmController@store'); //4002
    Route::get('edit-crm', 'CrmController@edit'); //4003
    Route::post('update-crm', 'CrmController@update'); //4004
    Route::delete('delete-crm', 'CrmController@destroy'); //4005
    Route::delete('delete-department', 'CrmController@destroyDepartments'); //4006

    Route::post('store-crm-rates', 'PayorRatesController@store'); //4007
    Route::post('update-crm-rates', 'PayorRatesController@update'); //4008
    Route::post('store-crm-contracts', 'PayorContractsController@store'); //4009
    Route::post('update-crm-contracts', 'PayorContractsController@update'); //4010

    Route::post('store-driver-rates', 'DriverServiceRatesController@store'); //4011
    Route::post('update-driver-rates', 'DriverServiceRatesController@update'); //4012


    Route::get('payor-logs', 'PayorLogController@index'); //4013
    Route::post('generate-pdf', 'PayorLogController@GeneratePdf'); //4014
    Route::get('driver-logs', 'DriverLogsController@index'); //4015
    Route::get('period-logs', 'PeriodLogsController@index'); //4016
    Route::get('get-member-signature', 'PayorLogController@memberSign'); //4032
    Route::get('trip-logs', 'TripLogController@index'); //4017
    Route::get('fairmetic-logs', 'FairmaticLogsController@index'); //4018
    Route::get('get-trip-status', 'TripLogController@tripStatus'); //4019
    Route::get('get-timelog-status', 'TripLogController@timelogStatus'); //4020

    Route::get('billing-invoices', 'BillingController@index'); //4021
    Route::get('download-invoices/{id}', 'BillingController@downloadInvoice')->name('download-invoices'); //4022
    Route::get('invoice-status', 'BillingController@invoiceStatus'); //4023
    Route::get('exclusive-service-operator/{id}', 'BillingController@exServiceOpertor'); //4023
    Route::post('send-invoice', 'BillingController@sendEmail'); //4024

    Route::get('delete-invoice-view', 'BillingController@deleteInvoiceView'); //4028
    Route::get('delete-invoice', 'BillingController@deleteInvoice'); //4029
    Route::get('delete-invoice-trip', 'BillingController@deleteInvoiceTrip'); //4030


    Route::get('billing-trips', 'BillingController@billingTrips'); //4025
    Route::get('billing-trips-preview', 'BillingController@previewInvoice'); //4026
    Route::get('billing-trips-generate', 'BillingController@genInvoice'); //4027



    Route::get('driver-payout', 'DriverPayoutController@driverPayout'); //4031

    Route::get('trip-payment-history', 'TripPaymentHistoryController@index'); //4033
    Route::get('get-remittance-status', 'TripPaymentHistoryController@remittancStatus'); //4034
    Route::get('restore-trip-list', 'RestoreTripController@index'); //4035
    Route::get('restore-trips', 'RestoreTripController@restorTrips'); //4036
    Route::get('route-profitability', 'RouteProfitabilityReportController@index'); //4037
    Route::get('driver-utilization', 'DriverUtilzationController@index'); //4038
    Route::get('earnings', 'EarningReportController@index'); //4039
    Route::get('daily-trip-logs', 'DailyTripController@index'); //4040
    Route::get('earnings-report-trip-list', 'EarningReportController@earningReportTrips'); //4041
    Route::get('route-report-trip-list', 'RouteProfitabilityReportController@routeReportTrips'); //4042
    Route::get('driver-report-trip-list', 'DriverUtilzationController@driverReportTrips'); //4043
    Route::get('vehicle-logs', 'VehicleLogReportController@index'); //4044






    //anamika

    Route::get('template', 'BrokerController@temp');
    Route::get('brokers', 'BrokerController@index');
    Route::get('broker-list', 'BrokerController@brokerList');
    Route::post('broker-add', 'BrokerController@store');
    Route::get('edit-broker', 'BrokerController@edit');
    Route::post('broker-update', 'BrokerController@update');
    Route::delete('broker-delete', 'BrokerController@destroy');


    Route::get('clerical-list', 'ClericalController@getList');
    Route::get('clerical-step1', 'ClericalPageStepController@step1');
    Route::get('clerical-step-point4', 'ClericalPageStepController@clericalstep4Point');
    Route::get('clerical-step-point3', 'ClericalPageStepController@clericalstep3');
    Route::get('clerical-step-point2', 'ClericalPageStepController@clericalstep2');
    Route::post('step3-approval', 'ClericalPageStepController@step3ApprovalStatus');
    Route::post('step4-approval', 'ClericalPageStepController@Step4ApprovalStatus');
    Route::post('addclericalnote', 'ClericalPageStepController@addclericalnote');


    // zone
    Route::get('zones', 'ZonesController@getzip');
    Route::post('get-zone', 'ZonesController@get');
    Route::post('add-zone', 'ZonesController@store');
    Route::post('update-zone', 'ZonesController@zoneUpdate');
    Route::post('get-zone-zip', 'ZonesController@getzip');
    Route::post('get-state-cities', 'ZonesController@getStateZip');
    Route::post('get-cities-county', 'ZonesController@getCitiesCounty');
    Route::post('get-countys-zip', 'ZonesController@getCountysZip');
    Route::get('edit-zones', 'ZonesController@editZones');
    Route::delete('delete-zone', 'ZonesController@delete');

    //complaint

    Route::get('complaint-list', 'ComplaintController@complaintList');
    Route::post('add-complaint', 'ComplaintController@complaintstore');
    Route::delete('complaint-delete', 'ComplaintController@complaintdestroy');

    //niti
    Route::get('accident-list', 'AccidentRequestController@index');
    Route::post('accident-add', 'AccidentRequestController@store');
    Route::get('edit-accident', 'AccidentRequestController@edit');
    Route::post('update-accident', 'AccidentRequestController@update');
    Route::delete('delete-accident', 'AccidentRequestController@destroy');
    Route::post('change-accident-status', 'AccidentRequestController@changestatus');

    //write here om
    Route::get('garage-list', [GarageController::class, 'index']);
    Route::post('garage-register', [GarageController::class, 'store']);
    Route::get('garage-edit', [GarageController::class, 'edit']);
    Route::post('garage-update', [GarageController::class, 'update']);
    Route::delete('garage-delete', [GarageController::class, 'destroy']);

    //Vehicle Maintenance Request
    Route::get('list-vehicle-maintenance-request', [VehicleMaintenanceRequestController::class, 'index']);
    Route::post('add-vehicle-maintenance-request', [VehicleMaintenanceRequestController::class, 'store']);
    Route::get('edit-vehicle-maintenance-request', [VehicleMaintenanceRequestController::class, 'edit']);
    Route::post('update-vehicle-maintenance-request', [VehicleMaintenanceRequestController::class, 'update']);
    Route::delete('delete-vehicle-maintenance-request', [VehicleMaintenanceRequestController::class, 'destroy']);



    //  Route::get('get-services', [VehicleMaintenanceRequestController::class, 'store']);



    //  Route::get('get-services', [VehicleMaintenanceRequestController::class, 'store']);
});








Route::get('users', 'UserController@index');

Route::get('/', function () {
    return view('welcome');
});
Route::any('play', 'PlayController@index')->middleware('nodeToken');
Route::any('play2', 'PlayController@index2');
// Route::any('play', 'PlayController@index');
