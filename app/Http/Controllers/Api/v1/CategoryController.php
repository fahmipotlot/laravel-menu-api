<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $category = (new Category)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $category->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        $category->orderBy('name', 'asc');

        return $category->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required|unique:categories|max:255',
            'description' => 'required|max:255'
        ]);

        $cat = Category::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return response()->json(['message' => 'Category created!'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Category::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cat = Category::findOrFail($id);

        $this->validate(request(), [
            'name' => 'required|unique:categories|max:255',
            'description' => 'required|max:255'
        ]);

        $cat->update([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return response()->json(['message' => 'Category updated!'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cat = Category::findOrFail($id);
        $cat->delete();
        return response()->json(['message' => 'Category deleted!'], 200);
    }
}
