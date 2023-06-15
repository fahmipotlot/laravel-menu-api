<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Recipe;
use DB;
use Illuminate\Support\Facades\Storage;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recipe = (new Recipe)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $recipe->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        $recipe->orderBy('name', 'asc');

        return $recipe->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required|unique:recipes|max:255',
            'description' => 'required|max:255',
            'image' => 'required|image'
        ]);

        if ($request->hasFile('image')) {
            if ($request->has('image')) {
                $image_data = request()->file('image');
                $image_ext  = request()->file('image')->getClientOriginalExtension();
                $image_name = md5(time()).".".$image_ext;
                $image_path = 'images/recipe';

                $uploaded = Storage::disk('public')->putFileAs($image_path, $image_data, $image_name, ['visibility' => 'public']);
            }
        }

        $recipe = recipe::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => isset($uploaded) ? $uploaded : null
        ]);

        return response()->json(['message' => 'recipe created!'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Recipe::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $recipe = Recipe::findOrFail($id);

        $this->validate(request(), [
            'name' => 'required|max:255|unique:recipes,name,'. $id .'',
            'description' => 'required|max:255',
            'image' => 'nullable|image'
        ]);

        if ($request->hasFile('image')) {
            // delete data
            if (Storage::disk('public')->exists($recipe->image)) {
                Storage::disk('public')->delete($recipe->image);
            }

            // upload data
            if ($request->has('image')) {
                $image_data = request()->file('image');
                $image_ext  = request()->file('image')->getClientOriginalExtension();
                $image_name = md5(time()).".".$image_ext;
                $image_path = 'images/recipe';

                $uploaded = Storage::disk('public')->putFileAs($image_path, $image_data, $image_name, ['visibility' => 'public']);

                $save = $recipe->update([
                    'image' => $uploaded,
                    'name' => $request->name,
                    'description' => $request->description
                ]);
            }
        } else {
            $save = $recipe->update([                
                'name' => $request->name,
                'description' => $request->description
            ]);
        }

        return response()->json(['message' => 'recipe updated!'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $recipe = Recipe::findOrFail($id);
        if (Storage::disk('public')->exists($recipe->image)) {
            Storage::disk('public')->delete($recipe->image);
        }
        $recipe->delete();
        return response()->json(['message' => 'recipe deleted!'], 200);
    }
}
