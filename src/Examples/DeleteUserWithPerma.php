<?php

namespace App\Actions\User;

use App\Models\User;

class DeleteUser
{
	public function execute(User $user, bool $perma = false): User
	{
		$perma ? $user->forceDelete() : $user->delete();

		return $perma ? true : tap($user)->refresh();
	}
}
