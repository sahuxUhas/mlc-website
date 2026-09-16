<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

/** অ্যাক্টিভিটি লগ — Login/Logout/Create/Edit/Publish/Delete/Approve/Settings */
class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user:id,name')->newest();

        if ($module = $request->query('module')) { $query->where('module', $module); }
        if ($action = $request->query('action')) { $query->where('action', $action); }
        if ($userId = $request->integer('user'))  { $query->where('user_id', $userId); }
        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w->where('description', 'like', '%'.$q.'%')->orWhere('user_name', 'like', '%'.$q.'%'));
        }
        if ($from = $request->date('from')) { $query->where('created_at', '>=', $from); }
        if ($to = $request->date('to'))     { $query->where('created_at', '<=', $to->endOfDay()); }

        return view('admin.activity.index', [
            'logs'    => $query->paginate(30)->withQueryString(),
            'modules' => ActivityLog::distinct()->orderBy('module')->pluck('module'),
            'actions' => ActivityLog::distinct()->orderBy('action')->pluck('action'),
            'users'   => \App\Models\User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function clear(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $count = ActivityLog::count();
        ActivityLog::truncate();
        ActivityLogger::log('clear', 'activity', 'সমস্ত অ্যাক্টিভিটি লগ মুছে ফেলা হয়েছে ('.bn_num($count).'টি)');

        return back()->with('success', 'অ্যাক্টিভিটি লগ পরিষ্কার করা হয়েছে।');
    }
}
