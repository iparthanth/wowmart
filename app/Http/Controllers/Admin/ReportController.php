<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /** CSV header => row key (see ReportService's row maps). */
    private const CSV = [
        'stock' => ['Product' => 'name', 'SKU' => 'sku', 'Category' => 'category', 'Current stock' => 'current_stock', 'Low-stock level' => 'low_stock_quantity', 'State' => 'state_label', 'Unit price' => 'unit_price', 'Stock value' => 'stock_value'],
        'in-house-sales' => ['Product' => 'name', 'SKU' => 'sku', 'Units sold' => 'units', 'Revenue' => 'revenue'],
        'seller-sales' => ['Shop' => 'shop', 'Seller' => 'seller', 'Orders' => 'orders', 'Units sold' => 'units', 'Revenue' => 'revenue'],
        'wishlist' => ['Product' => 'name', 'SKU' => 'sku', 'Category' => 'category', 'Wishlists' => 'wishlists'],
    ];

    public function __construct(private readonly ReportService $reports) {}

    public function stock(Request $request): View|StreamedResponse
    {
        return $this->show('stock', $request);
    }

    public function inHouseSales(Request $request): View|StreamedResponse
    {
        return $this->show('in-house-sales', $request);
    }

    public function sellerSales(Request $request): View|StreamedResponse
    {
        return $this->show('seller-sales', $request);
    }

    public function wishlist(Request $request): View|StreamedResponse
    {
        return $this->show('wishlist', $request);
    }

    private function show(string $report, Request $request): View|StreamedResponse
    {
        $result = $this->reports->run($report, $request->query());

        if ($request->query('format') === 'csv') {
            return $this->csv($report, $result);
        }

        // Only stock lists every product; the others are bounded by what sold or
        // was wishlisted. ponytail: unpaginated, fine up to a few thousand rows.
        $rows = $report === 'stock'
            ? $result['query']->paginate(25)->withQueryString()->through($result['map'])
            : $result['query']->get()->map($result['map']);

        return view("admin.reports.{$report}", [
            'rows' => $rows,
            'totals' => $result['totals'],
            'filters' => $result['filters'],
            'categories' => array_key_exists('category', $result['filters']) ? Category::inTreeOrder() : collect(),
            'csvUrl' => $request->fullUrlWithQuery(['format' => 'csv', 'page' => null]),
        ]);
    }

    private function csv(string $report, array $result): StreamedResponse
    {
        $columns = self::CSV[$report];

        return response()->streamDownload(function () use ($columns, $result) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM: Excel otherwise reads UTF-8 (৳, Bangla names) as ANSI
            fputcsv($out, array_keys($columns), escape: '');

            foreach ($result['query']->lazy() as $row) {
                $row = ($result['map'])($row);
                fputcsv($out, array_map(fn (string $key) => self::cell($key, $row[$key]), $columns), escape: '');
            }

            fclose($out);
        }, "wowmart-{$report}-".today()->toDateString().'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Money as a plain 2-decimal number; text that a spreadsheet would run as a
     * formula (= + - @, or a leading tab/CR) is prefixed with a quote so it
     * stays text. Numbers are never touched, so a negative stock stays a number.
     */
    private static function cell(string $key, mixed $value): mixed
    {
        if (in_array($key, ReportService::MONEY, true)) {
            return number_format($value, 2, '.', '');
        }

        return is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'".$value
            : $value;
    }
}
