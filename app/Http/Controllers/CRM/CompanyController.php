<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreCompanyRequest;
use App\Http\Requests\CRM\UpdateCompanyRequest;
use App\Models\Company;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Company::with('owner')
            ->withCount(['customers', 'deals'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->industry, fn ($q) => $q->where('industry', $request->industry))
            ->when($request->country, fn ($q) => $q->where('country', $request->country))
            ->when($request->sort_by, function ($q) use ($request) {
                $q->orderBy($request->sort_by, $request->sort_dir === 'desc' ? 'desc' : 'asc');
            }, fn ($q) => $q->latest());

        return $this->paginated($query->paginate($request->per_page ?? 15));
    }

    public function store(StoreCompanyRequest $request)
    {
        $company = Company::create($request->validated());
        $company->logActivity('company_created', "Company \"{$company->name}\" was created");

        return $this->success($company->load('owner'), 'Company created', 201);
    }

    public function show(Company $company)
    {
        $this->authorize('view', $company);

        return $this->success($company->load([
            'owner', 'customers', 'contacts', 'deals.stage', 'notes.user', 'activities.user', 'tags',
        ]));
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company->update($request->validated());
        $company->logActivity('company_updated', "Company \"{$company->name}\" was updated");

        return $this->success($company->fresh('owner'), 'Company updated');
    }

    public function destroy(Company $company)
    {
        $this->authorize('delete', $company);

        $company->delete();

        return $this->success(null, 'Company deleted');
    }
}
