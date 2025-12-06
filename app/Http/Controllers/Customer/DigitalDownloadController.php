<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DigitalDownload;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DigitalDownloadController extends Controller
{
    public function download(Order $order): StreamedResponse
    {
        $this->authorize('download', $order);

        if (!$order->canDownloadDigitalProduct()) {
            abort(403, 'Download not available.');
        }

        $listing = $order->listing;

        if (!$listing->hasDigitalFile()) {
            abort(404, 'Digital file not found.');
        }

        DigitalDownload::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'downloaded_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return Storage::disk('local')->download(
            $listing->digital_file_path,
            $listing->digital_file_name
        );
    }
}
