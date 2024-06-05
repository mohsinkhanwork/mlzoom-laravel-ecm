<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\BusinessSettingsModule\Entities\DataSetting;
use Modules\BusinessSettingsModule\Entities\LandingPageFeature;
use Modules\BusinessSettingsModule\Entities\LandingPageSpeciality;
use Modules\BusinessSettingsModule\Entities\LandingPageTestimonial;
use Modules\CategoryManagement\Entities\Category;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    private BusinessSettings $businessSettings;
    private DataSetting $dataSetting;
    private Category $category;

    public function __construct(BusinessSettings $businessSettings, Category $category, DataSetting $dataSetting)
    {
        $this->businessSettings = $businessSettings;
        $this->dataSetting = $dataSetting;
        $this->category = $category;
    }

    public function home(): Factory|View|Application
    {
        $settings = $this->businessSettings->whereNotIn('settings_type', ['payment_config', 'third_party'])->get();
        $settingss = $this->dataSetting->whereIn('type', ['landing_web_app', 'landing_text_setup'])->get();
        $categories = $this->category->ofType('main')->ofStatus(1)->with(['children'])->withCount('zones')->get();
        $testimonials = LandingPageTestimonial::all();
        $features = LandingPageFeature::all();
        $specialities = LandingPageSpeciality::all();
        $socialMediaData = []; // Initialize an empty array to store social media data

    $data = $this->businessSettings->where('settings_type', 'landing_social_media')->get();
    foreach ($data as $item) {
        // Access the live_values directly as an array
        $socialMedia = $item->live_values;

        // Loop through the array to access each social media data
        foreach ($socialMedia as $media) {
            // Access media type and link
            $mediaType = $media['media'];
            $mediaLink = $media['link'];

            // Store media type and link in the array
            $socialMediaData[] = ['mediaType' => $mediaType, 'mediaLink' => $mediaLink];
        }
    }

    // Pass all the data to the view
    return view('welcome', compact('settings', 'categories', 'testimonials', 'features', 'specialities', 'settingss', 'socialMediaData'));
    }

    public function aboutUs(): Factory|View|Application
    {
        $settings = $this->businessSettings->whereNotIn('settings_type', ['payment_config', 'third_party'])->get();
        $dataSettings = $this->dataSetting->where('type', 'pages_setup')->get();
        $socialMediaData = []; // Initialize an empty array to store social media data

        $data = $this->businessSettings->where('settings_type', 'landing_social_media')->get();
        foreach ($data as $item) {
            // Access the live_values directly as an array
            $socialMedia = $item->live_values;

            // Loop through the array to access each social media data
            foreach ($socialMedia as $media) {
                // Access media type and link
                $mediaType = $media['media'];
                $mediaLink = $media['link'];

                // Store media type and link in the array
                $socialMediaData[] = ['mediaType' => $mediaType, 'mediaLink' => $mediaLink];
            }
        }
        return view('about-us', compact('settings', 'dataSettings', 'socialMediaData'));
    }

    public function privacyPolicy(): Factory|View|Application
    {
        $settings = $this->businessSettings->whereNotIn('settings_type', ['payment_config', 'third_party'])->get();
        $dataSettings = $this->dataSetting->where('type', 'pages_setup')->get();
        $socialMediaData = []; // Initialize an empty array to store social media data

        $data = $this->businessSettings->where('settings_type', 'landing_social_media')->get();
        foreach ($data as $item) {
            // Access the live_values directly as an array
            $socialMedia = $item->live_values;

            // Loop through the array to access each social media data
            foreach ($socialMedia as $media) {
                // Access media type and link
                $mediaType = $media['media'];
                $mediaLink = $media['link'];

                // Store media type and link in the array
                $socialMediaData[] = ['mediaType' => $mediaType, 'mediaLink' => $mediaLink];
            }
        }
        return view('privacy-policy', compact('settings', 'dataSettings', 'socialMediaData'));
    }

    public function termsAndConditions(): Factory|View|Application
    {
        $settings = $this->businessSettings->whereNotIn('settings_type', ['payment_config', 'third_party'])->get();
        $dataSettings = $this->dataSetting->where('type', 'pages_setup')->get();
        $socialMediaData = []; // Initialize an empty array to store social media data

        $data = $this->businessSettings->where('settings_type', 'landing_social_media')->get();
        foreach ($data as $item) {
            // Access the live_values directly as an array
            $socialMedia = $item->live_values;

            // Loop through the array to access each social media data
            foreach ($socialMedia as $media) {
                // Access media type and link
                $mediaType = $media['media'];
                $mediaLink = $media['link'];

                // Store media type and link in the array
                $socialMediaData[] = ['mediaType' => $mediaType, 'mediaLink' => $mediaLink];
            }
        }
        return view('terms-and-conditions', compact('settings', 'dataSettings', 'socialMediaData'));
    }

    public function contactUs(): Factory|View|Application
    {
        $settings = $this->businessSettings->whereNotIn('settings_type', ['payment_config', 'third_party'])->get();
        $socialMediaData = []; // Initialize an empty array to store social media data

        $data = $this->businessSettings->where('settings_type', 'landing_social_media')->get();
        foreach ($data as $item) {
            // Access the live_values directly as an array
            $socialMedia = $item->live_values;

            // Loop through the array to access each social media data
            foreach ($socialMedia as $media) {
                // Access media type and link
                $mediaType = $media['media'];
                $mediaLink = $media['link'];

                // Store media type and link in the array
                $socialMediaData[] = ['mediaType' => $mediaType, 'mediaLink' => $mediaLink];
            }
        }
        return view('contact-us', compact('settings', 'socialMediaData'));
    }

    public function cancellationPolicy(): Factory|View|Application
    {
        $settings = $this->businessSettings->whereNotIn('settings_type', ['payment_config', 'third_party'])->get();
        $dataSettings = $this->dataSetting->where('type', 'pages_setup')->get();
        $socialMediaData = []; // Initialize an empty array to store social media data

        $data = $this->businessSettings->where('settings_type', 'landing_social_media')->get();
        foreach ($data as $item) {
            // Access the live_values directly as an array
            $socialMedia = $item->live_values;

            // Loop through the array to access each social media data
            foreach ($socialMedia as $media) {
                // Access media type and link
                $mediaType = $media['media'];
                $mediaLink = $media['link'];

                // Store media type and link in the array
                $socialMediaData[] = ['mediaType' => $mediaType, 'mediaLink' => $mediaLink];
            }
        }
        return view('cancellation-policy', compact('settings', 'dataSettings', 'socialMediaData'));
    }

    public function refundPolicy(): Factory|View|Application
    {
        $settings = $this->businessSettings->whereNotIn('settings_type', ['payment_config', 'third_party'])->get();
        $dataSettings = $this->dataSetting->where('type', 'pages_setup')->get();
        $socialMediaData = []; // Initialize an empty array to store social media data

        $data = $this->businessSettings->where('settings_type', 'landing_social_media')->get();
        foreach ($data as $item) {
            // Access the live_values directly as an array
            $socialMedia = $item->live_values;

            // Loop through the array to access each social media data
            foreach ($socialMedia as $media) {
                // Access media type and link
                $mediaType = $media['media'];
                $mediaLink = $media['link'];

                // Store media type and link in the array
                $socialMediaData[] = ['mediaType' => $mediaType, 'mediaLink' => $mediaLink];
            }
        }
        return view('refund-policy', compact('settings', 'dataSettings', 'socialMediaData'));
    }

    public function lang($local): RedirectResponse
    {
        $direction = $this->businessSettings->where('key_name', 'site_direction')->first();
        $direction = $direction->live_values ?? 'ltr';
        $language = $this->businessSettings->where('key_name', 'system_language')->first();
        foreach ($language?->live_values as $key => $data) {
            if ($data['code'] == $local) {
                $direction = isset($data['direction']) ? $data['direction'] : 'ltr';
            }
        }
        session()->forget('landing_language_settings');
        landing_language_load();
        session()->put('landing_site_direction', $direction);
        session()->put('landing_local', $local);
        return redirect()->back();
    }

    public function sendEmail(Request $request)
{
    $data = $request->validate([
        'category' => 'required',
        'name' => 'required',
        'email' => 'required|email',
        'subject' => 'required',
        'phone' => 'required',
        'message' => 'required',
        'attachment' => 'nullable|file|max:10240', // Max 10MB
    ]);
    $emailConfig = $this->businessSettings->where('key_name', 'email_config')->first();

    config([
        'mail.mailers.smtp.host' => $emailConfig->live_values['host'],
        'mail.mailers.smtp.port' => $emailConfig->live_values['port'],
        'mail.mailers.smtp.username' => $emailConfig->live_values['user_name'],
        'mail.mailers.smtp.password' => $emailConfig->live_values['password'],
        'mail.from.address' => $emailConfig->live_values['email_id'],
        'mail.from.name' => $emailConfig->live_values['mailer_name'],
    ]);

    Mail::to('Support@mlzom.com')->send(new ContactFormMail($data));

    return redirect()->back()->with('success', 'Your message has been sent successfully!');
}
}



