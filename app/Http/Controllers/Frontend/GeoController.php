<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GeoController extends Controller
{
    public function divisions(): JsonResponse
    {
        $divisions = Cache::rememberForever('geo.divisions.v2', function () {
            return DB::table('divisions')->select('id', 'name', 'bn_name')->orderBy('id')->get()->toArray();
        });

        return response()->json($divisions);
    }

    public function districts(int $divisionId): JsonResponse
    {
        $districts = Cache::rememberForever('geo.districts.v2.' . $divisionId, function () use ($divisionId) {
            return DB::table('districts')->select('id', 'division_id', 'name', 'bn_name')
                ->where('division_id', $divisionId)
                ->orderBy('name')
                ->get()
                ->toArray();
        });

        return response()->json($districts);
    }

    public function upazilas(int $districtId): JsonResponse
    {
        $upazilas = Cache::rememberForever('geo.upazilas.v2.' . $districtId, function () use ($districtId) {
            return DB::table('upazilas')->select('id', 'district_id', 'name', 'bn_name')
                ->where('district_id', $districtId)
                ->orderBy('name')
                ->get()
                ->toArray();
        });

        return response()->json($upazilas);
    }
}
