<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\Language;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Every write to a language. Input is validated already (LanguageRequest). */
class LanguageService
{
    public function create(array $data): Language
    {
        return DB::transaction(fn () => Language::create($data));
    }

    public function update(Language $language, array $data): Language
    {
        return DB::transaction(fn () => tap($language)->update($data));
    }

    /** @throws DomainException while the language is the panel's default */
    public function delete(Language $language): void
    {
        if ($language->code === BusinessSetting::get('default_language', 'en')) {
            throw new DomainException('This is the default language. Choose another default first.');
        }

        $language->delete();
    }

    /** @throws DomainException when the language is unknown or disabled */
    public function makeDefault(Language $language): void
    {
        if (! $language->status) {
            throw new DomainException('Enable the language before making it the default.');
        }

        BusinessSetting::put('default_language', $language->code);
    }
}
