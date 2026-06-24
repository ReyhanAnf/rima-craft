<!-- Desktop Table -->
<div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-800">
            <tr>
                <th scope="col" class="px-4 py-3 font-semibold">Nama Kontak</th>
                <th scope="col" class="px-4 py-3 font-semibold">Tipe</th>
                <th scope="col" class="px-4 py-3 font-semibold">Nomor Telepon</th>
                <th scope="col" class="px-4 py-3 font-semibold">Alamat</th>
                <th scope="col" class="px-4 py-3 font-semibold text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($contacts as $contact)
            <tr class="bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors">
                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $contact->name }}</td>
                <td class="px-4 py-2.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border 
                        {{ $contact->type === 'supplier' ? 'bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-400' : '' }}
                        {{ $contact->type === 'customer' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' : '' }}
                        {{ $contact->type === 'crafter' ? 'bg-purple-50 border-purple-200 text-purple-700 dark:bg-purple-500/10 dark:border-purple-500/20 dark:text-purple-400' : '' }}">
                        {{ ucfirst($contact->type) }}
                    </span>
                </td>
                <td class="px-4 py-2.5 text-xs">{{ $contact->phone ?? '-' }}</td>
                <td class="px-4 py-2.5 text-xs">{{ \Illuminate\Support\Str::limit($contact->address, 30) ?? '-' }}</td>
                <td class="px-4 py-2.5 text-right text-xs">
                    <button hx-get="{{ route('contacts.edit', $contact) }}" hx-target="#drawer-content" hx-swap="innerHTML" @click="$dispatch('open-drawer')" class="font-medium text-primary-600 dark:text-primary-400 hover:underline mr-3">Edit</button>
                    <button hx-delete="{{ route('contacts.destroy', $contact) }}" hx-confirm="Hapus {{ $contact->name }}?" hx-target="#contacts-list" hx-swap="innerHTML" class="font-medium text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-6 text-center text-xs text-gray-500">Data tidak ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Mobile Cards -->
<div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
    @forelse ($contacts as $contact)
    <div class="p-4 bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors flex justify-between items-center cursor-pointer active:bg-gray-100 dark:active:bg-gray-800"
         hx-get="{{ route('contacts.edit', $contact) }}" 
         hx-target="#drawer-content" 
         hx-swap="innerHTML" 
         @click="$dispatch('open-drawer')">
        <div class="flex-1 mr-4">
            <div class="flex items-center gap-2 mb-1">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $contact->name }}</h4>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold border uppercase tracking-wider
                    {{ $contact->type === 'supplier' ? 'bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-400' : '' }}
                    {{ $contact->type === 'customer' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' : '' }}
                    {{ $contact->type === 'crafter' ? 'bg-purple-50 border-purple-200 text-purple-700 dark:bg-purple-500/10 dark:border-purple-500/20 dark:text-purple-400' : '' }}">
                    {{ $contact->type }}
                </span>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5 mt-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                {{ $contact->phone ?? '-' }}
            </div>
        </div>
        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
    </div>
    @empty
    <div class="p-4 text-center text-xs text-gray-500">Data tidak ditemukan.</div>
    @endforelse
</div>

@if($contacts->hasPages())
<div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50" hx-boost="true" hx-target="#contacts-list" hx-swap="innerHTML">
    {{ $contacts->links('pagination::tailwind') }}
</div>
@endif

