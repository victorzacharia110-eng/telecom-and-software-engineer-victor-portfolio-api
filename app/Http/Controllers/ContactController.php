<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(ContactRequest $request): JsonResponse
    {
        $contact = Contact::create($request->validated());

        // Optionally send email notification
        // Mail::to('info@telesoft.co.tz')->send(new ContactReceived($contact));

        return response()->json([
            'success' => true,
            'message' => 'Message received. We will contact you within 24 hours.',
            'data'    => $contact->only(['id', 'name', 'created_at']),
        ], 201);
    }

    public function index(): JsonResponse
    {
        $contacts = Contact::latest()->paginate(20);
        return response()->json($contacts);
    }

    public function show(Contact $contact): JsonResponse
    {
        return response()->json($contact);
    }

    public function markRead(Contact $contact): JsonResponse
    {
        $contact->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();
        return response()->json(['success' => true]);
    }
}
