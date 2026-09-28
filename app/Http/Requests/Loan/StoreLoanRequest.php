<?php

namespace App\Http\Requests\Loan;

use App\Models\Loan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Loan::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'book_id' => ['required', 'exists:books,id'],
            'loan_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:loan_date'],
        ];

        if ($this->user()?->isPustakawan()) {
            $rules['user_id'] = ['nullable', 'exists:users,id'];
            $rules['nis_nip'] = ['nullable', 'string', 'exists:users,nis_nip'];
        }

        return $rules;
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'book_id' => 'Buku',
            'user_id' => 'Peminjam',
            'nis_nip' => 'NIS Siswa',
            'loan_date' => 'Tanggal Pinjam',
            'due_date' => 'Tanggal Jatuh Tempo',
        ];
    }
}
