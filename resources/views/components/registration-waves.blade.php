<!-- Informasi Gelombang Pendaftaran Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                Informasi Gelombang Pendaftaran
            </h2>
            <p class="text-lg text-gray-600">
                Berikut adalah jadwal pendaftaran siswa baru untuk tahun ajaran 2025/2026.
            </p>
        </div>
        
        @if($waves->isEmpty())
            <!-- No waves available -->
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-gray-500 text-lg">Informasi gelombang pendaftaran akan segera diumumkan.</p>
            </div>
        @else
            <!-- Timeline Progress Bar -->
            <div class="relative mb-12 hidden md:block">
                <div class="absolute top-1/2 left-0 right-0 h-0.5 bg-gray-300 -translate-y-1/2"></div>
                <div class="relative flex justify-between items-center max-w-4xl mx-auto">
                    @foreach($waves as $wave)
                        @php
                            $now = now();
                            // Check is_active field first, then check datetime
                            $isActive = $wave->is_active && $now->gte($wave->start_datetime) && $now->lte($wave->end_datetime);
                            $isClosed = !$wave->is_active || $now->gt($wave->end_datetime);
                            $isUpcoming = $wave->is_active && $now->lt($wave->start_datetime);
                            
                            if ($isClosed) {
                                $dotColor = 'bg-gray-400';
                            } elseif ($isActive) {
                                $dotColor = 'bg-green-500';
                            } else {
                                $dotColor = 'bg-blue-500';
                            }
                        @endphp
                        <div class="w-4 h-4 {{ $dotColor }} rounded-full ring-4 ring-white"></div>
                    @endforeach
                </div>
            </div>
            
            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-{{ min(count($waves), 3) }} gap-6 lg:gap-8">
                @foreach($waves as $wave)
                    @php
                        $now = now();
                        // Prioritize is_active field
                        // Active: is_active = true AND within date range
                        $isActive = $wave->is_active && $now->gte($wave->start_datetime) && $now->lte($wave->end_datetime);
                        // Closed: is_active = false OR past end_datetime
                        $isClosed = !$wave->is_active || $now->gt($wave->end_datetime);
                        // Upcoming: is_active = true AND before start_datetime
                        $isUpcoming = $wave->is_active && $now->lt($wave->start_datetime);
                        
                        // Calculate remaining slots
                        $registeredCount = $wave->applicants()->count();
                        $remainingSlots = $wave->quota_limit ? ($wave->quota_limit - $registeredCount) : null;
                    @endphp
                    
                    <div class="bg-white rounded-2xl p-8 shadow-sm border {{ $isActive ? 'border-2 border-green-500 shadow-lg' : 'border-gray-200' }} hover:shadow-lg transition-all duration-300 relative">
                        <!-- Active Badge -->
                        @if($isActive)
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                <span class="inline-flex items-center px-4 py-1 bg-green-500 text-white font-semibold rounded-full text-xs">
                                    Sedang Berlangsung
                                </span>
                            </div>
                        @endif
                        
                        <!-- Icon -->
                        <div class="w-16 h-16 {{ $isClosed ? 'bg-gray-100' : ($isActive ? 'bg-green-100' : 'bg-blue-100') }} rounded-full flex items-center justify-center mb-6 mx-auto">
                            @if($isClosed)
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            @elseif($isActive)
                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @else
                                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @endif
                        </div>
                        
                        <!-- Wave Name -->
                        <h3 class="text-2xl font-bold text-gray-900 text-center mb-3">{{ $wave->wave_name }}</h3>
                        
                        <!-- Date Range -->
                        <p class="text-gray-600 text-center mb-4">
                            {{ $wave->start_datetime->format('d M') }} - {{ $wave->end_datetime->format('d M Y') }}
                        </p>
                        
                        <!-- Registration Fee -->
                        <div class="text-center mb-4">
                            <p class="text-sm text-gray-500 mb-1">Biaya Pendaftaran</p>
                            <p class="text-2xl font-bold text-gray-900">
                                Rp {{ number_format($wave->registration_fee_amount, 0, ',', '.') }}
                            </p>
                        </div>
                        
                        <!-- Quota Info -->
                        @if($wave->quota_limit)
                            <div class="mb-6 bg-gray-50 rounded-lg p-3">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">Kuota Tersisa</span>
                                    <span class="text-sm font-semibold {{ $remainingSlots <= 10 ? 'text-red-500' : 'text-gray-900' }}">
                                        {{ max(0, $remainingSlots) }} / {{ $wave->quota_limit }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ $wave->quota_limit > 0 ? (($registeredCount / $wave->quota_limit) * 100) : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Status Badge -->
                        <div class="flex justify-center">
                            @if($isClosed)
                                <span class="inline-flex items-center px-6 py-2 bg-gray-100 text-gray-600 font-semibold rounded-full text-sm">
                                    Selesai
                                </span>
                            @elseif($isActive)
                                <span class="inline-flex items-center px-6 py-2 bg-green-500 text-white font-semibold rounded-full text-sm">
                                    Dibuka
                                </span>
                            @else
                                <span class="inline-flex items-center px-6 py-2 border-2 border-blue-500 text-blue-500 font-semibold rounded-full text-sm">
                                    Akan Datang
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
