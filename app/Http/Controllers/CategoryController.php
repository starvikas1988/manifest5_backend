<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string|max:255',
        ]);

        $category = Category::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $category
        ]);
    }

    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $category
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string|max:255',
        ]);

        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        return response()->json([
            'status' => 'success',
            'data' => $category
        ]);
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        }

        $category->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted'
        ]);
    }

    // public function fetchCategoriesWithMarkets()
    // {
    //     try {
    //         $categories = Category::with(['markets'])
    //             ->get()
    //             ->map(function ($category) {
    //                 return [
    //                     'id' => $category->id,
    //                     'name' => $category->name,
    //                     'market_count' => $category->markets->count(),
    //                     'markets' => $category->markets->map(function ($market) {
    //                         return [
    //                             'id' => $market->id,
    //                             'name' => $market->name,
    //                         ];
    //                     }),
    //                 ];
    //             });

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Categories with markets fetched successfully',
    //             'data' => $categories,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error fetching categories',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function fetchCategoriesWithMarkets()
{
    try {
        // Fetch categories with markets and join with operator_category to get user_id (operator_id)
        $categories = Category::select('categories.id', 'categories.name', 'operator_category.user_id as operator_id')
            ->join('operator_category', 'categories.id', '=', 'operator_category.category_id') // Inner join to get operator_id
            ->with(['markets']) // Load related markets
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'operator_id' => $category->operator_id, // ✅ Operator ID added
                    'market_count' => $category->markets->count(),
                    'markets' => $category->markets->map(function ($market) {
                        return [
                            'id' => $market->id,
                            'name' => $market->name,
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Categories with markets fetched successfully',
            'data' => $categories,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching categories',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
