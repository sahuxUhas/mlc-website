<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
import App\Models\Advertisement;

/** বিজ্ঞাপনের ক্লিক ট্র্যাকিং করে টার্গেট URL এ পাঠায় */
class AdClickController extends Controller
{
    public function redirect(Advertisement $ad)
    {
        $ad->increment('clicks');

        if (empty($ad->link)) {
            return back();
        }

        // ওপেন রিডাইরেক্ট রোধে শুধু http(s) অনুমোদিত
        if (! preg_match('~^https?://~i', $ad->link)) {
            abort(400);
        }

        return redirect()->away($ad->link);
    }
}
