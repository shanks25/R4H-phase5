<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;
	protected $table = "admins";

	protected $guard = 'admin';

	protected $fillable = [
		'user_name', 'email', 'password',
	];
	protected $dates = ['created_at'];
}
