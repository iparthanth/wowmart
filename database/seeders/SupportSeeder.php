<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\ProductConversation;
use App\Models\ProductQuery;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/** Rows for the support desk: tickets, contact messages, product queries, buyer-seller chats. */
class SupportSeeder extends Seeder
{
    private const TICKETS = [
        ['My order arrived with a broken part', 'One of the drone motors arrived cracked. Can I get a replacement shipped?', 'admin', 'Sorry about that! We have dispatched a replacement motor free of charge; tracking will reach your email tonight.'],
        ['Coupon code not applying', 'The WELCOME10 coupon says invalid at checkout but it should be valid this month.', null, 'The coupon requires a minimum order of 500 taka. Adding one more item will make it apply.'],
        ['How do I track my parcel?', 'I paid with bKash two days ago and have no tracking number yet.', 'admin', 'Your parcel left the warehouse this morning. The tracking code is now visible under My Orders.'],
        ['Return window for damaged box', 'The box was crushed but the product seems fine. Should I still report it?', null, null],
        ['Invoice request', 'I need a company invoice for order ORD-000104.', null, null],
    ];

    private const CONTACTS = [
        ['Farhan Ahmed', 'farhan@example.test', '+8801711000011', 'Do you ship wholesale quantities outside Dhaka?', 'Yes, wholesale cartons ship nationwide with our carrier partners.'],
        ['Nusrat Jahan', 'nusrat@example.test', '+8801811000022', 'I want to become a seller on WowMart. What documents are needed?', 'Register a seller account and upload your NID plus a trade licence; approval takes two working days.'],
        ['Imran Kabir', 'imran@example.test', null, 'Is cash on delivery available for Chattogram?', null],
        ['Sadia Islam', 'sadia@example.test', null, 'Please remove my account and all personal data.', null],
    ];

    private const QUERIES = [
        'Is this kit compatible with Arduino Mega?',
        'What is the battery life under continuous use?',
        'Does the price include VAT?',
        'Can it ship in original packaging as a gift?',
    ];

    private const CONVERSATIONS = [
        ['Bulk discount for 20 pieces', 'Hello, I need 20 units for a school lab. Is there a bulk price?', 'seller', 'Hi! For 20 pieces I can offer 8% off plus free shipping. Shall I reserve them?', 'admin', 'Bulk price agreed with the seller; both sides confirmed in chat.'],
        ['Colour option availability', 'Do you have the red variant in stock?', 'seller', 'Red is back in stock as of this morning. Go ahead and order!', null, null],
    ];

    public function run(): void
    {
        $customers = User::where('user_type', 'customer')->orderBy('id')->get();
        $sellers = User::where('user_type', 'seller')->orderBy('id')->get();
        $products = Product::orderBy('id')->get();
        $admin = User::where('user_type', 'admin')->first();

        if ($customers->isEmpty() || $products->isEmpty() || ! $admin) {
            return;
        }

        // Tickets: the customer's first message lands in the replies thread.
        foreach (self::TICKETS as $i => [$subject, $message, $last, $adminReply]) {
            $customer = $customers[$i % $customers->count()];
            $ticket = SupportTicket::create([
                'user_id' => $customer->id,
                'subject' => $subject,
                'created_at' => Carbon::now()->subDays(9 - $i)->subHours($i * 3),
            ]);
            $ticket->replies()->create([
                'user_id' => $customer->id,
                'message' => $message,
                'created_at' => $ticket->created_at,
            ]);
            if ($adminReply !== null) {
                $ticket->replies()->create([
                    'user_id' => $admin->id,
                    'message' => $adminReply,
                    'created_at' => Carbon::parse($ticket->created_at)->addHours(4),
                ]);
            }
        }

        foreach (self::CONTACTS as $i => [$name, $email, $phone, $query, $reply]) {
            ContactMessage::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'query' => $query,
                'reply' => $reply,
                'status' => $reply === null ? 'open' : 'answered',
                'created_at' => Carbon::now()->subDays(12 - $i * 2),
            ]);
        }

        foreach (self::QUERIES as $i => $question) {
            $answered = $i < 2;
            ProductQuery::create([
                'user_id' => $customers[$i % $customers->count()]->id,
                'product_id' => $products[$i % $products->count()]->id,
                'question' => $question,
                'reply' => $answered ? 'Yes — verified with the product team and updated on the listing.' : null,
                'status' => $answered ? 'answered' : 'open',
                'created_at' => Carbon::now()->subDays(8 - $i),
            ]);
        }

        if ($sellers->isEmpty()) {
            return;
        }

        // Buyer-seller chats about a single product, with staff able to step in.
        foreach (self::CONVERSATIONS as $i => [$title, $buyer, $next, $sellerReply, $joiner, $adminMessage]) {
            $customer = $customers[($i + 2) % $customers->count()];
            $seller = $sellers[$i % $sellers->count()];
            $conversation = ProductConversation::create([
                'product_id' => $products[($i + 3) % $products->count()]->id,
                'customer_id' => $customer->id,
                'seller_id' => $seller->id,
                'title' => $title,
                'created_at' => Carbon::now()->subDays(5 - $i * 2),
            ]);
            $at = Carbon::parse($conversation->created_at);
            $conversation->messages()->create([
                'user_id' => $customer->id,
                'message' => $buyer,
                'created_at' => $at->copy(),
            ]);
            if ($sellerReply !== null) {
                $conversation->messages()->create([
                    'user_id' => $seller->id,
                    'message' => $sellerReply,
                    'created_at' => $at->copy()->addHours(2),
                ]);
            }
            if ($adminMessage !== null && $joiner === 'admin' && $admin) {
                $conversation->messages()->create([
                    'user_id' => $admin->id,
                    'message' => $adminMessage,
                    'created_at' => $at->copy()->addHours(3),
                ]);
            }
        }
    }
}
