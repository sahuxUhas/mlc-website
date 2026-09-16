<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsReport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** পাঠকের প্রতিবেদন পর্যালোচনা */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsReport::with('post')->newest();
        if ($status = $request->query('status')) { $query->where('status', $status); }

        return view('admin.news.reports', ['reports' => $query->paginate(20)->withQueryString()]);
    }

    public function update(Request $request, NewsReport $report)
    {
        $data = $request->validate([
            'status'     => ['required', Rule::in(array_keys(NewsReport::STATUSES))],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $report->update($data);

        return back()->with('success', 'প্রতিবেদনের অবস্থা হালনাগাদ হয়েছে।');
    }

    public function destroy(NewsReport $report)
    {
        $report->delete();
        return back()->with('success', 'প্রতিবেদন মুছে ফেলা হয়েছে।');
    }
}
