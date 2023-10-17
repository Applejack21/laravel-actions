<?php

namespace App\Actions\User;

use App\Models\User;

class DeleteUser
{
	public function execute(User $user): User
	{
		$user->delete();

		return tap($user)->refresh();
	}
}
