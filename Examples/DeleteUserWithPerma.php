<?php

namespace App\Actions\User;

use App\Models\User;

class DeleteUser
{
	/**
	 * This option is recommended for models with soft deletes trait.
	 */
	public function execute(User $user, bool $perma = false): User|bool
	{
		$perma ? $user->forceDelete() : $user->delete();

		return $perma ? true : tap($user)->refresh();
	}
}
