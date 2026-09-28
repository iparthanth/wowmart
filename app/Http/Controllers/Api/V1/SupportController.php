<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupportRequest;
use App\Http\Resources\V1\ContactMessageResource;
use App\Http\Resources\V1\ProductConversationResource;
use App\Http\Resources\V1\ProductQueryResource;
use App\Http\Resources\V1\SupportTicketResource;
use App\Models\ContactMessage;
use App\Models\ProductConversation;
use App\Models\ProductQuery;
use App\Models\SupportTicket;
use App\Services\SupportService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SupportController extends Controller
{
    public function __construct(private readonly SupportService $support) {}

    // -------------------------------------------------------------- tickets

    public function ticketsIndex(Request $request): AnonymousResourceCollection
    {
        return SupportTicketResource::collection(
            SupportTicket::query()
                ->with('user')
                ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function ticketsStore(SupportRequest $request): SupportTicketResource
    {
        return new SupportTicketResource($this->support->createTicket($request->validated())->load('user')); // 201
    }

    public function ticketsShow(SupportTicket $ticket): SupportTicketResource
    {
        return new SupportTicketResource($ticket->load(['user', 'replies.user']));
    }

    public function ticketsUpdate(SupportRequest $request, SupportTicket $ticket): SupportTicketResource
    {
        if ($request->validated('subject') !== null) {
            $ticket->update(['subject' => $request->validated('subject')]);
        }

        return new SupportTicketResource($this->support->setTicketStatus(
            $ticket,
            $request->validated('status') ?? $ticket->status,
        )->load('user'));
    }

    public function ticketsDestroy(SupportTicket $ticket): Response
    {
        $ticket->delete();

        return response()->noContent();
    }

    // ------------------------------------------------------------- contacts

    public function contactsIndex(Request $request): AnonymousResourceCollection
    {
        return ContactMessageResource::collection(
            ContactMessage::query()
                ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function contactsShow(ContactMessage $contact): ContactMessageResource
    {
        return new ContactMessageResource($contact);
    }

    public function contactsUpdate(SupportRequest $request, ContactMessage $contact): ContactMessageResource
    {
        // A reply always marks the message answered; PUT without one edits the record.
        return new ContactMessageResource($request->validated('reply') !== null
            ? $this->support->replyToContact($contact, $request->validated('reply'))
            : tap($contact)->update($request->validated()));
    }

    public function contactsDestroy(ContactMessage $contact): Response
    {
        $contact->delete();

        return response()->noContent();
    }

    // -------------------------------------------------------------- queries

    public function queriesIndex(Request $request): AnonymousResourceCollection
    {
        return ProductQueryResource::collection(
            ProductQuery::query()
                ->with(['user', 'product'])
                ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function queriesShow(ProductQuery $query): ProductQueryResource
    {
        return new ProductQueryResource($query->load(['user', 'product']));
    }

    public function queriesUpdate(SupportRequest $request, ProductQuery $query): ProductQueryResource
    {
        return new ProductQueryResource($this->support->replyToQuery($query, $request->validated('reply'))->load(['user', 'product']));
    }

    public function queriesDestroy(ProductQuery $query): Response
    {
        $query->delete();

        return response()->noContent();
    }

    // -------------------------------------------------------- conversations

    public function conversationsIndex(Request $request): AnonymousResourceCollection
    {
        return ProductConversationResource::collection(
            ProductConversation::query()
                ->with(['product', 'customer', 'seller'])
                ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function conversationsShow(ProductConversation $conversation): ProductConversationResource
    {
        return new ProductConversationResource($conversation->load(['product', 'customer', 'seller', 'messages.user']));
    }

    public function conversationsUpdate(SupportRequest $request, ProductConversation $conversation): ProductConversationResource
    {
        return new ProductConversationResource($this->support->setConversationStatus(
            $conversation,
            $request->validated('status') ?? $conversation->status,
        )->load(['product', 'customer', 'seller']));
    }

    public function conversationsDestroy(ProductConversation $conversation): Response
    {
        $conversation->delete();

        return response()->noContent();
    }

    /** A store endpoint for contact messages coming from outside the panel. */
    public function contactsStore(Request $request): ContactMessageResource
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'query' => ['required', 'string', 'max:5000'],
        ]);

        return new ContactMessageResource(ContactMessage::create($data)); // 201
    }

    /** PUT /contacts/{contact}/reply is not needed: contactsUpdate handles replies. */
    public function conversationsReply(SupportRequest $request, ProductConversation $conversation): ProductConversationResource
    {
        $this->support->replyToConversation($conversation, $request->user(), $request->validated('message'));

        return new ProductConversationResource($conversation->load(['product', 'customer', 'seller', 'messages.user']));
    }

    /** Admin reply to a ticket through the API thread. */
    public function ticketsReply(SupportRequest $request, SupportTicket $ticket): SupportTicketResource
    {
        $this->support->replyToTicket($ticket, $request->user(), $request->validated('message'));

        return new SupportTicketResource($ticket->load(['user', 'replies.user']));
    }
}
