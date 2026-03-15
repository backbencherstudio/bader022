<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    protected $guard_name = 'api';

    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'type',
        'phone',
        'role',
        'status',
        'website_domain',
        'address',
        'platform_access',
        'current_package',
        'package_duration',
        'package_start_date',
        'package_end_date',
        'package_expire_date',
        'number_of_branches',
        'remaining_day',
        'package_status',
        'jwt_token',
        'google_id',
        'business_category',
        'business_name', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'jwt_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function scopeMerchants($query)
    {
        return $query->where('type', 2);
    }

    public function merchantSetting()
    {
        return $this->hasOne(MerchantSetting::class);
    }

    public function minisite()
    {
        return $this->hasOne(MiniSite::class);
    }

    public function whyChooseUs()
    {
        return $this->hasOne(WhyChooseUs::class);
    }

    public function globalSetting()
    {
        return $this->hasOne(GlobalSetting::class);
    }
}
