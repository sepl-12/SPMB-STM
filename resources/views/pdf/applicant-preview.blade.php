<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Data Pendaftaran - {{ $applicant->registration_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #16a34a;
        }

        .header h1 {
            font-size: 18pt;
            color: #16a34a;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 14pt;
            color: #555;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 9pt;
            color: #666;
        }

        .applicant-info {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 5px;
            border-left: 4px solid #16a34a;
        }

        .applicant-info table {
            width: 100%;
        }

        .applicant-info td {
            padding: 4px 0;
        }

        .applicant-info td:first-child {
            width: 40%;
            font-weight: bold;
            color: #555;
        }

        .step-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .step-header {
            background: #16a34a;
            color: white;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 12pt;
        }

        .step-header .step-number {
            display: inline-block;
            width: 25px;
            height: 25px;
            background: white;
            color: #16a34a;
            text-align: center;
            line-height: 25px;
            border-radius: 50%;
            margin-right: 10px;
            font-weight: bold;
        }

        .step-description {
            font-size: 9pt;
            color: #666;
            margin-left: 35px;
            margin-top: 5px;
            font-style: italic;
        }

        .field-row {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .field-label {
            display: table-cell;
            width: 40%;
            font-weight: bold;
            color: #555;
            padding-right: 15px;
            vertical-align: top;
            font-size: 9.5pt;
        }

        .field-value {
            display: table-cell;
            width: 60%;
            color: #333;
            vertical-align: top;
            font-size: 9.5pt;
        }

        .field-value ul {
            margin: 0;
            padding-left: 20px;
        }

        .field-value li {
            margin-bottom: 3px;
        }

        .required-indicator {
            color: #dc2626;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        .badge-green {
            background: #dcfce7;
            color: #166534;
        }

        .badge-gray {
            background: #f3f4f6;
            color: #4b5563;
        }

        .empty-value {
            color: #9ca3af;
            font-style: italic;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }

        .page-number {
            text-align: right;
            font-size: 8pt;
            color: #999;
            margin-top: 20px;
        }

        /* Image handling */
        img {
            max-width: 200px;
            max-height: 150px;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 5px;
        }

        /* File display */
        .file-info {
            background: #f9fafb;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            display: inline-block;
        }

        .file-icon {
            display: inline-block;
            width: 20px;
            height: 20px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ config('app.name', 'PPDB SMK') }}</h1>
        <h2>Preview Data Pendaftaran</h2>
        <p>{{ $formTitle }}</p>
    </div>

    <!-- Applicant Information -->
    <div class="applicant-info">
        <table>
            <tr>
                <td>Nomor Pendaftaran</td>
                <td>: <strong>{{ $applicant->registration_number }}</strong></td>
            </tr>
            <tr>
                <td>Nama Lengkap</td>
                <td>: {{ $applicant->applicant_full_name }}</td>
            </tr>
            <tr>
                <td>Gelombang</td>
                <td>: {{ $applicant->wave->wave_name }}</td>
            </tr>
            @if($applicant->chosen_major_name)
            <tr>
                <td>Jurusan Pilihan</td>
                <td>: {{ $applicant->chosen_major_name }}</td>
            </tr>
            @endif
            <tr>
                <td>Tanggal Daftar</td>
                <td>: {{ $applicant->registered_datetime->format('d F Y, H:i') }} WIB</td>
            </tr>
            <tr>
                <td>Status Pembayaran</td>
                <td>:
                    @if($applicant->latestPayment)
                        <span class="badge badge-{{ $applicant->latestPayment->payment_status_name->value === 'settlement' ? 'green' : 'gray' }}">
                            {{ $applicant->latestPayment->payment_status_name->label() }}
                        </span>
                    @else
                        <span class="badge badge-gray">Belum Bayar</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Preview Data by Steps -->
    @foreach($previewData as $stepData)
    <div class="step-section">
        <div class="step-header">
            <span class="step-number">{{ $stepData['step_order'] }}</span>
            {{ $stepData['step_title'] }}
        </div>

        @if(!empty($stepData['step_description']))
        <div class="step-description">
            {{ $stepData['step_description'] }}
        </div>
        @endif

        <div style="margin-left: 10px; margin-top: 10px;">
            @foreach($stepData['fields'] as $field)
            <div class="field-row">
                <div class="field-label">
                    {{ $field['field_label'] }}
                    @if($field['is_required'])
                        <span class="required-indicator">*</span>
                    @endif
                </div>
                <div class="field-value">
                    {!! $field['formatted_value'] !!}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <!-- Footer -->
    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis pada {{ now()->format('d F Y, H:i') }} WIB</p>
        <p>{{ config('app.name', 'PPDB SMK') }} - Sistem Penerimaan Peserta Didik Baru Online</p>
    </div>
</body>
</html>
