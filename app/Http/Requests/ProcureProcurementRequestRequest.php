<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcureProcurementRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['required', 'integer', Rule::exists('vendors', 'id')->whereNull('deleted_at')],
            'po_number' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }
}
