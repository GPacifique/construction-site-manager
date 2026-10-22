<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'company_name' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request): User {
            $user = User::create(array(
                'name' => $request->string('name')->toString(),
                'email' => Str::lower($request->string('email')->toString()),
                'password' => $request->string('password')->toString(),
            ));

            $company = Company::create(array(
                'name' => $request->string('company_name')->toString(),
                'slug' => Str::slug($request->string('company_name')->toString())
                    .'-'.Str::lower(Str::random(8)),
                'email' => $user->email,
                'country' => 'Rwanda',
                'currency' => 'RWF',
                'created_by' => $user->id,
            ));

            $company->users()->attach($user->id, array(
                'role' => 'company_owner',
                'status' => 'active',
            ));

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
