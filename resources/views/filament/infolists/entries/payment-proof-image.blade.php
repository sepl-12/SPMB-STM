@php
    $record = $getRecord();
    $imageUrl = $record->getProofImageUrl();
@endphp

<div class="w-full">
    @if($imageUrl)
        <div class="relative group">
            <a href="{{ $imageUrl }}" target="_blank" class="block">
                <img
                    src="{{ $imageUrl }}"
                    alt="Bukti Pembayaran"
                    class="w-full h-auto rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                    onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect fill=%22%23f3f4f6%22 width=%22400%22 height=%22300%22/%3E%3Ctext fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2218%22 dy=%2210.5%22 font-weight=%22bold%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22%3EGambar tidak dapat dimuat%3C/text%3E%3C/svg%3E';"
                />
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-200 rounded-lg flex items-center justify-center">
                    <svg class="w-16 h-16 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                    </svg>
                </div>
            </a>
        </div>
        <p class="text-sm text-gray-500 mt-2 text-center">
            Klik gambar untuk melihat ukuran penuh
        </p>
    @else
        <div class="w-full bg-gray-100 rounded-lg p-8 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <p class="text-gray-500">Tidak ada bukti pembayaran</p>
        </div>
    @endif
</div>
