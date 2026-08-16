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
        
        // Search by name, phone, or address
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%');
            });
        }
        
        $contacts = $query->orderBy('name')->paginate(15)->withQueryString();

        // Get lightweight summary of existing contacts for client-side duplicate detection
        $existingContacts = Contact::select('id', 'name', 'phone', 'email', 'type')->get()->map(function ($c) {
            $cleanPhone = preg_replace('/[^\d]/', '', (string)$c->phone);
            // If phone starts with 62, normalize to 0
            if (str_starts_with($cleanPhone, '62') && strlen($cleanPhone) > 9) {
                $cleanPhone = '0' . substr($cleanPhone, 2);
            }
            return [
                'id' => $c->id,
                'name' => mb_strtolower(trim($c->name)),
                'phone' => $c->phone,
                'clean_phone' => $cleanPhone,
                'email' => mb_strtolower(trim((string)$c->email)),
                'type' => $c->type,
            ];
        });

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'filters' => $request->only(['search', 'type']),
            'existingContacts' => $existingContacts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:supplier,customer,crafter,reseller',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        if (!empty($validated['phone'])) {
            $validated['phone'] = $this->normalizePhone($validated['phone']);
        }

        Contact::create($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Kontak berhasil ditambahkan!');
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'type' => 'required|in:supplier,customer,crafter,reseller',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        if (!empty($validated['phone'])) {
            $validated['phone'] = $this->normalizePhone($validated['phone']);
        }

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
            fputcsv($file, ['Rina Reseller Tasik', 'reseller', '081987654321', 'rina@reseller.id', 'Jl. Sukalaya No. 15, Tasikmalaya']);
            fputcsv($file, ['PT Supplier Kayu Jati', 'supplier', '085678901234', 'supplier@example.com', 'Jl. Industri No. 45, Jepara']);
            fputcsv($file, ['Pak Made Pengrajin', 'crafter', '087890123456', '', 'Desa Ukir, Bali']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk import contacts via JSON payload (Web Contact Picker) or File upload (.vcf / .csv).
     */
    public function import(Request $request)
    {
        $duplicateStrategy = $request->input('duplicate_strategy', 'skip'); // 'skip', 'update', 'create_all'
        $defaultType = $request->input('default_type', 'customer');
        if (!in_array($defaultType, ['customer', 'supplier', 'crafter', 'reseller'])) {
            $defaultType = 'customer';
        }

        $itemsToImport = [];

        // Mode A: JSON payload from Web Contact Picker or parsed frontend file
        if ($request->has('contacts') && is_array($request->contacts)) {
            $validated = $request->validate([
                'default_type'       => 'nullable|string|in:customer,supplier,crafter,reseller',
                'duplicate_strategy' => 'nullable|string|in:skip,update,create_all',
                'contacts'           => 'required|array|min:1',
                'contacts.*.name'    => 'required|string|max:255',
                'contacts.*.phone'   => 'nullable|string|max:50',
                'contacts.*.email'   => 'nullable|string|max:255',
                'contacts.*.address' => 'nullable|string',
                'contacts.*.type'    => 'nullable|string|in:customer,supplier,crafter,reseller',
            ]);

            foreach ($validated['contacts'] as $item) {
                $itemsToImport[] = [
                    'name'    => trim($item['name']),
                    'type'    => $item['type'] ?? $defaultType,
                    'phone'   => $this->normalizePhone($item['phone'] ?? null),
                    'email'   => !empty($item['email']) ? trim($item['email']) : null,
                    'address' => !empty($item['address']) ? trim($item['address']) : null,
                ];
            }
        }
        // Mode B: File upload (.csv or .vcf)
        elseif ($request->hasFile('file')) {
            $request->validate([
                'file'               => 'required|file|max:10240',
                'default_type'       => 'nullable|string|in:customer,supplier,crafter,reseller',
                'duplicate_strategy' => 'nullable|string|in:skip,update,create_all',
            ]);

            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $realPath = $file->getRealPath();

            if ($extension === 'vcf' || $extension === 'vcard' || str_contains($file->getMimeType(), 'vcard')) {
                $vcfContent = file_get_contents($realPath);
                $itemsToImport = $this->parseVCardContent($vcfContent ?: '', $defaultType);
            } else {
                $handle = fopen($realPath, 'r');
                if ($handle !== false) {
                    $itemsToImport = $this->parseCsvContent($handle, $defaultType);
                    fclose($handle);
                }
            }
        } else {
            return redirect()->back()->with('error', 'Silakan pilih kontak perangkat atau unggah file kontak (.vcf / .csv).');
        }

        if (empty($itemsToImport)) {
            return redirect()->back()->with('error', 'Tidak ada kontak valid yang ditemukan untuk diimpor.');
        }

        // Process batch import with duplicate strategy
        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($itemsToImport as $item) {
            $name = $item['name'];
            $phone = $this->normalizePhone($item['phone'] ?? null);
            $email = $item['email'] ?? null;
            $address = $item['address'] ?? null;
            $type = in_array($item['type'] ?? '', ['customer', 'supplier', 'crafter', 'reseller']) ? $item['type'] : $defaultType;

            // Find duplicate by phone (primary) or by exact name
            $existing = null;
            if (!empty($phone)) {
                $cleanPhone = preg_replace('/[^\d]/', '', $phone);
                $existing = Contact::whereRaw("REPLACE(REPLACE(REPLACE(phone, '-', ''), ' ', ''), '+', '') = ?", [$cleanPhone])
                    ->orWhere('phone', $phone)
                    ->first();
            }

            if (!$existing && !empty($name)) {
                $existing = Contact::where('name', $name)->first();
            }

            if ($existing) {
                if ($duplicateStrategy === 'skip') {
                    $skippedCount++;
                    continue;
                } elseif ($duplicateStrategy === 'update') {
                    $updateData = [];
                    if (!empty($name)) $updateData['name'] = $name;
                    if (!empty($phone)) $updateData['phone'] = $phone;
                    if (!empty($email)) $updateData['email'] = $email;
                    if (!empty($address)) $updateData['address'] = $address;
                    if (!empty($type)) $updateData['type'] = $type;

                    $existing->update($updateData);
                    $updatedCount++;
                    continue;
                }
            }

            // Create new contact
            Contact::create([
                'name'    => $name,
                'type'    => $type,
                'phone'   => $phone,
                'email'   => $email,
                'address' => $address,
            ]);
            $importedCount++;
        }

        $messageParts = [];
        if ($importedCount > 0) {
            $messageParts[] = "{$importedCount} kontak baru berhasil ditambahkan";
        }
        if ($updatedCount > 0) {
            $messageParts[] = "{$updatedCount} kontak diperbarui";
        }
        if ($skippedCount > 0) {
            $messageParts[] = "{$skippedCount} kontak duplikat dilewati";
        }

        $summaryMessage = !empty($messageParts)
            ? implode(', ', $messageParts) . '.'
            : 'Tidak ada perubahan kontak yang disimpan.';

        return redirect()->route('contacts.index')
            ->with('success', "Import selesai: {$summaryMessage}");
    }

    /**
     * Normalize Indonesian and international phone numbers.
     */
    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $clean = trim($phone);
        // Remove spaces, dashes, parentheses
        $clean = preg_replace('/[\s\-\(\)\.]/', '', $clean);

        // Convert +62... or 62... to 08...
        if (str_starts_with($clean, '+62')) {
            $clean = '0' . substr($clean, 3);
        } elseif (str_starts_with($clean, '62') && strlen($clean) > 9) {
            $clean = '0' . substr($clean, 2);
        }

        return $clean;
    }

    /**
     * Parse vCard (.vcf) content exported from Android / Google Contacts / iOS.
     */
    private function parseVCardContent(string $content, string $defaultType): array
    {
        $contacts = [];
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $current = null;

        foreach ($lines as $rawLine) {
            $line = trim($rawLine);
            if (empty($line)) continue;

            if (strcasecmp($line, 'BEGIN:VCARD') === 0) {
                $current = [
                    'name' => '',
                    'phone' => '',
                    'email' => '',
                    'address' => '',
                    'type' => $defaultType,
                ];
                continue;
            }

            if (strcasecmp($line, 'END:VCARD') === 0) {
                if ($current && !empty($current['name'])) {
                    $contacts[] = $current;
                }
                $current = null;
                continue;
            }

            if (!$current) continue;

            // Full Name (FN)
            if (preg_match('/^FN(?:;[^:]*)?:(.*)$/i', $line, $matches)) {
                $current['name'] = trim($matches[1]);
            }
            // Structured Name (N) as fallback if FN is empty
            elseif (empty($current['name']) && preg_match('/^N(?:;[^:]*)?:(.*)$/i', $line, $matches)) {
                $parts = explode(';', $matches[1]);
                $cleanParts = array_filter(array_map('trim', array_reverse($parts)));
                $current['name'] = implode(' ', $cleanParts);
            }
            // Phone (TEL)
            elseif (preg_match('/^TEL(?:;[^:]*)?:(.*)$/i', $line, $matches)) {
                if (empty($current['phone'])) {
                    $current['phone'] = $this->normalizePhone($matches[1]);
                }
            }
            // Email (EMAIL)
            elseif (preg_match('/^EMAIL(?:;[^:]*)?:(.*)$/i', $line, $matches)) {
                if (empty($current['email'])) {
                    $current['email'] = trim($matches[1]);
                }
            }
            // Address (ADR)
            elseif (preg_match('/^ADR(?:;[^:]*)?:(.*)$/i', $line, $matches)) {
                if (empty($current['address'])) {
                    $adrParts = explode(';', $matches[1]);
                    $cleanAdr = array_filter(array_map('trim', $adrParts));
                    $current['address'] = implode(', ', $cleanAdr);
                }
            }
        }

        return $contacts;
    }

    /**
     * Parse CSV files (Standard template or Google Contacts export).
     */
    private function parseCsvContent($handle, string $defaultType): array
    {
        $contacts = [];
        $header = fgetcsv($handle, 2000, ',');
        if (!$header) return $contacts;

        $headerMap = [];
        foreach ($header as $index => $colName) {
            $cleanCol = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', (string)$colName)));
            $headerMap[$cleanCol] = $index;
        }

        while (($row = fgetcsv($handle, 2000, ',')) !== false) {
            if (empty(array_filter($row))) continue;

            // Name
            $name = '';
            if (isset($headerMap['nama']) && isset($row[$headerMap['nama']])) {
                $name = trim($row[$headerMap['nama']]);
            } elseif (isset($headerMap['name']) && isset($row[$headerMap['name']])) {
                $name = trim($row[$headerMap['name']]);
            } elseif (isset($headerMap['givenname'])) {
                $first = trim($row[$headerMap['givenname']] ?? '');
                $last = trim($row[$headerMap['familyname']] ?? '');
                $name = trim("{$first} {$last}");
            } else {
                $name = isset($row[0]) ? trim($row[0]) : '';
            }

            if (!$name) continue;

            // Type
            $type = '';
            if (isset($headerMap['tipe']) && isset($row[$headerMap['tipe']])) {
                $type = strtolower(trim($row[$headerMap['tipe']]));
            } elseif (isset($headerMap['type']) && isset($row[$headerMap['type']])) {
                $type = strtolower(trim($row[$headerMap['type']]));
            }
            if (!in_array($type, ['customer', 'supplier', 'crafter', 'reseller'])) {
                $type = $defaultType;
            }

            // Phone
            $phone = null;
            if (isset($headerMap['telepon']) && isset($row[$headerMap['telepon']])) {
                $phone = trim($row[$headerMap['telepon']]);
            } elseif (isset($headerMap['phone']) && isset($row[$headerMap['phone']])) {
                $phone = trim($row[$headerMap['phone']]);
            } elseif (isset($headerMap['phone1value']) && isset($row[$headerMap['phone1value']])) {
                $phone = trim($row[$headerMap['phone1value']]);
            } elseif (isset($headerMap['phonenumber']) && isset($row[$headerMap['phonenumber']])) {
                $phone = trim($row[$headerMap['phonenumber']]);
            } elseif (isset($headerMap['mobile']) && isset($row[$headerMap['mobile']])) {
                $phone = trim($row[$headerMap['mobile']]);
            } else {
                $phone = isset($row[2]) ? trim($row[2]) : null;
            }

            // Email
            $email = null;
            if (isset($headerMap['email']) && isset($row[$headerMap['email']])) {
                $email = trim($row[$headerMap['email']]);
            } elseif (isset($headerMap['email1value']) && isset($row[$headerMap['email1value']])) {
                $email = trim($row[$headerMap['email1value']]);
            } elseif (isset($headerMap['emailaddress']) && isset($row[$headerMap['emailaddress']])) {
                $email = trim($row[$headerMap['emailaddress']]);
            } else {
                $email = isset($row[3]) ? trim($row[3]) : null;
            }

            // Address
            $address = null;
            if (isset($headerMap['alamat']) && isset($row[$headerMap['alamat']])) {
                $address = trim($row[$headerMap['alamat']]);
            } elseif (isset($headerMap['address']) && isset($row[$headerMap['address']])) {
                $address = trim($row[$headerMap['address']]);
            } elseif (isset($headerMap['address1formatted']) && isset($row[$headerMap['address1formatted']])) {
                $address = trim($row[$headerMap['address1formatted']]);
            } else {
                $address = isset($row[4]) ? trim($row[4]) : null;
            }

            $contacts[] = [
                'name'    => $name,
                'type'    => $type,
                'phone'   => $this->normalizePhone($phone),
                'email'   => $email ?: null,
                'address' => $address ?: null,
            ];
        }

        return $contacts;
    }
}

