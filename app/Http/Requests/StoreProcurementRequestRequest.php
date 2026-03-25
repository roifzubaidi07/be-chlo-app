<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProcurementRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'submit' => ['sometimes', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', Rule::exists('items', 'id')->whereNull('deleted_at')],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.code' => ['sometimes', 'string', 'max:50'],
            'items.*.discount' => ['sometimes', 'numeric', 'min:0'],
            'items.*.tax' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
