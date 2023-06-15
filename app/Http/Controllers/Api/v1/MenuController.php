<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use DB;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menu = (new Menu)->newQuery();

        $menu->with(['category', 'recipes']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $menu->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('category')) {
            $category = request()->input('category');
            $menu->whereHas('category', function ($q) use ($category){
                $q->whereIn('menu.category_id', $category);
            });
        }

        if (request()->has('recipes')) {
            $recipes = request()->input('recipes');
            $menu->whereHas('recipes', function ($q) use ($recipes) {
                for ($i=0; $i<count(request()->input('recipes')); $i++) {
                    if ($i==0) {
                        $q->where('recipes.id', $recipes[$i]);                            
                    } else {
                        $q->orWhere('recipes.id', $recipes[$i]);
                    }
                }
            });
        }

        $menu->orderBy('name', 'asc');

        return $menu->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required|unique:menus|max:255',
            'description' => 'required|max:255',
            'image' => 'required|image',
            'category_id' => 'required|exists:categories,id',
            'recipe_id'     => 'required|array',
            'recipe_id.*'   => 'required|exists:recipes,id'
        ]);

        if ($request->hasFile('image')) {
            if ($request->has('image')) {
                $image_data = request()->file('image');
                $image_ext  = request()->file('image')->getClientOriginalExtension();
                $image_name = md5(time()).".".$image_ext;
                $image_path = 'images/menu';

                $uploaded = Storage::disk('public')->putFileAs($image_path, $image_data, $image_name, ['visibility' => 'public']);
            }
        }

        $menu = Menu::create([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'image' => isset($uploaded) ? $uploaded : null
        ]);

        $menu->recipes()->attach($request->recipe_id);

        return response()->json(['message' => 'menu created!'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Menu::with(['category', 'recipes'])->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $menu = Menu::findOrFail($id);

        $this->validate(request(), [
            'name' => 'required|max:255|unique:menus,name,'. $id .'',
            'description' => 'required|max:255',
            'image' => 'nullable|image',
            'category_id' => 'required|exists:categories,id',
            'recipe_id'     => 'required|array',
            'recipe_id.*'   => 'required|exists:recipes,id'
        ]);

        if ($request->hasFile('image')) {
            // delete data
            if (Storage::disk('public')->exists($menu->image)) {
                Storage::disk('public')->delete($menu->image);
            }

            // upload data
            if ($request->has('image')) {
                $image_data = request()->file('image');
                $image_ext  = request()->file('image')->getClientOriginalExtension();
                $image_name = md5(time()).".".$image_ext;
                $image_path = 'images/menu';

                $uploaded = Storage::disk('public')->putFileAs($image_path, $image_data, $image_name, ['visibility' => 'public']);

                $save = $menu->update([
                    'image' => $uploaded,
                    'name' => $request->name,
                    'category_id' => $request->category_id,
                    'description' => $request->description
                ]);
            }
        } else {
            $save = $menu->update([                
                'name' => $request->name,
                'category_id' => $request->category_id,
                'description' => $request->description
            ]);
        }        

        $menu->recipes()->syncWithoutDetaching($request->recipe_id);

        return response()->json(['message' => 'menu updated!'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $menu = Menu::findOrFail($id);
        if (Storage::disk('public')->exists($menu->image)) {
            Storage::disk('public')->delete($menu->image);
        }
        $menu->delete();
        return response()->json(['message' => 'menu deleted!'], 200);
    }
}
