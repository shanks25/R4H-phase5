<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
	protected $table = "rel_invoice_items";

	public function trip()
	{
		return $this->belongsTo(TripMaster::class,'trip_id');
	}

	public function invoice()
	{
		return $this->belongsTo(InvoiceMaster::class,'invoice_id');
	}




}
