<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreDealRequest;
use App\Http\Requests\CRM\UpdateDealRequest;
use App\Http\Requests\CRM\UpdateDealStageRequest;
use App\Models\Deal;
use App\Models\DealStage;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DealController extends Controller
{
    use ApiResponse;

    /**
     * Returns deals grouped by stage — exactly what the frontend's
     * Kanban board (src/pages/Deals/Deals.jsx) needs to render columns.
     */
    public function index(Request $request)
    {
        $query = Deal::with(['stage', 'customer', 'company', 'owner'])
            ->when($request->owner_id, fn ($q) => $q->where('owner_id', $request->owner_id))
            ->when($request->search, fn ($q) => $q->where('title', 'like', "%{$request->search}%"));

        if ($request->boolean('grouped')) {
            $stages = DealStage::orderBy('sort_order')->get();
            $deals = $query->get();

            return $this->success(
                $stages->map(fn ($stage) => [
                    'stage' => $stage,
                    'deals' => $deals->where('deal_stage_id', $stage->id)->values(),
                    'total_value' => $deals->where('deal_stage_id', $stage->id)->sum('value'),
                ])
            );
        }

        return $this->paginated($query->latest()->paginate($request->per_page ?? 15));
    }

    public function store(StoreDealRequest $request)
    {
        $deal = Deal::create($request->safe()->except('products'));

        foreach ($request->input('products', []) as $product) {
            $deal->products()->create($product);
        }

        $deal->logActivity('deal_created', "Deal \"{$deal->title}\" was created");

        return $this->success($deal->load(['stage', 'customer', 'company', 'products']), 'Deal created', 201);
    }

    public function show(Deal $deal)
    {
        $this->authorize('view', $deal);

        return $this->success($deal->load([
            'stage', 'customer', 'company', 'owner', 'products',
            'notes.user', 'activities.user', 'attachments',
        ]));
    }

    public function update(UpdateDealRequest $request, Deal $deal)
    {
        $deal->update($request->validated());

        return $this->success($deal->fresh(['stage', 'customer', 'company']), 'Deal updated');
    }

    /** Moves a deal to a new pipeline stage — called on Kanban drag-and-drop. */
    public function updateStage(UpdateDealStageRequest $request, Deal $deal)
    {
        $stage = DealStage::where('key', $request->stage)->firstOrFail();
        $deal->moveToStage($stage);

        return $this->success($deal->fresh('stage'), "Deal moved to {$stage->label}");
    }

    public function destroy(Deal $deal)
    {
        $this->authorize('delete', $deal);

        $deal->delete();

        return $this->success(null, 'Deal deleted');
    }
}
