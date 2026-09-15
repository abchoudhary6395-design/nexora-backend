<?php

namespace App\Http\Controllers\Files;

use App\Http\Controllers\Controller;
use App\Http\Requests\Files\StoreDocumentRequest;
use App\Models\Document;
use App\Models\Folder;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Document::with(['folder', 'uploader'])
            ->when($request->folder_id, fn ($q) => $q->where('folder_id', $request->folder_id))
            ->when($request->favorite, fn ($q) => $q->where('is_favorite', true))
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"));

        return $this->success([
            'folders' => Folder::withCount('documents')->whereNull('parent_id')->get(),
            'documents' => $query->latest()->paginate($request->per_page ?? 20),
        ]);
    }

    public function store(StoreDocumentRequest $request)
    {
        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        $document = Document::create([
            'name' => $file->getClientOriginalName(),
            'folder_id' => $request->folder_id,
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return $this->success($document, 'Document uploaded', 201);
    }

    public function update(Request $request, Document $document)
    {
        $document->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'folder_id' => ['nullable', 'exists:folders,id'],
            'is_favorite' => ['sometimes', 'boolean'],
        ]));

        return $this->success($document->fresh(), 'Document updated');
    }

    public function destroy(Document $document)
    {
        $document->delete();

        return $this->success(null, 'Document deleted');
    }
}
