<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationVerificationCode extends Model
{
    use HasFactory;
    protected $fillable = ['email', 'phone', 'code'];
}
