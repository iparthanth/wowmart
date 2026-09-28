<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LanguageRequest;
use App\Models\BusinessSetting;
use App\Models\Language;
use App\Services\LanguageService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function __construct(private readonly LanguageService $languages) {}

    /** The list, with the "Add a language" form beside it. */
    public function index(): View
    {
        return view('admin.languages.index', [
            'languages' => Language::orderBy('name')->paginate(20),
            'newLanguage' => new Language,
            'defaultCode' => BusinessSetting::get('default_language', 'en'),
        ]);
    }

    public function store(LanguageRequest $request): RedirectResponse
    {
        $language = $this->languages->create($request->payload());

        return redirect()->route('admin.languages.index')->with('status', "Language \"{$language->name}\" added.");
    }

    public function edit(Language $language): View
    {
        return view('admin.languages.edit', [
            'language' => $language,
            'defaultCode' => BusinessSetting::get('default_language', 'en'),
        ]);
    }

    public function update(LanguageRequest $request, Language $language): RedirectResponse
    {
        $this->languages->update($language, $request->payload());

        return redirect()->route('admin.languages.edit', $language)->with('status', 'Changes saved.');
    }

    public function destroy(Language $language): RedirectResponse
    {
        try {
            $this->languages->delete($language);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.languages.index')->with('status', "Language \"{$language->name}\" deleted.");
    }

    public function makeDefault(Language $language): RedirectResponse
    {
        try {
            $this->languages->makeDefault($language);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.languages.index')->with('status', "\"{$language->name}\" is now the default language.");
    }
}
