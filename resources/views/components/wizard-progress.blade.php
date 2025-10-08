@props([
    'steps' => [],
    'currentStep' => 1
])

<div class="mb-8">
    <div class="flex items-center justify-between">
        @foreach($steps as $index => $step)
            @php
                $stepNumber = $index + 1;
                $isActive = $stepNumber == $currentStep;
                $isCompleted = $stepNumber < $currentStep;
            @endphp
            
            <div class="flex items-center {{ $index < count($steps) - 1 ? 'flex-1' : '' }}">
                <!-- Step Circle -->
                <div class="relative flex flex-col items-center">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-300 {{ $isActive ? 'border-green-600 bg-green-600 text-white' : ($isCompleted ? 'border-green-600 bg-green-600 text-white' : 'border-gray-300 bg-white text-gray-500') }}">
                        @if($isCompleted)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @else
                            <span class="text-sm font-semibold">{{ $stepNumber }}</span>
                        @endif
                    </div>
                    <div class="mt-2 text-center">
                        <p class="text-xs sm:text-sm font-medium {{ $isActive ? 'text-green-600' : 'text-gray-500' }}">
                            {{ $step['title'] ?? 'Step ' . $stepNumber }}
                        </p>
                    </div>
                </div>
                
                <!-- Connector Line -->
                @if($index < count($steps) - 1)
                    <div class="flex-1 h-0.5 mx-2 {{ $stepNumber < $currentStep ? 'bg-green-600' : 'bg-gray-300' }} transition-all duration-300"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>
