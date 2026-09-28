<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\ProductConversation;
use App\Models\ProductQuery;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Every support write. Input is validated already (SupportRequest). */
class SupportService
{
    /** The admin opens a ticket on a customer's behalf. */
    public function createTicket(array $data): SupportTicket
    {
        return DB::transaction(function () use ($data): SupportTicket {
            $ticket = SupportTicket::create([
                'user_id' => $data['user_id'],
                'subject' => $data['subject'],
            ]);
            $ticket->replies()->create([
                'user_id' => $data['user_id'],
                'message' => $data['message'],
            ]);

            return $ticket;
        });
    }

    /** @throws DomainException when the ticket is closed */
    public function replyToTicket(SupportTicket $ticket, User $admin, string $message): SupportTicketReply
    {
        if ($ticket->status === 'closed') {
            throw new DomainException('This ticket is closed. Reopen it before replying.');
        }

        return DB::transaction(function () use ($ticket, $admin, $message): SupportTicketReply {
            $reply = $ticket->replies()->create(['user_id' => $admin->id, 'message' => $message]);

            return $reply;
        });
    }

    public function setTicketStatus(SupportTicket $ticket, string $status): SupportTicket
    {
        return DB::transaction(fn () => tap($ticket)->update(['status' => $status]));
    }

    /** @throws DomainException when the conversation is closed */
    public function replyToConversation(ProductConversation $conversation, User $admin, string $message): void
    {
        if ($conversation->status === 'closed') {
            throw new DomainException('This conversation is closed. Reopen it before replying.');
        }

        $conversation->messages()->create(['user_id' => $admin->id, 'message' => $message]);
    }

    public function setConversationStatus(ProductConversation $conversation, string $status): ProductConversation
    {
        return DB::transaction(fn () => tap($conversation)->update(['status' => $status]));
    }

    /** Replying to a contact marks it answered. */
    public function replyToContact(ContactMessage $contact, string $reply): ContactMessage
    {
        return DB::transaction(fn () => tap($contact)->update(['reply' => $reply, 'status' => 'answered']));
    }

    /** Replying to a product query marks it answered. */
    public function replyToQuery(ProductQuery $query, string $reply): ProductQuery
    {
        return DB::transaction(fn () => tap($query)->update(['reply' => $reply, 'status' => 'answered']));
    }
}
