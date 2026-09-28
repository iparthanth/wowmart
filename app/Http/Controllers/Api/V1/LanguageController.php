<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LanguageRequest;
use App\Http\Resources\V1\LanguageResource;
use App\Models\Language;
use App\Services\LanguageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LanguageController extends Controller
{
    public function __construct(private readonly LanguageService $languages) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return LanguageResource::collection(
            Language::orderBy('name')->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    public function store(LanguageRequest $request): LanguageResource
    {
        return new LanguageResource($this->languages->create($request->payload())); // 201
    }

    public function show(Language $language): LanguageResource
    {
        return new LanguageResource($language);
    }

    public function update(LanguageRequest $request, Language $language): LanguageResource
    {
        return new LanguageResource($this->languages->update($language, $request->payload()));
    }

    public function destroy(Language $language): Response
    {
        $this->languages->delete($language); // 409 while it is the default language

        return response()->noContent();
    }
}
