<?php

namespace App\Models;

use <?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_name',
        'amount',
        'period_days',
        'starts_at',
        'ends_at',
        'flutterwave_transaction_id',
        'flutterwave_tx_ref',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function isActive()
    {
        return $this->ends_at->isFuture();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

}
