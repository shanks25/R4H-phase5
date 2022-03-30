<?php

namespace App\Models;

use App\Traits\Timezone;
use Illuminate\Database\Eloquent\Model;

class ProviderRemittanceTripsToAdmin extends Model
{
    use  Timezone;
    protected $table = "provider_remittance_trips_to_admin";
    public  $timestamps = false;

    public function invoice()
    {
        return $this->belongsTo(InvoiceMaster::class, 'invoice_id', 'id');
    }
    public function providerRemittance()
    {
        return $this->belongsTo(ProviderRemittanceMaster::class, 'remittance_id', 'id');
    }
    public function relInvoice()
    {
        return $this->belongsTo(RelInvoiceItem::class, 'rel_invoice_item_id');
    }
}
