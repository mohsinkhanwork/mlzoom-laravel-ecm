<?php

namespace Modules\ProviderManagement\Http\Controllers\Api\V1\Provider;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Modules\UserManagement\Entities\User;
use Stevebauman\Location\Facades\Location;
use DateTimeZone;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class ConfigController extends Controller
{
    private $google_map;

    public function __construct()
    {
        $this->google_map = business_config('google_map', 'third_party');
    }

    public function getRoutes(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'origin_latitude' => 'required',
            'origin_longitude' => 'required',
            'destination_latitude' => 'required',
            'destination_longitude' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $distance = get_distance(
            [$request['origin_latitude'], $request['origin_longitude']],
            [$request['destination_latitude'], $request['destination_longitude']]
        );
        $distance = ($distance) ? number_format($distance, 2) . ' km' : null;

        return response()->json(response_formatter(DEFAULT_200, $distance), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function config(Request $request): JsonResponse
    {
        $location = Location::get($request->ip());

        //payment gateways
        $isPublished = 0;
        try {
            $fullData = include('Modules/Gateways/Addon/info.php');
            $isPublished = $fullData['is_published'] == 1 ? 1 : 0;
        } catch (\Exception $exception) {}

        $payment_gateways = collect($this->getPaymentMethods())
            ->filter(function ($query) use ($isPublished) {
                if (!$isPublished) {
                    return in_array($query['gateway'], array_column(PAYMENT_METHODS, 'key'));
                } else return $query;
            })->map(function ($query) {
                $query['label'] = ucwords(str_replace('_', ' ', $query['gateway']));
                return $query;
            })->values();

        return response()->json(response_formatter(DEFAULT_200, [
            'provider_can_cancel_booking' => (int)((business_config('provider_can_cancel_booking', 'provider_config'))->live_values ?? null),
            'provider_self_registration' => (int)((business_config('provider_self_registration', 'provider_config'))->live_values ?? null),
            'provider_self_delete' => (int)((business_config('provider_self_delete', 'provider_config'))->live_values ?? null),
            'provider_can_edit_booking' => (int)((business_config('provider_can_edit_booking', 'provider_config'))->live_values ?? null),
            'currency_symbol_position' => (business_config('currency_symbol_position', 'business_information'))->live_values ?? null,
            'business_name' => (business_config('business_name', 'business_information'))->live_values ?? null,
            'logo' => (business_config('business_logo', 'business_information'))->live_values ?? null,
            'favicon' => (business_config('business_favicon', 'business_information'))->live_values ?? null,
            'country_code' => (business_config('country_code', 'business_information'))->live_values ?? null,
            'business_address' => (business_config('business_address', 'business_information'))->live_values ?? null,
            'business_phone' => (business_config('business_phone', 'business_information'))->live_values ?? null,
            'business_email' => (business_config('business_email', 'business_information'))->live_values ?? null,
            'base_url' => rtrim(url('/'), '/') . '/api/v1/',
            'currency_decimal_point' => (business_config('currency_decimal_point', 'business_information'))->live_values ?? null,
            'currency_code' => (business_config('currency_code', 'business_information'))->live_values ?? null,
            'currency_symbol' => currency_symbol() ?? '',
            'about_us' => route('about-us'),
            'privacy_policy' => route('privacy-policy'),
            'terms_and_conditions' => (business_config('terms_and_conditions', 'pages_setup'))->is_active ? route('terms-and-conditions') : "",
            'refund_policy' => (business_config('refund_policy', 'pages_setup'))->is_active ? route('refund-policy') : "",
            'cancellation_policy' => (business_config('cancellation_policy', 'pages_setup'))->is_active ? route('cancellation-policy') : "",
            'default_location' => ['default' => [
                'lat' => $location->latitude ?? null,
                'lon' => $location->longitude ?? null
            ]],
            'image_base_url' => asset('storage/app/public'),
            'pagination_limit' => (int)pagination_limit(),
            'time_format' => (business_config('time_format', 'business_information'))->live_values ?? '24h',
            'max_cash_in_hand_limit_provider' => (business_config('max_cash_in_hand_limit_provider', 'provider_config'))->live_values ?? 0,
            'suspend_on_exceed_cash_limit_provider' => (business_config('suspend_on_exceed_cash_limit_provider', 'provider_config'))->live_values ?? 0,
            'default_commission' => (business_config('default_commission', 'business_information'))->live_values,
            'admin_details' => User::select('id', 'first_name', 'last_name', 'profile_image')->where('user_type', ADMIN_USER_TYPES[0])->first(),
            'footer_text' => (business_config('footer_text', 'business_information'))->live_values ?? null,
            'min_versions' => json_decode((business_config('provider_app_settings', 'app_settings'))->live_values ?? null),
            'minimum_withdraw_amount' => business_config('minimum_withdraw_amount', 'business_information') ? ((float)(business_config('minimum_withdraw_amount', 'business_information'))->live_values ?? null) : null,
            'maximum_withdraw_amount' => business_config('maximum_withdraw_amount', 'business_information') ? ((float)(business_config('maximum_withdraw_amount', 'business_information'))->live_values ?? null) : null,
            'phone_number_visibility_for_chatting' => (int)((business_config('phone_number_visibility_for_chatting', 'business_information'))->live_values ?? 0),
            'bid_offers_visibility_for_providers' => (int)((business_config('bid_offers_visibility_for_providers', 'bidding_system'))->live_values ?? 0),
            'bidding_status' => (int)((business_config('bidding_status', 'bidding_system'))->live_values ?? 0),
            'phone_verification' => (int)((business_config('phone_verification', 'service_setup'))->live_values ?? 0),
            'email_verification' => (int)((business_config('email_verification', 'service_setup'))->live_values ?? 0),
            'forget_password_verification_method' => (business_config('forget_password_verification_method', 'business_information'))->live_values ?? null,
            'otp_resend_time' => (int)(business_config('otp_resend_time', 'otp_login_setup'))?->live_values ?? null,
            'booking_otp_verification' => (int)(business_config('booking_otp', 'booking_setup'))->live_values ?? null,
            'service_complete_photo_evidence' => (int)(business_config('service_complete_photo_evidence', 'booking_setup'))?->live_values ?? null,
            'booking_additional_charge' => (int)(business_config('booking_additional_charge', 'booking_setup'))?->live_values ?? null,
            'additional_charge_label_name' => (string)(business_config('additional_charge_label_name', 'booking_setup'))?->live_values ?? null,
            'additional_charge_fee_amount' => (int)(business_config('additional_charge_fee_amount', 'booking_setup'))?->live_values ?? null,
            'payment_gateways' => $payment_gateways,
        ]), 200);
    }

    private function getPaymentMethods(): array
    {
        // Check if the addon_settings table exists
        if (!Schema::hasTable('addon_settings')) {
            return [];
        }

        $methods = DB::table('addon_settings')->where('settings_type', 'payment_config')->get();
        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $credentialsData = json_decode($method->$credentials);
            $additionalData = json_decode($method->additional_data);
            if ($credentialsData->status == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_image' => $additionalData?->gateway_image
                ];
            }
        }
        return $data;
    }
}
