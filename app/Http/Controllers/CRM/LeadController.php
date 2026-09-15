<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreLeadRequest;
use App\Http\Requests\CRM\UpdateLeadRequest;
use App\Models\Lead;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Lead::with(['source', 'status', 'assignee', 'tags'])
            ->when($request->status, fn ($q) => $q->whereHas('status', fn ($s) => $s->where('name', $request->status)))
            ->when($request->source, fn ($q) => $q->whereHas('source', fn ($s) => $s->where('name', $request->source)))
            ->when($request->assigned_to, fn ($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('name', 'like', "%{$request->search}%")
                        ->orWhere('company_name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->when($request->sort_by, function ($q) use ($request) {
                $q->orderBy($request->sort_by, $request->sort_dir === 'desc' ? 'desc' : 'asc');
            }, fn ($q) => $q->latest());

        return $this->paginated($query->paginate($request->per_page ?? 15));
    }

    public function store(StoreLeadRequest $request)
    {
        $lead = Lead::create($request->safe()->except('tags'));

        if ($request->filled('tags')) {
            $lead->syncTags($request->tags);
        }

        $lead->logActivity('lead_created', "Lead \"{$lead->name}\" was created");

        return $this->success($lead->load(['source', 'status', 'assignee']), 'Lead created', 201);
    }

    public function show(Lead $lead)
    {
        $this->authorize('view', $lead);

        return $this->success(
            $lead->load(['source', 'status', 'assignee', 'tags', 'notes.user', 'activities.user', 'attachments'])
        );
    }

    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $lead->update($request->validated());
        $lead->logActivity('lead_updated', "Lead \"{$lead->name}\" was updated");

        return $this->success($lead->fresh(['source', 'status', 'assignee']), 'Lead updated');
    }

    public function destroy(Lead $lead)
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return $this->success(null, 'Lead deleted');
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:leads,id'],
            'userId' => ['required', 'exists:users,id'],
        ]);

        Lead::whereIn('id', $request->ids)->update(['assigned_to' => $request->userId]);

        return $this->success(null, count($request->ids).' leads reassigned');
    }

    /** Converts a lead into a real Customer record. */
    public function convert(Lead $lead)
    {
        if ($lead->converted_customer_id) {
            return $this->error('This lead has already been converted', 422);
        }

        $customer = $lead->convertToCustomer();

        return $this->success($customer, 'Lead converted to customer', 201);
    }
}
