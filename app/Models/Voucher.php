<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'price', 'used', 'used_by', 'used_at', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}
