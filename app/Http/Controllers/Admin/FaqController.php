<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        $faqs = Faq::all();

        return view('admin.faqs.index', compact('faqs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('admin.faqs.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string'
        ]);

        $model = Faq::create($request->all());

        $request->session()->flash('success', 'Запись добавлена');

        return redirect()->route('admin.faqs.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $model = Faq::where('id', $id)->first();
        return view('admin.faqs.edit', compact('model'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string'
        ]);

        $model = Faq::where('id', $id)->first();
        if (!$model->update($request->only('question', 'answer'))) {
            $request->session()->flash('success', 'Ошибка обновления записи');

            return redirect()->route('admin.faqs.edit', ['faq' => $model->id]);
        }

        $request->session()->flash('success', 'Запись обновлена');

        return redirect()->route('admin.faqs.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $id)
    {
        $model = Faq::where('id', $id)->first();
        if (!$model || !$model->delete()) {
            $request->session()->flash('success', 'Ошибка удаления записи');

            return redirect()->route('admin.faqs.edit', ['faq' => $model->id]);
        }

        $request->session()->flash('success', 'Запись удалена');

        return redirect()->route('admin.faqs.index');
    }
}
