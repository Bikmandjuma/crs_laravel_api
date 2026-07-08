<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements JWTSubject
{
    protected $table='users';
    protected $guarded = array();
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'country',
        'gender',
        'phone',
        'birthdate',
        'password',
        'image',
        'professionalism'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthdate' => 'date',
    ];

    /**
     * Get the identifier that will be stored in the JWT payload.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Typically the user ID, or you could use a custom identifier
    }

    /**
     * Get the custom claims to be added to the JWT payload.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return []; // You can return any custom claims you want to include in the JWT payload
    }

    public function seekerbojcategory_id(){
        return $this->belongTo(SeekerJobCategory::class);
    }

    public function subscriptions(){
        return $this->hasMany(\App\Models\Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest()
            ->first();
    }

    public function subscriptionDaysLeft()
    {
        $sub = $this->activeSubscription();
        return $sub ? now()->diffInDays($sub->ends_at, false) : 0;
    }

}
