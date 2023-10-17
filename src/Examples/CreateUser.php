<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Validator;

class CreateUser
{
	public function execute(array $request): User
	{
		$this->validate($request);

		$user = User::create([
			...$request,
		]);

		return tap($user)->refresh();
	}

	private function validate(array $request): array
	{
		return Validator::validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|unique:users,email|max:255|email',
            'email_verified_at' => 'nullable',
            'password' => 'required|max:255|confirmed',
            'two_factor_secret' => 'nullable',
            'two_factor_recovery_codes' => 'nullable',
            'two_factor_confirmed_at' => 'nullable',
            'remember_token' => 'nullable|max:100',
            'current_team_id' => 'nullable',
            'profile_photo_path' => 'nullable|max:2048'
		]);
	}
}
