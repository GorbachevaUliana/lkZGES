<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Document;

class Client extends Model
{
    protected $fillable = [
        'last_name', 'first_name', 'middle_name', 
        'account_number', 'address', 'phone', 'email', 'contract_date'
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function readings()
    {
        return $this->hasMany(MeterReading::class);
    }
}