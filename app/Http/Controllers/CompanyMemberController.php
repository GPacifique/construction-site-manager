<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyMemberRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompanyMemberController extends Controller
{
    public function store(
        StoreCompanyMemberRequest $request,
        Company $company,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $company): void {
            Company::query()->whereKey($company->getKey())->lockForUpdate()->firstOrFail();

            $member = User::query()
                ->where('email', $request->validated('email'))
                ->firstOrFail();

            if ($company->users()->whereKey($member->getKey())->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'This user already has a membership in the company.',
                ]);
            }

            $company->users()->attach($member->getKey(), [
                'role' => $request->validated('role'),
                'status' => 'active',
            ]);
        });

        return back()->with('success', 'Company member added successfully.');
    }
}
