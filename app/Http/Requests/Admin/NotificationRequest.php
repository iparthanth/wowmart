<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** One request for the notification screens: settings, types, custom sends, bulk SMS. */
class NotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return match ($this->route()?->getName()) {
            'admin.notifications.settings.save', 'api.v1.notifications.settings.update' => [
                'types' => ['nullable', 'array'],
                'types.*' => ['string', 'max:40'],
                'notification_show_type' => ['required', 'in:text,design1,design2,design3'],
            ],
            'admin.notifications.types.store' => [
                'name' => ['required', 'string', 'max:60', 'unique:notification_types,name'],
                'image' => ['nullable', 'integer'],
                'default_text' => ['required', 'string', 'max:80'],
                'status' => ['nullable', 'in:0,1'],
            ],
            'admin.notifications.types.update' => [
                'name' => ['required', 'string', 'max:60', 'unique:notification_types,name,'.($this->route('type')?->getKey() ?? '')],
                'image' => ['nullable', 'integer'],
                'default_text' => ['required', 'string', 'max:80'],
                'status' => ['nullable', 'in:0,1'],
            ],
            'admin.notifications.custom.store', 'api.v1.notifications.custom.store' => [
                'notification_type_id' => ['required', 'integer'],
                'content' => ['required', 'string', 'max:80'],
                'link' => ['nullable', 'string', 'max:255'],
                'user_ids' => ['required', 'array', 'min:1'],
                'user_ids.*' => ['integer'],
            ],
            'api.v1.notifications.types.store' => [
                'name' => ['required', 'string', 'max:60', 'unique:notification_types,name'],
                'image' => ['nullable', 'integer'],
                'default_text' => ['required', 'string', 'max:80'],
                'status' => ['nullable', 'boolean'],
            ],
            'api.v1.notifications.types.update' => [
                'name' => ['sometimes', 'string', 'max:60'],
                'image' => ['nullable', 'integer'],
                'default_text' => ['sometimes', 'string', 'max:80'],
                'status' => ['nullable', 'boolean'],
            ],
            'admin.bulk-sms.send', 'api.v1.bulk-sms.store' => [
                'user_phones' => ['required', 'array', 'min:1'],
                'user_phones.*' => ['string', 'max:32'],
                'content' => ['required', 'string', 'max:500'],
                'template_id' => ['nullable', 'string', 'max:60'],
            ],
            default => [],
        };
    }

    protected function prepareForValidation(): void
    {
        if (str_contains((string) $this->route()?->getName(), 'bulk-sms') && $this->has('user_phones')) {
            // A pasted multiline textarea splits into one phone per line.
            $phones = is_array($this->user_phones) ? $this->user_phones : preg_split('/[\r\n,;]+/', (string) $this->user_phones);
            $this->merge(['user_phones' => $phones]);
        }
    }
}
