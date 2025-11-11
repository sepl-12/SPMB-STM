<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadManualPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow public access (payment page is public)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'proof_image' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png',
                'max:2048', // 2MB
            ],
            'paid_amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'payment_notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'proof_image.required' => 'Bukti pembayaran wajib diupload.',
            'proof_image.image' => 'File harus berupa gambar.',
            'proof_image.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',
            'proof_image.max' => 'Ukuran gambar maksimal 2MB.',
            'paid_amount.required' => 'Jumlah pembayaran wajib diisi.',
            'paid_amount.numeric' => 'Jumlah pembayaran harus berupa angka.',
            'paid_amount.min' => 'Jumlah pembayaran tidak valid.',
            'payment_notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'proof_image' => 'Bukti Pembayaran',
            'paid_amount' => 'Jumlah Pembayaran',
            'payment_notes' => 'Catatan',
        ];
    }
}
