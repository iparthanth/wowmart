<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Facades\DB;

/** Every write to a static page. Input is validated already (PageRequest). */
class PageService
{
    public function create(array $data): Page
    {
        return DB::transaction(fn () => Page::create($data));
    }

    public function update(Page $page, array $data): Page
    {
        return DB::transaction(fn () => tap($page)->update($data));
    }

    /** A page can always be deleted: the storefront links render a 404 after. */
    public function delete(Page $page): void
    {
        $page->delete();
    }
}
