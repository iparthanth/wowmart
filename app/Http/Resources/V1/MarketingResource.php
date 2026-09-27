<?php

namespace App\Http\Resources\V1;

use App\Models\Newsletter;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Newsletter|Subscriber */
class MarketingResource extends JsonResource
{
    /**
     * Wraps a newsletter or a subscriber, whichever the route returned; the
     * `type` key tells them apart on mixed listings.
     */
    public function toArray(Request $request): array
    {
        return $this->resource instanceof Newsletter ? $this->newsletter() : $this->subscriber();
    }

    /** @return array<string, mixed> */
    private function newsletter(): array
    {
        /** @var Newsletter $n */
        $n = $this->resource;

        return [
            'type' => 'newsletter',
            'id' => $n->id,
            'subject' => $n->subject,
            'body' => $n->body,
            'segment' => $n->segment,
            'sent_at' => $n->sent_at?->toISOString(),
        ];
    }

    /** @return array<string, mixed> */
    private function subscriber(): array
    {
        /** @var Subscriber $s */
        $s = $this->resource;

        return [
            'type' => 'subscriber',
            'id' => $s->id,
            'email' => $s->email,
            'status' => (bool) $s->status,
        ];
    }
}
