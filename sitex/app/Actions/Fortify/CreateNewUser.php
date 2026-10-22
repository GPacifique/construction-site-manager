<?php

namespace App\Actions\Fortify;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'company_name' => ['required', 'string', 'max:255'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::defaults(),
            ],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => Str::lower($input['email']),
                'password' => Hash::make($input['password']),
            ]);

            $company = Company::create([
                'name' => $input['company_name'],
                'slug' => Str::slug($input['company_name'])
                    . '-' . Str::lower(Str::random(8)),
                'email' => Str::lower($input['email']),
                'country' => 'Rwanda',
                'currency' => 'RWF',
                'created_by' => $user->id,
            ]);

            $company->users()->attach($user->id, [
                'role' => 'company_owner',
                'status' => 'active',
            ]);

            return $user;
        });
    }
}
