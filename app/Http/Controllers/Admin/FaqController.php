<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** FAQ management - mirrors Areas/Admin/Controllers/FaqController. */
class FaqController extends Controller
{
    public function index(): View
    {
        return view('admin.faq.index', ['faqs' => Faq::query()->orderBy('DisplayOrder')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Faq::query()->create([
            'Question' => trim($data['Question']),
            'Answer' => trim($data['Answer']),
            'Category' => ! empty($data['Category']) ? trim($data['Category']) : null,
            'DisplayOrder' => $data['DisplayOrder'] ?? 0,
            'IsPublished' => $request->boolean('IsPublished'),
        ]);

        return redirect()->route('admin.faq.index')->with('faqMessage', 'FAQ added.');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $this->validated($request, requireId: true);

        $faq = Faq::query()->find($data['Id']);
        if (! $faq) {
            abort(404);
        }

        $faq->Question = trim($data['Question']);
        $faq->Answer = trim($data['Answer']);
        $faq->Category = ! empty($data['Category']) ? trim($data['Category']) : null;
        $faq->DisplayOrder = $data['DisplayOrder'] ?? 0;
        $faq->IsPublished = $request->boolean('IsPublished');
        $faq->save();

        return redirect()->route('admin.faq.index')->with('faqMessage', 'FAQ updated.');
    }

    public function togglePublished(int $id): RedirectResponse
    {
        $faq = Faq::query()->find($id);
        if ($faq) {
            $faq->IsPublished = ! $faq->IsPublished;
            $faq->save();
        }

        return redirect()->route('admin.faq.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        Faq::query()->where('Id', $id)->delete();

        return redirect()->route('admin.faq.index')->with('faqMessage', 'FAQ permanently deleted.');
    }

    private function validated(Request $request, bool $requireId = false): array
    {
        $rules = [
            'Question' => ['required', 'max:300'],
            'Answer' => ['required', 'max:4000'],
            'Category' => ['nullable', 'max:100'],
            'DisplayOrder' => ['nullable', 'integer'],
        ];
        if ($requireId) {
            $rules['Id'] = ['required', 'integer'];
        }

        return $request->validate($rules);
    }
}
