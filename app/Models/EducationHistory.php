<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationHistory extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'institution',
        'degree',
        'field_of_study',
        'start_date',
        'end_date',
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
