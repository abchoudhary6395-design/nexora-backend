<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreCustomerRequest;
use App\Http\Requests\CRM\UpdateCustomerRequest;
use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Customer::with(['company', 'owner', 'tags'])
            ->withCount('deals')
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhereHas('company', fn ($c) => $c->where('name', 'like', "%{$request->search}%"));
            })
            ->when($request->owner_id, fn ($q) => $q->where('owner_id', $request->owner_id))
            ->when($request->sort_by, function ($q) use ($request) {
                $q->orderBy($request->sort_by, $request->sort_dir === 'desc' ? 'desc' : 'asc');
            }, fn ($q) => $q->latest());

        return $this->paginated($query->paginate($request->per_page ?? 15));
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->safe()->except('tags'));

        if ($request->filled('tags')) {
            $customer->syncTags($request->tags);
        }

        $customer->logActivity('customer_created', "Customer \"{$customer->name}\" was created");

        return $this->success($customer->load(['company', 'owner']), 'Customer created', 201);
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        return $this->success($customer->load([
            'company', 'owner', 'contacts', 'deals.stage', 'tasks', 'invoices',
            'projects', 'notes.user', 'activities.user', 'attachments', 'tags',
        ]));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        $customer->logActivity('customer_updated', "Customer \"{$customer->name}\" was updated");

        return $this->success($customer->fresh(['company', 'owner']), 'Customer updated');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return $this->success(null, 'Customer deleted');
    }
}
