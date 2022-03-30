<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelInvoiceItem extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "rel_invoice_items";

	public function providerRemmitanceLog()
	{
		return $this->hasMany(ProviderRemittanceTripsToAdmin::class, 'rel_invoice_item_id');
	}
	public function remittancelog()
	{
		return $this->hasMany(ProviderRemittanceTripsToAdmin::class, 'trip_id', 'trip_id')->whereNull('is_deleted');
	}
	public function trip()
	{
		return $this->belongsTo(TripMaster::class, 'trip_id');
	}
	public function invoice()
	{
		return $this->belongsTo(InvoiceMaster::class, 'invoice_id');
	}
}
