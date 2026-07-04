<div class="px-4 sm:px-4 py-5 flex items-start justify-between border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 sticky top-0 z-10">
    <div>
        <h2 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">Detail Pesanan</h2>
        <p class="text-[10px] text-gray-500 mt-0.5">{{ $order->order_number }}</p>
    </div>
    <button type="button" @click="drawerOpen = false" class="rounded-full p-1.5 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
    </button>
</div>

<div class="px-4 sm:px-4 py-6 pb-24 space-y-6 overflow-y-auto">
    <!-- Quick Actions Panel -->
    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-md p-4 border border-gray-200 dark:border-gray-850" x-data="{ selectedStatus: '{{ $order->status }}' }">
        <form hx-patch="{{ route('orders.update-status', $order) }}" hx-target="#drawer-content" hx-swap="innerHTML" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div x-show="selectedStatus === 'shipped' || '{{ $order->tracking_number }}' !== ''"
                     x-transition class="col-span-2" style="display: none;">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Nomor Resi</label>
                    <input type="text" name="tracking_number"
                           value="{{ $order->tracking_number }}"
                           placeholder="Contoh: JNE123456789ID"
                           class="w-full text-xs font-mono border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded outline-none focus:ring-1 focus:ring-primary-500 px-2.5 py-1.5 text-gray-900 dark:text-gray-200">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Status Pesanan</label>
                    <select name="status" x-model="selectedStatus"
                            class="w-full text-xs font-bold border rounded outline-none focus:ring-1 focus:ring-primary-500 uppercase tracking-wider px-2.5 py-1.5 cursor-pointer bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200
                            {{ $order->status === 'pending' ? 'border-gray-300 text-gray-700' : '' }}
                            {{ $order->status === 'confirmed' ? 'border-blue-300 text-blue-700' : '' }}
                            {{ $order->status === 'processing' ? 'border-indigo-300 text-indigo-700' : '' }}
                            {{ $order->status === 'shipped' ? 'border-cyan-300 text-cyan-700' : '' }}
                            {{ $order->status === 'completed' ? 'border-emerald-300 text-emerald-700' : '' }}
                            {{ $order->status === 'cancelled' ? 'border-red-300 text-red-700' : '' }}">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Status Bayar</label>
                    <select name="payment_status"
                            class="w-full text-xs font-bold border rounded outline-none focus:ring-1 focus:ring-primary-500 uppercase tracking-wider px-2.5 py-1.5 cursor-pointer bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200
                            {{ $order->payment_status === 'paid' ? 'border-emerald-300 text-emerald-700' : '' }}
                            {{ $order->payment_status === 'unpaid' ? 'border-red-300 text-red-700' : '' }}
                            {{ $order->payment_status === 'partial' ? 'border-amber-300 text-amber-700' : '' }}
                            {{ $order->payment_status === 'refunded' ? 'border-amber-300 text-amber-700' : '' }}">
                        <option value="unpaid" {{ $order->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ $order->payment_status === 'partial' ? 'selected' : '' }}>DP / Partial</option>
                        <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>

                <div x-show="selectedStatus === 'shipped' || '{{ $order->tracking_number }}' !== ''"
                     x-transition class="col-span-2" style="display: none;">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Nomor Resi</label>
                    <input type="text" name="tracking_number"
                           value="{{ $order->tracking_number }}"
                           placeholder="Contoh: JNE123456789ID"
                           class="w-full text-xs font-mono border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded outline-none focus:ring-1 focus:ring-primary-500 px-2.5 py-1.5 text-gray-900 dark:text-gray-200">
                </div>
            </div>

            <!-- Cancellation Reason Input -->
            <div x-show="selectedStatus === 'cancelled'" x-transition class="pt-2" style="display: none;">
                <label class="block text-xs font-bold text-red-600 dark:text-red-400 mb-1">Alasan Pembatalan</label>
                <textarea name="cancellation_reason" rows="2" class="w-full text-xs px-3 py-2 border border-red-200 dark:border-red-900 bg-white dark:bg-gray-900 rounded-md text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-red-500 outline-none" placeholder="Masukkan alasan pembatalan pesanan...">{{ $order->cancellation_reason }}</textarea>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-3.5 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded text-xs font-semibold transition shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Customer Information -->
    <div>
        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3">Informasi Pelanggan</h4>
        <div class="bg-white dark:bg-gray-900 rounded-md border border-gray-200 dark:border-gray-800 p-4 space-y-3">
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-gray-400">Nama Lengkap</span>
                    <p class="font-semibold text-gray-900 dark:text-white mt-0.5">{{ $order->customer_name }}</p>
                </div>
                <div>
                    <span class="text-gray-400">No. Telepon / WA</span>
                    <p class="font-semibold text-gray-900 dark:text-white mt-0.5">{{ $order->customer_phone }}</p>
                </div>
                <div class="col-span-2">
                    <span class="text-gray-400">Email</span>
                    <p class="font-semibold text-gray-900 dark:text-white mt-0.5">{{ $order->customer_email ?? '-' }}</p>
                </div>
            </div>
            
            <div class="border-t border-gray-100 dark:border-gray-800 pt-3 text-xs">
                <span class="text-gray-400">Alamat Pengiriman</span>
                <p class="font-medium text-gray-800 dark:text-gray-200 mt-1 leading-relaxed">{{ $order->customer_address ?? '-' }}</p>
            </div>

            @if($order->notes)
            <div class="border-t border-gray-100 dark:border-gray-800 pt-3 text-xs">
                <span class="text-gray-400">Catatan Pelanggan</span>
                <p class="text-gray-600 dark:text-gray-400 mt-1 italic">"{{ $order->notes }}"</p>
            </div>
            @endif

            @if($order->user_id)
            <div class="border-t border-gray-100 dark:border-gray-800 pt-3 text-[10px] flex items-center justify-between text-gray-500">
                <span>Terhubung dengan Akun User ID: {{ $order->user_id }}</span>
                <a href="{{ route('users.edit', $order->user_id) }}" class="text-primary-600 dark:text-primary-400 hover:underline font-bold">Lihat Profil Akun &rarr;</a>
            </div>
            @endif

            <!-- Whatsapp URL Follow-up -->
            <div class="border-t border-gray-100 dark:border-gray-800 pt-3 flex gap-2">
                @php
                    $phone = preg_replace('/\D/', '', $order->customer_phone);
                    if (str_starts_with($phone, '0')) {
                        $phone = '62' . substr($phone, 1);
                    }
                    $waText = "Halo {$order->customer_name}, kami dari Rima Craft ingin mengonfirmasi pesanan Anda dengan nomor {$order->order_number}.";
                    $waLink = "https://wa.me/{$phone}?text=" . urlencode($waText);
                @endphp
                <a href="{{ $waLink }}" target="_blank" class="w-full flex items-center justify-center gap-1.5 py-2 bg-[#25D366] hover:bg-[#20ba5a] text-white rounded font-bold text-xs shadow-sm transition">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Hubungi via WhatsApp
                </a>
            </div>
        </div>
    </div>

    <!-- Items Detail -->
    <div>
        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3">Rincian Belanja</h4>
        <div class="bg-white dark:bg-gray-900 rounded-md border border-gray-200 dark:border-gray-800 p-4 space-y-3">
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($order->items as $item)
                <div class="flex justify-between items-center text-xs pb-2.5 pt-2.5 first:pt-0 last:pb-0">
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $item['name'] }}</div>
                        <div class="text-[10px] text-gray-500">{{ $item['qty'] }} pcs x Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                    </div>
                    <div class="font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($item['qty'] * $item['price'], 0, ',', '.') }}
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="border-t border-gray-100 dark:border-gray-800 pt-3 space-y-1.5 text-xs text-gray-500 text-right">
                <div class="flex justify-end gap-6">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($order->shipping_cost > 0)
                <div class="flex justify-end gap-6">
                    <span>Ongkir (+):</span>
                    <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between items-center pt-3 border-t border-gray-100 dark:border-gray-800 font-bold text-sm">
                    <span class="text-gray-950 dark:text-white">Grand Total:</span>
                    <span class="text-amber-600 dark:text-amber-400 text-base">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
                @if($order->down_payment_amount > 0)
                <div class="mt-3 pt-3 border-t border-amber-100 dark:border-amber-900/30 space-y-1.5 text-xs">
                    <div class="flex justify-end gap-6 font-semibold text-emerald-700 dark:text-emerald-400">
                        <span>DP Dibayar:</span>
                        <span>Rp {{ number_format($order->down_payment_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-end gap-6 font-bold text-amber-700 dark:text-amber-400">
                        <span>Sisa Piutang:</span>
                        <span>Rp {{ number_format($order->remaining_balance, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Payment Detail & Upload Proof -->
    <div>
        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3">Bukti Pembayaran</h4>
        <div class="bg-white dark:bg-gray-900 rounded-md border border-gray-200 dark:border-gray-800 p-4 space-y-4">
            <div class="text-xs">
                <span class="text-gray-400">Metode Pembayaran</span>
                <p class="font-bold text-gray-900 dark:text-white mt-0.5 uppercase tracking-wide">{{ $order->payment_method }} (via {{ $order->order_method }})</p>
            </div>

            <!-- Payment Proof Image -->
            <div>
                <span class="block text-xs text-gray-400 mb-2">Dokumen Bukti</span>
                @if($order->payment_proof)
                    <div class="relative group w-32 aspect-square rounded-md overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 mb-2">
                        <img src="{{ asset('storage/' . $order->payment_proof) }}" class="w-full h-full object-cover">
                        <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[10px] font-bold transition">
                            Lihat Full
                        </a>
                    </div>
                @else
                    <p class="text-xs text-gray-500 italic mb-2">Belum ada bukti pembayaran yang diunggah.</p>
                @endif
            </div>

            <!-- Upload new Proof -->
            <form hx-post="{{ route('orders.update-status', $order) }}" hx-encoding="multipart/form-data" hx-target="#drawer-content" hx-swap="innerHTML" class="pt-3 border-t border-gray-100 dark:border-gray-850">
                @csrf
                @method('PATCH')
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ $order->payment_proof ? 'Ganti Bukti Transfer' : 'Unggah Bukti Transfer' }}</label>
                        <input type="file" name="payment_proof" accept="image/*" required class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-500/10 dark:file:text-primary-400 transition cursor-pointer">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-3 py-1 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300 rounded text-xs font-bold transition">
                            Upload Bukti
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Timeline History -->
    <div>
        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3">Riwayat & Log Status</h4>
        <div class="bg-white dark:bg-gray-900 rounded-md border border-gray-200 dark:border-gray-800 p-4">
            <ol class="relative border-l border-gray-200 dark:border-gray-800 space-y-4 ml-2">
                <!-- Created -->
                <li class="ml-4">
                    <span class="absolute -left-[4.5px] mt-1.5 w-2.5 h-2.5 bg-gray-400 dark:bg-gray-700 rounded-full"></span>
                    <time class="text-[10px] text-gray-400 font-semibold">{{ $order->created_at->format('d M Y, H:i') }}</time>
                    <p class="text-xs font-semibold text-gray-900 dark:text-white mt-0.5">Pesanan Dibuat</p>
                    <span class="text-[10px] text-gray-500">Pelanggan membuat pesanan via {{ $order->order_method }}</span>
                </li>

                <!-- Confirmed -->
                @if($order->confirmed_at)
                <li class="ml-4">
                    <span class="absolute -left-[4.5px] mt-1.5 w-2.5 h-2.5 bg-blue-500 rounded-full"></span>
                    <time class="text-[10px] text-gray-400 font-semibold">{{ $order->confirmed_at->format('d M Y, H:i') }}</time>
                    <p class="text-xs font-semibold text-gray-900 dark:text-white mt-0.5">Pesanan Dikonfirmasi</p>
                    <span class="text-[10px] text-gray-500 font-medium">Pembayaran dikonfirmasi & pesanan disetujui</span>
                </li>
                @endif

                <!-- Shipped -->
                @if($order->shipped_at)
                <li class="ml-4">
                    <span class="absolute -left-[4.5px] mt-1.5 w-2.5 h-2.5 bg-cyan-500 rounded-full"></span>
                    <time class="text-[10px] text-gray-400 font-semibold">{{ $order->shipped_at->format('d M Y, H:i') }}</time>
                    <p class="text-xs font-semibold text-gray-900 dark:text-white mt-0.5">Pesanan Dikirim</p>
                    <span class="text-[10px] text-gray-500">Barang telah diserahkan ke kurir pengiriman</span>
                </li>
                @endif

                @if($order->tracking_number)
                <li class="ml-4">
                    <span class="absolute -left-[4.5px] mt-1.5 w-2.5 h-2.5 bg-cyan-500 rounded-full"></span>
                    <time class="text-[10px] text-gray-400 font-semibold">Nomor Resi</time>
                    <p class="text-xs font-semibold text-gray-900 dark:text-white mt-0.5 font-mono">{{ $order->tracking_number }}</p>
                    <span class="text-[10px] text-cyan-600 dark:text-cyan-400">Nomor resi pengiriman telah dicatat</span>
                </li>
                @endif

                @if($order->tracking_number)
                <li class="ml-4">
                    <span class="absolute -left-[4.5px] mt-1.5 w-2.5 h-2.5 bg-cyan-500 rounded-full"></span>
                    <time class="text-[10px] text-gray-400 font-semibold">Nomor Resi</time>
                    <p class="text-xs font-semibold text-gray-900 dark:text-white mt-0.5">{{ $order->tracking_number }}</p>
                    <span class="text-[10px] text-cyan-600 dark:text-cyan-400">Nomor resi pengiriman telah dicatat</span>
                </li>
                @endif

                <!-- Completed -->
                @if($order->completed_at)
                <li class="ml-4">
                    <span class="absolute -left-[4.5px] mt-1.5 w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                    <time class="text-[10px] text-gray-400 font-semibold">{{ $order->completed_at->format('d M Y, H:i') }}</time>
                    <p class="text-xs font-semibold text-gray-900 dark:text-white mt-0.5">Pesanan Selesai</p>
                    <span class="text-[10px] text-gray-500">Pelanggan telah menerima barang dan transaksi selesai</span>
                </li>
                @endif

                <!-- Cancelled -->
                @if($order->cancelled_at)
                <li class="ml-4 border-l-2 border-red-500 pl-2">
                    <span class="absolute -left-[4.5px] mt-1.5 w-2.5 h-2.5 bg-red-500 rounded-full"></span>
                    <time class="text-[10px] text-red-500 font-semibold">{{ $order->cancelled_at->format('d M Y, H:i') }}</time>
                    <p class="text-xs font-bold text-red-600 dark:text-red-400 mt-0.5">Pesanan Dibatalkan</p>
                    @if($order->cancellation_reason)
                        <p class="text-[10px] text-red-500 bg-red-50 dark:bg-red-950/20 p-2 rounded border border-red-100 dark:border-red-900/50 mt-1">Alasan: {{ $order->cancellation_reason }}</p>
                    @endif
                </li>
                @endif
            </ol>
        </div>
    </div>

    <!-- Permanent Delete Action -->
    <div class="pt-6 border-t border-dashed border-gray-200 dark:border-gray-800">
        <div class="flex justify-between items-center bg-red-50 dark:bg-red-950/10 border border-red-100 dark:border-red-900/30 p-4 rounded-md">
            <div>
                <h5 class="text-xs font-bold text-red-700 dark:text-red-400 uppercase tracking-wide">Zona Bahaya</h5>
                <p class="text-[10px] text-red-600/80 dark:text-red-400/60 mt-0.5">Menghapus pesanan ini akan menyembunyikannya dari sistem (Soft Delete).</p>
            </div>
            <button type="button"
                    hx-delete="{{ route('orders.destroy', $order) }}"
                    hx-confirm="Hapus pesanan {{ $order->order_number }} secara permanen?"
                    hx-target="#drawer-content"
                    class="px-3.5 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-bold transition shadow-sm">
                Hapus
            </button>
        </div>
    </div>
</div>
