<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    public function index(Request $request): View
    {
        $f = Review::filtersFrom($request->query());

        // Whole-table summary in one query, independent of the filters.
        $summary = Review::toBase()->selectRaw(
            'count(*) as total, avg(rating) as average, sum(case when status = 0 then 1 else 0 end) as hidden'
        )->first();

        return view('admin.reviews.index', [
            'reviews' => Review::query()
                ->with(['product.thumbnail', 'user'])
                ->filtered($f)
                ->paginate(20)
                ->withQueryString(),
            'filters' => $f,
            'filtered' => $f['search'] !== '' || $f['rating'] || $f['status'],
            'summary' => [
                'total' => (int) $summary->total,
                'average' => round((float) $summary->average, 2),
                'hidden' => (int) $summary->hidden,
            ],
        ]);
    }

    public function toggle(Review $review, string $field): RedirectResponse
    {
        $review = $this->reviews->setFlags($review, [$field => ! $review->status]);

        return back()->with('status', $review->status
            ? 'The review is visible on the storefront again.'
            : 'The review is hidden from the storefront.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $this->reviews->delete($review);

        return back()->with('status', 'Review deleted. The product rating is updated.');
    }
}
