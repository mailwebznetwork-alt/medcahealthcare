<?php

namespace App\Http\Requests\Settings;

use App\Support\BackupOperator;
use Illuminate\Foundation\Http\FormRequest;

class RestoreDatabaseBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return BackupOperator::allows($this->user());
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'backup_file' => ['required', 'file'],
        ];
    }
}
