<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ReviewResource;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/** Moderation only: customers write reviews in the storefront, so there is no store or update. */
class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $reviews = Review::query()
            ->with(['product', 'user'])
            ->filtered(Review::filtersFrom($request->query()))
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
            ->withQueryString();

        return ReviewResource::collection($reviews);
    }

    public function show(Review $review): ReviewResource
    {
        return new ReviewResource($review->load(['product', 'user']));
    }

    public function flags(Request $request, Review $review): ReviewResource
    {
        $flags = $request->validate(['status' => ['required', 'boolean']]);

        return $this->show($this->reviews->setFlags($review, ['status' => (bool) $flags['status']]));
    }

    public function destroy(Review $review): Response
    {
        $this->reviews->delete($review);

        return response()->noContent();
    }
}
