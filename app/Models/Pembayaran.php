<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $guarded = ['id'];

    // Relasi ke tabel tickets
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Relasi ke tabel ap_methods
    public function apMethod()
    {
        return $this->belongsTo(ApMethod::class);
    }
}