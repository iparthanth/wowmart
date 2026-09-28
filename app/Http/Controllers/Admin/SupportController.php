<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupportRequest;
use App\Models\ContactMessage;
use App\Models\ProductConversation;
use App\Models\ProductQuery;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function __construct(private readonly SupportService $support) {}

    // -------------------------------------------------------------- tickets

    public function tickets(): View
    {
        return view('admin.support.tickets', [
            'tickets' => SupportTicket::query()
                ->with('user')
                ->when(request('status'), fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'newTicket' => new SupportTicket,
            'customers' => User::where('user_type', 'customer')->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function storeTicket(SupportRequest $request): RedirectResponse
    {
        $ticket = $this->support->createTicket($request->validated());

        return redirect()->route('admin.support.tickets.show', $ticket)
            ->with('status', "Ticket {$ticket->code} opened for {$ticket->user->name}.");
    }

    public function showTicket(SupportTicket $ticket): View
    {
        return view('admin.support.ticket', [
            'ticket' => $ticket->load(['user', 'replies.user']),
        ]);
    }

    public function replyTicket(SupportRequest $request, SupportTicket $ticket): RedirectResponse
    {
        try {
            $this->support->replyToTicket($ticket, $request->user(), $request->validated('message'));
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Reply posted.');
    }

    public function ticketStatus(SupportRequest $request, SupportTicket $ticket): RedirectResponse
    {
        $this->support->setTicketStatus($ticket, $request->validated('status'));

        return back()->with('status', $ticket->status === 'closed' ? 'Ticket closed.' : 'Ticket reopened.');
    }

    // ------------------------------------------------------------- contacts

    public function contacts(): View
    {
        return view('admin.support.contacts', [
            'contacts' => ContactMessage::query()
                ->when(request('status'), fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function replyContact(SupportRequest $request, ContactMessage $contact): RedirectResponse
    {
        $this->support->replyToContact($contact, $request->validated('reply'));

        return back()->with('status', "Reply recorded for {$contact->name}.");
    }

    public function destroyContact(ContactMessage $contact): RedirectResponse
    {
        $contact->delete();

        return back()->with('status', 'Message deleted.');
    }

    // -------------------------------------------------------------- queries

    public function queries(): View
    {
        return view('admin.support.queries', [
            'queries' => ProductQuery::query()
                ->with(['user', 'product'])
                ->when(request('status'), fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function replyQuery(SupportRequest $request, ProductQuery $query): RedirectResponse
    {
        $this->support->replyToQuery($query, $request->validated('reply'));

        return back()->with('status', 'Answer published with the product.');
    }

    public function destroyQuery(ProductQuery $query): RedirectResponse
    {
        $query->delete();

        return back()->with('status', 'Question deleted.');
    }

    // -------------------------------------------------------- conversations

    public function conversations(): View
    {
        return view('admin.support.conversations', [
            'conversations' => ProductConversation::query()
                ->with(['product', 'customer', 'seller'])
                ->when(request('status'), fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function showConversation(ProductConversation $conversation): View
    {
        return view('admin.support.conversation', [
            'conversation' => $conversation->load(['product', 'customer', 'seller', 'messages.user']),
        ]);
    }

    public function replyConversation(SupportRequest $request, ProductConversation $conversation): RedirectResponse
    {
        try {
            $this->support->replyToConversation($conversation, $request->user(), $request->validated('message'));
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Message posted.');
    }

    public function conversationStatus(SupportRequest $request, ProductConversation $conversation): RedirectResponse
    {
        $this->support->setConversationStatus($conversation, $request->validated('status'));

        return back()->with('status', $conversation->status === 'closed' ? 'Conversation closed.' : 'Conversation reopened.');
    }
}
