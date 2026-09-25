<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/** {data, totals, filters}; stock also carries links and meta because it is paginated. */
class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function stock(Request $request): AnonymousResourceCollection
    {
        $result = $this->reports->run('stock', $request->query());
        $page = $result['query']
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
            ->withQueryString()
            ->through(fn (object $row) => $this->money(($result['map'])($row)));

        return JsonResource::collection($page)->additional([
            'totals' => $this->money($result['totals']),
            'filters' => $result['filters'],
        ]);
    }

    public function inHouseSales(Request $request): JsonResponse
    {
        return $this->all('in-house-sales', $request);
    }

    public function sellerSales(Request $request): JsonResponse
    {
        return $this->all('seller-sales', $request);
    }

    public function wishlist(Request $request): JsonResponse
    {
        return $this->all('wishlist', $request);
    }

    private function all(string $report, Request $request): JsonResponse
    {
        $result = $this->reports->run($report, $request->query());

        return response()->json([
            'data' => $result['query']->get()->map(fn (object $row) => $this->money(($result['map'])($row))),
            'totals' => $this->money($result['totals']),
            'filters' => $result['filters'],
        ]);
    }

    /** Money as 2-decimal strings, so clients never see float artefacts. */
    private function money(array $values): array
    {
        foreach (ReportService::MONEY as $key) {
            if (array_key_exists($key, $values)) {
                $values[$key] = number_format($values[$key], 2, '.', '');
            }
        }

        return $values;
    }
}
