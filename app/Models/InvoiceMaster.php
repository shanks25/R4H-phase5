<?php

namespace App\Models;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceMaster extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = "invoice_master";

public function items()
{
    return $this->hasMany(InvoiceItem::class, 'invoice_id');
}

public function adminRemittanceToFranchise()
{
    return $this->hasOne(AdminRemittanceTripsToFranchise::class, 'invoice_id')->orderBy('id', 'DESC');
}
public function provider()
{
    return $this->belongsTo(ProviderMaster::class, 'provider_id');
}
public function invoiceDetail()
{
    return $this->hasMany(RelInvoiceItem::class, 'invoice_id');
}

}
