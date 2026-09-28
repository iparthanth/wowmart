<?php

namespace App\Http\Requests\Admin;

use App\Models\ContactMessage;
use App\Models\ProductConversation;
use App\Models\SupportTicket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * One request for every support write:
 * - ticket reply, close, reopen and create-on-behalf (SupportService)
 * - contact reply
 * - product-query reply
 * - conversation close/reopen
 */
class SupportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return match ($this->route()?->getName()) {
            'admin.support.tickets.store', 'api.v1.tickets.store' => [
                'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
                'subject' => ['required', 'string', 'max:180'],
                'message' => ['required', 'string', 'max:5000'],
            ],
            'admin.support.tickets.reply', 'api.v1.tickets.reply',
            'admin.support.conversations.reply', 'api.v1.conversations.reply' => [
                'message' => ['required', 'string', 'max:5000'],
            ],
            'admin.support.tickets.status', 'admin.support.conversations.status' => [
                'status' => ['required', Rule::in(SupportTicket::STATUSES)],
            ],
            'admin.support.contacts.reply' => [
                'reply' => ['required', 'string', 'max:5000'],
            ],
            'admin.support.queries.reply' => [
                'reply' => ['required', 'string', 'max:5000'],
            ],
            'api.v1.queries.update' => [
                'reply' => ['required', 'string', 'max:5000'],
            ],
            'api.v1.tickets.status' => [
                'status' => ['required', Rule::in(SupportTicket::STATUSES)],
            ],
            'api.v1.conversations.status' => [
                'status' => ['required', Rule::in(ProductConversation::STATUSES)],
            ],
            'api.v1.contacts.update' => [
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:40'],
                'query' => ['required', 'string', 'max:5000'],
                'reply' => ['nullable', 'string', 'max:5000'],
                'status' => ['nullable', Rule::in(ContactMessage::STATUSES)],
            ],
            'api.v1.tickets.update' => [
                'subject' => ['sometimes', 'string', 'max:180'],
                'status' => ['nullable', Rule::in(SupportTicket::STATUSES)],
            ],
            'api.v1.conversations.update' => [
                'title' => ['sometimes', 'string', 'max:180'],
                'status' => ['nullable', Rule::in(ProductConversation::STATUSES)],
            ],
            default => [],
        };
    }
}
