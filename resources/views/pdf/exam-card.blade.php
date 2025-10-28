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
            left: 34mm;
            bottom: 53mm;
            width: 34mm;
            height: 44mm;
            object-fit: cover;
            border-radius: 4mm;
        }
    </style>
</head>
<body>
    @php
        $birthDateText = $birth_date?->translatedFormat('d F Y');
        $examDateText = $exam_date?->translatedFormat('d F Y');
        $waCombined = collect([$whatsapp_parent, $whatsapp_student])->filter()->implode(' / ');
        $signatureDateText = $signature_day_month;
        $photoSrc = $photo_path ? 'file://' . $photo_path : null;
        $birthLine = collect([$birth_place, $birthDateText])->filter()->implode(', ');
    @endphp

    <div class="page">
        <div class="layer">
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

            @if($photoSrc)
                <img src="{{ $photoSrc }}" alt="Foto Peserta" class="photo">
            @else
                <div class="field signature" style="--left: 13mm; --top: 240mm; --width: 60mm; text-align: center;">
                {{ 'Foto' }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>
