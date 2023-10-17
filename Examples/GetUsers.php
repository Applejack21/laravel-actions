<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetUsers
{
	public function execute(): Collection
	{
		return User::all();
	}
}
