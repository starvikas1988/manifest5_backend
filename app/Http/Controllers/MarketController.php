<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Models\Category;

class MarketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $markets = Market::with('category')->get();
        return response()->json($markets);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $market = Market::create($request->all());
        return response()->json($market, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $market = Market::with('category')->find($id);
        return response()->json($market);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Market $market)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $market = Market::find($id);
        $market->update($request->all());
        return response()->json($market);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $market = Market::findOrFail($id);
        $market->delete();
        return response()->json(['message'=>'Deleted Successfully!'], 200);
    }
}
