<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Page;

/** স্ট্যাটিক পেজ — About / Contact / Advertise / Privacy ইত্যাদি */
class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::visible()->where('slug', $slug)->firstOrFail();

        return view('public.page', compact('page'));
    }

    public function about()     { return $this->show('about'); }
    public function contact()   { return $this->show('contact'); }
    public function advertise() { return $this->show('advertise'); }
    public function submitReport() { return $this->show('submit-report'); }
}
