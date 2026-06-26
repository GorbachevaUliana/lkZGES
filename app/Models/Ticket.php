<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'staff_id',
        'status',
        'admin_reply',
        'replied_at',
        'replied_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function repliedBy()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
}