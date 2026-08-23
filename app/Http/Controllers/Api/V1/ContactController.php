<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexContactRequest;
use App\Http\Requests\Api\StoreContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function index(IndexContactRequest $request)
    {
        $query = Contact::with(['category', 'tags']);

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');

            $query->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $contacts = $query
            ->latest()
            ->paginate($request->integer('per_page', 7));

        return ContactResource::collection($contacts);
    }

    public function show(int $id): ContactResource
    {
        $contact = Contact::with(['category', 'tags'])->findOrFail($id);

        return new ContactResource($contact);
    }

    public function store(StoreContactRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $tagIds = $validated['tag_ids'];
        unset($validated['tag_ids']);

        $contact = Contact::create($validated);

        $contact->tags()->sync($tagIds);

        $contact->load(['category', 'tags']);

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    public function update(StoreContactRequest $request, int $id): ContactResource
    {
        $contact = Contact::findOrFail($id);

        $validated = $request->validated();

        $tagIds = $validated['tag_ids'];
        unset($validated['tag_ids']);

        $contact->update($validated);

        $contact->tags()->sync($tagIds);

        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    public function destroy(int $id)
    {
        $contact = Contact::findOrFail($id);

        $contact->delete();

        return response()->noContent();
    }
}
