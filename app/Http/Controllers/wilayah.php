<?php

namespace App\Http\Controllers;


use Indonesia;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class wilayah extends Controller
{
    public function provinces()
    {
        // Using the Indonesia package to get all provinces
        // This assumes that the package is properly installed and configured
        if (!class_exists('Indonesia')) {
            return response()->json(['error' => 'Indonesia package not found'], 500);
        }
        if (!method_exists('Indonesia', 'allProvinces')) {
            return response()->json(['error' => 'Indonesia package method not found'], 500);
        }
        if (!Indonesia::allProvinces()) {
            return response()->json(['error' => 'No provinces found'], 404);
        }
        // Return all provinces as a collection with 'name' and 'id'
        if (!Indonesia::allProvinces() instanceof Collection) {
            return response()->json(['error' => 'Invalid response from Indonesia package'], 500);
        }
        if (Indonesia::allProvinces()->isEmpty()) {
            return response()->json(['error' => 'No provinces found'], 404);
        }
        return Indonesia::allProvinces();
    }

    public function cities(Request $request)
    {
        return Indonesia::findProvince($request->id, ['cities'])->cities->pluck('name', 'id');
    }

    public function districts(Request $request)
    {
        return Indonesia::findCity($request->id, ['districts'])->districts->pluck('name', 'id');
    }

    public function villages(Request $request)
    {
        return Indonesia::findDistrict($request->id, ['villages'])->villages->pluck('name', 'id');
    }
}
