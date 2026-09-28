<?php

namespace App\Http\Requests\Visit;

use App\Models\Visit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVisitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Visit::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nis_nip' => ['nullable', 'string', 'exists:users,nis_nip'],
            'purpose' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'nis_nip' => 'NIS / NIP Siswa',
            'purpose' => 'Tujuan Kunjungan',
            'notes' => 'Catatan',
        ];
    }
}
