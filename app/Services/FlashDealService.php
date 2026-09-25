<?php

namespace App\Services;

use App\Models\FlashDeal;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

/**
 * Every write to a flash deal, for the panel and the API alike. Input is validated already (FlashDealRequest).
 * The product form writes the same flash_deal_products pivot one product at a time.
 */
class FlashDealService
{
    private const FIELDS = ['title', 'banner', 'start_date', 'end_date', 'background_color', 'text_color'];

    public function create(array $data): FlashDeal
    {
        return DB::transaction(function () use ($data) {
            $deal = new FlashDeal($this->fields($data));
            $deal->forceFill(['slug' => FlashDeal::uniqueSlug($data['title'])]);

            return $this->save($deal, $data);
        });
    }

    /** The slug is kept on rename: the landing page link may already be shared. */
    public function update(FlashDeal $deal, array $data): FlashDeal
    {
        return DB::transaction(fn () => $this->save($deal->fill($this->fields($data)), $data));
    }

    /** Sets exactly the given flags, so repeating a request changes nothing. */
    public function setFlags(FlashDeal $deal, array $flags): FlashDeal
    {
        return DB::transaction(function () use ($deal, $flags) {
            $deal->forceFill(Arr::only($flags, ['status', 'featured']))->save();
            $this->keepSingleFeatured($deal);

            return $deal;
        });
    }

    /** The pivot rows cascade, so the products simply return to their normal prices. */
    public function delete(FlashDeal $deal): void
    {
        DB::transaction(fn () => $deal->delete());
    }

    private function save(FlashDeal $deal, array $data): FlashDeal
    {
        $deal->forceFill(['status' => (bool) $data['status'], 'featured' => (bool) $data['featured']])->save();

        $deal->products()->sync(array_map(fn (array $row) => [
            'discount' => $row['discount'],
            'discount_type' => $row['discount_type'],
        ], $data['products']));

        $this->keepSingleFeatured($deal);

        return $deal;
    }

    /** The shop has one featured-deal slot; featuring a deal takes it from the others. */
    private function keepSingleFeatured(FlashDeal $deal): void
    {
        if ($deal->featured) {
            FlashDeal::whereKeyNot($deal->id)->where('featured', true)->update(['featured' => false]);
        }
    }

    /**
     * The datetime cast writes the wall-clock time and drops any offset, so an
     * API client's "...T00:00:00+00:00" would be stored hours off. Convert first.
     */
    private function fields(array $data): array
    {
        $fields = Arr::only($data, self::FIELDS);

        foreach (['start_date', 'end_date'] as $key) {
            $fields[$key] = Date::parse($data[$key])->setTimezone(config('app.timezone'));
        }

        return $fields;
    }
}
