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

    /**
     * Download CSV template for contact import.
     */
    public function sampleCsv()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_kontak.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // Write BOM for UTF-8 compatibility in Excel
            fputs($file, "\xEF\xBB\xBF");
            // Write CSV Headers
            fputcsv($file, ['nama', 'tipe', 'telepon', 'email', 'alamat']);

            // Sample rows
            fputcsv($file, ['Budi Santoso', 'customer', '081234567890', 'budi@example.com', 'Jl. Merdeka No. 10, Jakarta']);
            fputcsv($file, ['PT Supplier Kayu Jati', 'supplier', '085678901234', 'supplier@example.com', 'Jl. Industri No. 45, Jepara']);
            fputcsv($file, ['Pak Made Pengrajin', 'crafter', '087890123456', '', 'Desa Ukir, Bali']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk import contacts via CSV or JSON payload.
     */
    public function import(Request $request)
    {
        $importedCount = 0;

        // Mode A: JSON array (Web Contact Picker / Batch array payload)
        if ($request->has('contacts') && is_array($request->contacts)) {
            $validated = $request->validate([
                'default_type'       => 'nullable|string|in:customer,supplier,crafter',
                'contacts'           => 'required|array|min:1',
                'contacts.*.name'    => 'required|string|max:255',
                'contacts.*.phone'   => 'nullable|string|max:50',
                'contacts.*.email'   => 'nullable|string|max:255',
                'contacts.*.address' => 'nullable|string',
                'contacts.*.type'    => 'nullable|string|in:customer,supplier,crafter',
            ]);

            $defaultType = $validated['default_type'] ?? 'customer';

            foreach ($validated['contacts'] as $item) {
                Contact::create([
                    'type'    => $item['type'] ?? $defaultType,
                    'name'    => $item['name'],
                    'phone'   => $item['phone'] ?? null,
                    'email'   => $item['email'] ?? null,
                    'address' => $item['address'] ?? null,
                ]);
                $importedCount++;
            }
        }
        // Mode B: File upload (CSV)
        elseif ($request->hasFile('file')) {
            $request->validate([
                'file'         => 'required|file|max:5120',
                'default_type' => 'nullable|string|in:customer,supplier,crafter',
            ]);

            $defaultType = $request->input('default_type', 'customer');
            $file = $request->file('file');
            $handle = fopen($file->getRealPath(), 'r');

            if ($handle !== false) {
                $header = fgetcsv($handle, 1000, ',');

                $headerMap = [];
                if ($header) {
                    foreach ($header as $index => $colName) {
                        $cleanCol = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', $colName)));
                        $headerMap[$cleanCol] = $index;
                    }
                }

                while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    if (empty(array_filter($row))) continue;

                    $name    = isset($headerMap['nama']) && isset($row[$headerMap['nama']]) ? trim($row[$headerMap['nama']]) : (isset($row[0]) ? trim($row[0]) : '');
                    $type    = isset($headerMap['tipe']) && isset($row[$headerMap['tipe']]) ? strtolower(trim($row[$headerMap['tipe']])) : '';
                    $phone   = isset($headerMap['telepon']) && isset($row[$headerMap['telepon']]) ? trim($row[$headerMap['telepon']]) : (isset($row[2]) ? trim($row[2]) : null);
                    $email   = isset($headerMap['email']) && isset($row[$headerMap['email']]) ? trim($row[$headerMap['email']]) : (isset($row[3]) ? trim($row[3]) : null);
                    $address = isset($headerMap['alamat']) && isset($row[$headerMap['alamat']]) ? trim($row[$headerMap['alamat']]) : (isset($row[4]) ? trim($row[4]) : null);

                    if (!$name) continue;

                    if (!in_array($type, ['customer', 'supplier', 'crafter'])) {
                        $type = $defaultType;
                    }

                    Contact::create([
                        'name'    => $name,
                        'type'    => $type,
                        'phone'   => $phone ?: null,
                        'email'   => $email ?: null,
                        'address' => $address ?: null,
                    ]);

                    $importedCount++;
                }

                fclose($handle);
            }
        } else {
            return redirect()->back()->with('error', 'Silakan pilih file CSV atau pilih kontak untuk diimpor.');
        }

        return redirect()->route('contacts.index')
            ->with('success', "Berhasil mengimpor {$importedCount} kontak baru!");
    }
}
