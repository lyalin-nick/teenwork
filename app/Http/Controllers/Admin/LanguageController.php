<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        $languages = Language::all();

        return view('admin.languages.index', compact('languages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('admin.languages.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $model = Language::create($request->all());

        $request->session()->flash('success', 'Запись добавлена');

        return redirect()->route('admin.languages.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $model = Language::where('id', $id)->first();
        return view('admin.languages.edit', compact('model'));
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
            'name' => 'required|string|max:255'
        ]);

        $model = Language::where('id', $id)->first();
        if (!$model->update($request->only('name'))) {
            $request->session()->flash('success', 'Ошибка обновления записи');

            return redirect()->route('admin.languages.edit', ['language' => $model->id]);
        }

        $request->session()->flash('success', 'Запись обновлена');

        return redirect()->route('admin.languages.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $model = Language::where('id', $id)->first();
        if (!$model || !$model->delete()) {
            $request->session()->flash('success', 'Ошибка удаления записи');

            return redirect()->route('admin.languages.edit', ['language' => $model->id]);
        }

        $request->session()->flash('success', 'Запись удалена');

        return redirect()->route('admin.languages.index');
    }
}
