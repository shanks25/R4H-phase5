<?php

namespace App\Models;

use App\Traits\Timezone;
use Illuminate\Database\Eloquent\Model;

class AdminRemittanceTripsToFranchise extends Model
{
	use Timezone;
	protected $table = "admin_remittance_trips_to_franchise";
	protected $guarded = [];

	public function invoice()
	{
		return $this->belongsTo(InvoiceMaster::class, 'invoice_id', 'id');
	}

	public function adminRemittance()
	{
		return $this->belongsTo(AdminRemittanceMaster::class, 'admin_remittance_id', 'id');
	}

	public function trip()
	{
		return $this->belongsTo(TripMaster::class, 'trip_id', 'id');
	}
	public function providerRemitanceRel()
	{
		return $this->belongsTo(ProviderRemittanceTripsToAdmin::class, 'provider_remittance_trip_item_id');
	}
}
