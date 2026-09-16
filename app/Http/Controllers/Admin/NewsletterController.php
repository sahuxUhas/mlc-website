<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

/** নিউজলেটার সাবস্ক্রাইবার তালিকা */
class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsletterSubscriber::latest();
        if ($q = trim((string) $request->query('q'))) { $query->where('email', 'like', '%'.$q.'%'); }

        return view('admin.news.newsletter', [
            'subscribers' => $query->paginate(30)->withQueryString(),
            'total'       => NewsletterSubscriber::count(),
            'active'      => NewsletterSubscriber::where('is_active', true)->count(),
        ]);
    }

    public function destroy(NewsletterSubscriber $newsletter)
    {
        $newsletter->delete();
        return back()->with('success', 'সাবস্ক্রাইবার মুছে ফেলা হয়েছে।');
    }
}
