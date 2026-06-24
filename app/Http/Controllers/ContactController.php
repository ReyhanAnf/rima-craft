<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
        public function index(Request $request)
    {
        $query = Contact::query();
        
        // Filter by contact type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Search by name or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%');
            });
        }
        
        $contacts = $query->orderBy('name')->paginate(15);

        if ($request->header('HX-Target') === 'contacts-list') {
            return view('contacts.contacts-list', compact('contacts'));
        }
        return view('contacts.contacts-index', compact('contacts'));
    }

    public function create(): View
    {
        $contact = new Contact();
        return view('contacts.contacts-form', compact('contact'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:supplier,customer,crafter',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        Contact::create($validated);

        return response()
            ->view('contacts.contacts-list', ['contacts' => Contact::orderBy('name')->paginate(10)])
            ->header('HX-Trigger', json_encode([
                'close-drawer' => true,
                'toast' => ['message' => 'Kontak berhasil ditambahkan!', 'type' => 'success']
            ]));
    }

    public function edit(Contact $contact): View
    {
        return view('contacts.contacts-form', compact('contact'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'type' => 'required|in:supplier,customer,crafter',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $contact->update($validated);

        return response()
            ->view('contacts.contacts-list', ['contacts' => Contact::orderBy('name')->paginate(10)])
            ->header('HX-Trigger', json_encode([
                'close-drawer' => true,
                'toast' => ['message' => 'Kontak berhasil diperbarui!', 'type' => 'success']
            ]));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()
            ->view('contacts.contacts-list', ['contacts' => Contact::orderBy('name')->paginate(10)])
            ->header('HX-Trigger', json_encode([
                'toast' => ['message' => 'Kontak berhasil dihapus!', 'type' => 'success']
            ]));
    }
}
