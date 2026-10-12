<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    private function company(Request $request): Company
    {
        $company = $request->user()
            ->companies()
            ->wherePivot('status', 'active')
            ->wherePivot('role', 'company_admin')
            ->first();

        abort_unless($company, 403, 'Company administrator access required.');

        return $company;
    }

    public function index(Request $request)
    {
        $company = $this->company($request);

        $members = CompanyMembership::with('user')
            ->where('company_id', $company->id)
            ->orderBy('id')
            ->get();

        return view('team.index', compact('company', 'members'));
    }

    public function store(Request $request)
    {
        $company = $this->company($request);

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', Rule::in([
                'company_admin',
                'manager',
                'technician',
            ])],
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        $membership = CompanyMembership::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->first();

        if ($membership) {
            return back()->withErrors([
                'email' => 'This user already belongs to your company.',
            ])->withInput();
        }

        CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role' => $validated['role'],
            'status' => 'active',
        ]);

        return back()->with('success', 'Team member added successfully.');
    }

    public function update(Request $request, CompanyMembership $membership)
    {
        $company = $this->company($request);

        abort_unless($membership->company_id === $company->id, 404);

        $validated = $request->validate([
            'role' => ['required', Rule::in([
                'company_admin',
                'manager',
                'technician',
            ])],
        ]);

        if (
            $membership->role === 'company_admin' &&
            $validated['role'] !== 'company_admin' &&
            $this->activeAdminCount($company) <= 1
        ) {
            return back()->withErrors([
                'role' => 'A company must have at least one active administrator.',
            ]);
        }

        $membership->update([
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'Team member updated successfully.');
    }

    public function destroy(Request $request, CompanyMembership $membership)
    {
        $company = $this->company($request);

        abort_unless($membership->company_id === $company->id, 404);

        if (
            $membership->role === 'company_admin' &&
            $membership->status === 'active' &&
            $this->activeAdminCount($company) <= 1
        ) {
            return back()->withErrors([
                'member' => 'You cannot remove the last active administrator.',
            ]);
        }

        $membership->delete();

        return back()->with('success', 'Team member removed successfully.');
    }

    private function activeAdminCount(Company $company): int
    {
        return CompanyMembership::where('company_id', $company->id)
            ->where('role', 'company_admin')
            ->where('status', 'active')
            ->count();
    }
}
