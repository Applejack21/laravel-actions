<?php

namespace App\Actions\User;

use App\Models\User;

class DeleteUser
{
	public function execute(User $user): User|bool
	{
		$deleted = $user->delete();

		// return the model if is utilises soft deletes as the record still exists
		if(in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($user)) && $deleted === true) {
			return tap($user)->refresh();
		}

		return $deleted;

	}
}
