<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyMembership extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'role',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}