<?php

namespace Modules\AddonModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use function response;


class AddonController extends Controller
{
    /**
     * @return Factory|View|Application
     */
    public function index(): Factory|View|Application
    {
        $dir = 'Modules';
        $directories = self::getDirectories($dir);

        $addons = [];
        foreach ($directories as $directory) {
            $subDirectories = self::getDirectories('Modules/' . $directory);
            if (in_array('Addon', $subDirectories)) {
                $addons[] = 'Modules/' . $directory;
            }
        }

        $publishedStatus = 0;
        $paymentPublishedStatus = config('get_payment_publish_status');
        if (isset($paymentPublishedStatus[0]['is_published'])) {
            $publishedStatus = $paymentPublishedStatus[0]['is_published'];
        }

        return view('addonmodule::addon.index', compact('addons', 'publishedStatus'));
    }

    /**
     * @param Request $request
     * @return JsonResponse|int
     */
    public function publish(Request $request): JsonResponse|int
    {

        $fullData = include($request['path'] . '/Addon/info.php');
        $path = $request['path'];
        $addonName = $fullData['name'];

        if ($fullData['purchase_code'] == null || $fullData['username'] == null) {
            return response()->json([
                'flag' => 'inactive',
                'view' => view('addonmodule::addon.partials.activation-modal-data', compact('fullData', 'path', 'addonName'))->render(),
            ]);
        }
        $fullData['is_published'] = $fullData['is_published'] ? 0 : 1;

        $str = "<?php return " . var_export($fullData, true) . ";";
        file_put_contents(base_path($request['path'] . '/Addon/info.php'), $str);

        return response()->json([
            'status' => 'success',
            'message' => translate('status_updated_successfully')
        ]);
    }

    /**
     * @param Request $request
     * @return Redirector|RedirectResponse|Application
     */
    public function activation(Request $request): Redirector|RedirectResponse|Application
    {
        $remove = ["http://", "https://", "www."];
        $url = str_replace($remove, "", url('/'));
        $full_data = include($request['path'] . '/Addon/info.php');

		$full_data['is_published'] = 1;
		$full_data['username'] = $request['username'];
		$full_data['purchase_code'] = $request['purchase_code'];
		$str = "<?php return " . var_export($full_data, true) . ";";
		file_put_contents(base_path($request['path'] . '/Addon/info.php'), $str);

		Toastr::success(translate('activated_successfully'));
		return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_upload' => 'required|mimes:zip'
        ]);

        if ($validator->errors()->count() > 0) {
            $error = error_processor($validator);
            return response()->json(['status' => 'error', 'message' => $error[0]['message']]);
        }

        if (File::exists(base_path('Modules/') . '/' . 'Gateways')) {
            Toastr::warning(translate('already_installed!'));
            $message = translate('already_installed');
            $status = 'error';
            return response()->json([
                'status' => $status,
                'message' => $message
            ]);
        }

        $file = $request->file('file_upload');
        $fileName = $file->getClientOriginalName();
        $tempPath = $file->storeAs('temp', $fileName);
        $zip = new \ZipArchive();
        if ($zip->open(storage_path('app/' . $tempPath)) === TRUE) {
            $extractPath = base_path('Modules/');
            $zip->extractTo($extractPath);
            $zip->close();
            if (File::exists($extractPath . '/' . explode('.', $fileName)[0] . '/Addon/info.php')) {
                File::chmod($extractPath . '/' . explode('.', $fileName)[0] . '/Addon', 0777);
                Toastr::success(translate('file_upload_successfully!'));
                $status = 'success';
                $message = translate('file_upload_successfully!');
            } else {
                File::deleteDirectory($extractPath . '/' . explode('.', $fileName)[0]);
                $status = 'error';
                $message = translate('invalid_file!');
            }
        } else {
            $status = 'error';
            $message = translate('file_upload_fail!');
        }

        Storage::delete($tempPath);

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAddon(Request $request): JsonResponse
    {
        $path = $request->path;
        $fullPath = base_path($path);

        if (File::deleteDirectory($fullPath)) {
            return response()->json([
                'status' => 'success',
                'message' => translate('file_delete_successfully')
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => translate('file_delete_fail')
            ]);
        }

    }

    /**
     * @param string $path
     * @return array
     */
    function getDirectories(string $path): array
    {
        $directories = [];
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item == '..' || $item == '.')
                continue;
            if (is_dir($path . '/' . $item))
                $directories[] = $item;
        }
        return $directories;
    }
}
