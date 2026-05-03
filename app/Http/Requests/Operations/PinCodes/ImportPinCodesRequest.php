<?php

namespace App\Http\Requests\Operations\PinCodes;

use App\Models\PinCode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportPinCodesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('import', PinCode::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ];
    }
}
