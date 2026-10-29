<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Src\Auth\Application\IssueToken\IssueTokenCommand;

final class IssueTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'device_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function toCommand(): IssueTokenCommand
    {
        return new IssueTokenCommand(
            email: $this->string('email')->toString(),
            password: $this->string('password')->toString(),
            deviceName: $this->string('device_name')->toString(),
        );
    }
}
