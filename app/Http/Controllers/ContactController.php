<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ContactController extends Controller
{
    public function index(Request $request): InertiaResponse
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
        
        $contacts = $query->orderBy('name')->paginate(15)->withQueryString();

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'filters' => $request->only(['search', 'type']),
        ]);
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

        return redirect()->route('contacts.index')
            ->with('success', 'Kontak berhasil ditambahkan!');
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

        return redirect()->route('contacts.index')
            ->with('success', 'Kontak berhasil diperbarui!');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', 'Kontak berhasil dihapus!');
    }
}
