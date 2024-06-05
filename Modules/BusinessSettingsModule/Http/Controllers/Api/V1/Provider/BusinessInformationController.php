<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Api\V1\Provider;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\ProviderSetting;

class BusinessInformationController extends Controller
{
    private ProviderSetting $providerSetting;

    public function __construct(ProviderSetting $providerSetting)
    {
        $this->providerSetting = $providerSetting;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function businessSettingsGet(Request $request): JsonResponse
    {
        $request['key'] = ['provider_serviceman_can_cancel_booking', 'provider_serviceman_can_edit_booking'];

        $dataValues = $this->providerSetting
            ->select('key_name', 'live_values', 'test_values', 'mode')
            ->when(!is_null($request['key']), fn($query) => $query->whereIn('key_name', $request['key'])->where('provider_id', $request->user()->provider->id))
            ->get();

        return response()->json(response_formatter(DEFAULT_200, $dataValues), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function businessSettingsSet(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required',
            'data.*.key' => 'required|string',
            'data.*.value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        foreach (collect(json_decode($request['data'], true)) as $key => $item) {
            $settingType = in_array($item['key'], ['provider_serviceman_can_edit_booking', 'provider_serviceman_can_cancel_booking']) ? 'serviceman_config' : null;

            if (!is_null($settingType)) {
                $this->providerSetting->updateOrCreate(['key_name' => $item['key'], 'provider_id' => $request->user()->provider->id], [
                    'key_name' => $item['key'],
                    'live_values' => $item['value'],
                    'test_values' => $item['value'],
                    'settings_type' => $settingType,
                    'mode' => 'live',
                    'is_active' => 1,
                ]);
            }
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }



    public function availabilityStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_availability' => 'in:1,0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed'], 400);
        }

        $provider = Provider::where('user_id', $request->user()->id)->first();

        if ($provider){
            $provider->service_availability = $request->service_availability;
            $provider->save();
            return response()->json(['message' => 'Successfully updated'], 200);
        }

        return response()->json(['message' => 'Provider not found'], 404);
    }




public function availabilitySchedule(Request $request): JsonResponse
{
    $request->validate([
       'start_time' => 'nullable|string',
       'end_time' => 'nullable|string',
        'day' => 'array',
        'day.*' => 'in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
    ]);

    $requestData = $request->all();

    $timeSchedulesData = [
        'start_time' => $requestData['start_time'],
        'end_time' => $requestData['end_time'],
    ];

    $weekend = $requestData['day'] ?? [];

    $this->providerSetting::updateOrCreate(
        [
            'key_name' => 'time_schedule',
            'settings_type' => 'service_schedule',
            'provider_id' => $request->user()->provider->id,
        ],
        [
            'live_values' => json_encode($timeSchedulesData),
            'test_values' => json_encode($timeSchedulesData),
        ]
    );

    $this->providerSetting::updateOrCreate(
        [
            'key_name' => 'weekends',
            'settings_type' => 'service_schedule',
            'provider_id' => $request->user()->provider->id,
        ],
        [
            'live_values' => isset($weekend) ? json_encode($weekend) : null,
            'test_values' => isset($weekend) ? json_encode($weekend) : null,
        ]
    );

    return response()->json(['message' => 'Successfully updated'], 200);
}
  public function getAvailabilityStatus(Request $request): JsonResponse
{
    $provider = Provider::where('user_id', $request->user()->id)->first();

    if ($provider) {
        return response()->json(['service_availability' => $provider->service_availability], 200);
    }

    return response()->json(['message' => 'Provider not found'], 404);
}

public function getAvailabilitySchedule(Request $request): JsonResponse
{
    $provider = Provider::where('user_id', $request->user()->id)->first();

    if ($provider) {
        $timeSchedule = $this->providerSetting::where([
            'key_name' => 'time_schedule',
            'settings_type' => 'service_schedule',
            'provider_id' => $provider->id,
        ])->first();

        $weekends = $this->providerSetting::where([
            'key_name' => 'weekends',
            'settings_type' => 'service_schedule',
            'provider_id' => $provider->id,
        ])->first();

        return response()->json([
            'time_schedule' => $timeSchedule ? json_decode($timeSchedule->live_values) : null,
            'weekends' => $weekends ? json_decode($weekends->live_values) : null,
        ], 200);
    }

    return response()->json(['message' => 'Provider not found'], 404);
}
}
