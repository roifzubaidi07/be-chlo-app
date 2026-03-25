<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectProcurementRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reason' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
