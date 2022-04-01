<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $category_id = $request->get('category_id') ?: 0;
        $categories = Category::where('category_id', $category_id)->get();

        return view('admin.categories.index', compact('categories', 'category_id'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $category_id = ($request->get('category_id')) ?: 0;
        return view('admin.categories.create', compact('category_id'));
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
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|min:0',
            'flag' => 'integer',
            'icon_name' => 'nullable|string|max:255',
        ]);

        $model = Category::create($request->all());

        $request->session()->flash('success', 'Запись добавлена');

        return redirect()->route('admin.categories.index', ['category_id' => $model->category_id]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $model = Category::where('id', $id)->first();
        return view('admin.categories.edit', compact('model'));
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
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|min:0',
            'flag' => 'integer',
            'icon_name' => 'nullable|string|max:255',
        ]);

        $model = Category::where('id', $id)->first();
        if (!$model->update($request->all())) {
            $request->session()->flash('success', 'Ошибка обновления записи');

            return redirect()->route('admin.categories.edit', ['category' => $model->id, 'category_id' => $model->category_id]);
        }

        $request->session()->flash('success', 'Запись обновлена');

        return redirect()->route('admin.categories.index', ['category_id' => $model->category_id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $model = Category::where('id', $id)->first();
        if (!$model || !$model->delete()) {
            $request->session()->flash('success', 'Ошибка удаления записи');

            return redirect()->route('admin.categories.edit', ['category' => $model->id, 'category_id' => $model->category_id]);
        }

        $request->session()->flash('success', 'Запись удалена');

        return redirect()->route('admin.categories.index', ['category_id' => $model->category_id]);
    }
}
