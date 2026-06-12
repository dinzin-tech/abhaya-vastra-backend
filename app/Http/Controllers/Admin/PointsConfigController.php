<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PointsConfig;

class PointsConfigController extends Controller
{
    /**
     * Display the listing page.
     */
    public function index(Request $request)
    {
        return view('admin.modules.points.list', [
            'q' => $request->q,
            'offset' => $request->offset ?? 10,
        ]);
    }

    /**
     * Fetch points config rows for AJAX listing.
     */
    public function listPoints(Request $request)
    {
        $query = PointsConfig::query();
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where('min_amount', 'like', "%{$request->q}%")
                ->orWhere('max_amount', 'like', "%{$request->q}%")
                ->orWhere('points', 'like', "%{$request->q}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'success' => true,
            'rows' => view('admin.modules.points.list_rows', ['points' => $items])->render(),
            'items' => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show the form for creating a points config.
     */
    public function create()
    {
        return view('admin.modules.points.add', [
            'item' => null
        ]);
    }

    /**
     * Store a new points configuration.
     */
    public function store(Request $request)
    {
        $request->validate([
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gte:min_amount',
            'points' => 'required|integer|min:0',
            'coin_value' => 'required|numeric|min:0.01',
            'status' => 'nullable|boolean',
        ]);



        $data = $request->only([
            'min_amount',
            'max_amount',
            'points',
            'coin_value'

        ]);


        $data['status'] = $request->boolean('status');

        PointsConfig::create($data);

        return redirect()->route('points.index')->with('success', 'Points configuration added successfully.');
    }

    /**
     * Show the form for editing a points config.
     */
    public function edit($id)
    {
        $item = PointsConfig::findOrFail($id);
        return view('admin.modules.points.add', compact('item'));
    }

    /**
     * Update an existing points configuration.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gte:min_amount',
            'points' => 'required|integer|min:0',
            'coin_value' => 'required|numeric|min:0.01',
            'status' => 'nullable|boolean',
        ]);

        $config = PointsConfig::findOrFail($id);
        $data = $request->only([
            'min_amount',
            'max_amount',
            'points',
            'coin_value'
        ]);


        $data['status'] = $request->has('status') ? 1 : 0;

        $config->update($data);

        return redirect()->route('points.index')->with('success', 'Points configuration updated successfully.');
    }

    /**
     * Delete a points configuration.
     */
    public function delete(Request $request)
    {
        $config = PointsConfig::findOrFail($request->id);
        $config->delete();

        return response()->json([
            'message' => 'Points configuration deleted successfully.',
        ], 200);
    }
}
