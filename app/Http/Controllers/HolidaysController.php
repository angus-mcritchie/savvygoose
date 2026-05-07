<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Holidays\Holidays;

class HolidaysController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'max:10'],
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $country = strtolower($validated['country']);

        if (! Holidays::has($country)) {
            return response()->json([
                'message' => 'Unsupported country.',
                'errors' => ['country' => ['That country is not supported.']],
            ], 422);
        }

        $from = CarbonImmutable::createFromFormat('Y-m-d', $validated['from'])->startOfDay();
        $to = CarbonImmutable::createFromFormat('Y-m-d', $validated['to'])->startOfDay();

        // Cap the range so a careless or hostile request can't fan out forever.
        if ($from->diffInYears($to) > 25) {
            return response()->json([
                'message' => 'Range too large.',
                'errors' => ['to' => ['Range must span 25 years or fewer.']],
            ], 422);
        }

        $holidays = collect(Holidays::for($country)->getInRange($from, $to))
            ->map(fn ($holiday) => [
                'date' => $holiday->date->format('Y-m-d'),
                'name' => $holiday->name,
                'type' => $holiday->type->value,
                'region' => $holiday->region,
            ])
            ->values();

        return response()->json([
            'country' => $country,
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'holidays' => $holidays,
        ]);
    }
}
