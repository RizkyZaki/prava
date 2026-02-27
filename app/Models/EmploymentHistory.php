<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentHistory extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'company_name',
        'position_title',
        'start_date',
        'end_date',
        'responsibilities',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function profile()
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_profile_id');
    }
}
