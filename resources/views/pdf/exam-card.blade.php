<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
        }

        .page::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("{{ public_path('images/exam-card/kartutes-02.png') }}") center center / cover no-repeat;
            z-index: 0;
        }

        .layer {
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        .field {
            position: absolute;
            left: var(--left);
            top: var(--top);
            width: var(--width, auto);
            font-weight: 600;
            font-size: 12.5pt;
            color: #0f172a;
        }

        .field.small {
            font-size: 11.5pt;
        }

        .signature {
            font-size: 11.5pt;
        }

        .photo {
            position: absolute;
            object-fit: cover;
        }

        .signature-image {
            position: absolute;
            object-fit: contain;
        }
    </style>
</head>
<body>
    @php
        // Helper function untuk format nilai field
        $formatFieldValue = function($field) use ($birth_place, $birth_date, $whatsapp_parent, $whatsapp_student, $exam_date, $signature_day_month) {
            $value = $field['value'];
            $config = $field['config'];

            // Special formatting untuk field tertentu
            switch ($config->field_key) {
                case 'birth_place':
                    // Combine birth_place dan birth_date
                    $birthDateText = $birth_date?->translatedFormat('d F Y');
                    $birthLine = collect([$birth_place, $birthDateText])->filter()->implode(', ');
                    return $birthLine !== '' ? $birthLine : ($config->fallback_value ?? '-');

                case 'birth_date':
                    // Skip, sudah digabung dengan birth_place
                    return null;

                case 'whatsapp_parent':
                    // Combine parent and student whatsapp
                    $waCombined = collect([$whatsapp_parent, $whatsapp_student])->filter()->implode(' / ');
                    return $waCombined ?: ($config->fallback_value ?? '-');

                case 'whatsapp_student':
                    // Skip, sudah digabung dengan whatsapp_parent
                    return null;

                case 'exam_date':
                    return $value?->translatedFormat('d F Y') ?? ($config->fallback_value ?? '-');

                case 'signature_date':
                    return $value ?? $signature_day_month ?? ($config->fallback_value ?? '-');

                default:
                    // Default formatting
                    if ($value instanceof \Carbon\Carbon) {
                        return $value->translatedFormat('d F Y');
                    }
                    return $value ?? ($config->fallback_value ?? '-');
            }
        };

        // Prepare photo and signature
        $photoSrc = $photo_path ? 'file://' . $photo_path : null;
        $signatureSrc = $signature_image_path ? 'file://' . $signature_image_path : null;
    @endphp

    <div class="page">
        <div class="layer">
            @if(isset($fields) && !empty($fields))
                {{-- Dynamic field rendering berdasarkan konfigurasi --}}
                @foreach($fields as $fieldKey => $field)
                    @php
                        $config = $field['config'];
                        $displayValue = $formatFieldValue($field);
                    @endphp

                    @if($displayValue !== null)
                        @if($config->field_type === 'text')
                            <div class="field @if($config->font_size < 12) small @endif" style="{{ $config->getCssPosition() }}">
                                {{ $displayValue }}
                            </div>
                        @elseif($config->field_type === 'image' && $config->field_key === 'photo')
                            @if($photoSrc)
                                <img src="{{ $photoSrc }}" alt="Foto Peserta" class="photo" style="left: {{ $config->position_left }}mm; top: {{ $config->position_top }}mm; width: {{ $config->width }}mm; height: {{ $config->height }}mm;">
                            @else
                                <div class="field signature" style="left: {{ $config->position_left }}mm; top: {{ $config->position_top + 27 }}mm; width: {{ $config->width }}mm; text-align: center;">
                                    Foto
                                </div>
                            @endif
                        @elseif($config->field_type === 'signature' && $config->field_key === 'signature_image')
                            @if($signatureSrc)
                                <img src="{{ $signatureSrc }}" alt="Tanda Tangan Peserta" class="signature-image" style="left: {{ $config->position_left }}mm; top: {{ $config->position_top }}mm; width: {{ $config->width }}mm; height: {{ $config->height }}mm;">
                            @endif
                        @endif
                    @endif
                @endforeach
            @else
                {{-- Fallback ke format lama jika fields tidak tersedia --}}
                @php
                    $birthDateText = $birth_date?->translatedFormat('d F Y');
                    $examDateText = $exam_date?->translatedFormat('d F Y');
                    $waCombined = collect([$whatsapp_parent, $whatsapp_student])->filter()->implode(' / ');
                    $signatureDateText = $signature_day_month;
                    $birthLine = collect([$birth_place, $birthDateText])->filter()->implode(', ');
                @endphp

                <div class="field" style="--left: 70mm; --top: 82mm; --width: 82mm;">
                    {{ $registration_number ?? '-' }}
                </div>
                <div class="field" style="--left: 148mm; --top: 82mm; --width: 58mm;">
                    {{ $nisn ?? '-' }}
                </div>

                <div class="field" style="--left: 80mm; --top: 92mm; --width: 122mm;">
                    {{ $name }}
                </div>
                <div class="field small" style="--left: 80mm; --top: 102mm; --width: 130mm;">
                    {{ $birthLine !== '' ? $birthLine : '-' }}
                </div>
                <div class="field small" style="--left: 80mm; --top: 110mm; --width: 138mm;">
                    {{ $address ?? '-' }}
                </div>

                <div class="field small" style="--left: 80mm; --top: 128mm; --width: 120mm;">
                    {{ $parent_father ?? '-' }}
                </div>
                <div class="field small" style="--left: 80mm; --top: 137mm; --width: 120mm;">
                    {{ $parent_mother ?? '-' }}
                </div>
                <div class="field small" style="--left: 80mm; --top: 147mm; --width: 120mm;">
                    {{ $waCombined ?: '-' }}
                </div>
                <div class="field small" style="--left: 80mm; --top: 157mm; --width: 138mm;">
                    {{ $email ?? '-' }}
                </div>

                <div class="field small" style="--left: 80mm; --top: 167mm; --width: 120mm;">
                    {{ $major_first ?? '-' }}
                </div>
                <div class="field small" style="--left: 80mm; --top: 177mm; --width: 120mm;">
                    {{ $major_second ?? '-' }}
                </div>
                <div class="field small" style="--left: 80mm; --top: 187mm; --width: 120mm;">
                    {{ $major_third ?? '-' }}
                </div>
                <div class="field small" style="--left: 80mm; --top: 197mm; --width: 140mm;">
                    {{ $previous_school ?? '-' }}
                </div>
                <div class="field small" style="--left: 80mm; --top: 205mm; --width: 80mm;">
                    {{ $examDateText ?? '-' }}
                </div>

                <div class="field signature" style="--left: 131mm; --top: 222mm; --width: 68mm;">
                    {{ $signatureDateText }}
                </div>
                <div class="field signature" style="--left: 110mm; --top: 256mm; --width: 60mm; text-align: center;">
                    {{ $signature_name ?? '-' }}
                </div>
                @if($signatureSrc)
                    <img src="{{ $signatureSrc }}" alt="Tanda Tangan Peserta" class="signature-image" style="--left: 110mm; --top: 245mm; --width: 60mm; --height: 25mm;">
                @endif

                @if($photoSrc)
                    <img src="{{ $photoSrc }}" alt="Foto Peserta" class="photo">
                @else
                    <div class="field signature" style="--left: 13mm; --top: 240mm; --width: 60mm; text-align: center;">
                        Foto
                    </div>
                @endif
            @endif
        </div>
    </div>
</body>
</html>
