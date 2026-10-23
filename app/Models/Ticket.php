<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = ['id'];

    // WAJIB ADA: Mengubah Array PHP menjadi JSON secara otomatis saat disimpan ke SQLite
    protected $casts = [
        'img_req' => 'array',
        'json_req' => 'array',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function apMethod() { return $this->belongsTo(ApMethod::class); }
    public function approvals() { return $this->hasMany(TicketApproval::class); }
    public function pembayaran() { return $this->hasOne(Pembayaran::class); }
}