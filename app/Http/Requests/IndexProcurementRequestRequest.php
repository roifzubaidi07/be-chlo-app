<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexProcurementRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'string',
                Rule::in([
                    'DRAFT',
                    'SUBMITTED',
                    'APPROVED',
                    'REJECTED',
                    'IN_PROCUREMENT',
                    'COMPLETED',
                    'CANCELLED',
                ]),
            ],
        ];
    }
}
