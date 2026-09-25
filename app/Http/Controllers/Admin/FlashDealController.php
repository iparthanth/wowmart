<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FlashDealRequest;
use App\Models\FlashDeal;
use App\Models\Product;
use App\Models\Upload;
use App\Services\FlashDealService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FlashDealController extends Controller
{
    public function __construct(private readonly FlashDealService $deals) {}

    public function index(): View
    {
        return view('admin.flash-deals.index', [
            'deals' => FlashDeal::with('bannerFile')->withCount('products')->latest('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        // Opens ready to run: active, starting this hour, for a week.
        $deal = (new FlashDeal)->forceFill([
            'status' => true,
            'featured' => false,
            'start_date' => now()->startOfHour(),
            'end_date' => now()->startOfHour()->addWeek(),
            'background_color' => '#14213D',
            'text_color' => '#FFFFFF',
        ]);

        return $this->form($deal);
    }

    public function store(FlashDealRequest $request): RedirectResponse
    {
        $deal = $this->deals->create($request->validated());

        return redirect()->route('admin.flash-deals.index')->with('status', "Flash deal \"{$deal->title}\" created.");
    }

    public function edit(FlashDeal $flashDeal): View
    {
        return $this->form($flashDeal->load('products'));
    }

    public function update(FlashDealRequest $request, FlashDeal $flashDeal): RedirectResponse
    {
        $this->deals->update($flashDeal, $request->validated());

        return redirect()->route('admin.flash-deals.edit', $flashDeal)->with('status', 'Changes saved.');
    }

    public function destroy(FlashDeal $flashDeal): RedirectResponse
    {
        $this->deals->delete($flashDeal);

        return redirect()->route('admin.flash-deals.index')
            ->with('status', "Flash deal \"{$flashDeal->title}\" deleted. Its products are back to their normal prices.");
    }

    public function toggle(FlashDeal $flashDeal, string $field): RedirectResponse
    {
        $this->deals->setFlags($flashDeal, [$field => ! $flashDeal->{$field}]);

        $message = $field === 'featured'
            ? sprintf('"%s" is %s the featured deal.', $flashDeal->title, $flashDeal->featured ? 'now' : 'no longer')
            : sprintf('"%s" is switched %s.', $flashDeal->title, $flashDeal->status ? 'on' : 'off');

        return back()->with('status', $message);
    }

    private function form(FlashDeal $deal): View
    {
        return view('admin.flash-deals.form', [
            'deal' => $deal,
            // ponytail: renders every product; fine for hundreds, add search/paging past that.
            'products' => Product::with('thumbnail', 'flashDeals')->orderBy('name')->get(['id', 'name', 'unit_price', 'thumbnail_img']),
            'images' => Upload::where('type', 'image')->latest('id')->get(),
        ]);
    }
}
