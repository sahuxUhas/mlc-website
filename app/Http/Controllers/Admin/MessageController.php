<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

/** যোগাযোগ বার্তা ইনবক্স */
class MessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::newest();
        if ($request->query('unread')) { $query->unread(); }
        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w->where('name', 'like', '%'.$q.'%')->orWhere('email', 'like', '%'.$q.'%')->orWhere('subject', 'like', '%'.$q.'%'));
        }

        return view('admin.news.messages', [
            'messages' => $query->paginate(20)->withQueryString(),
            'unread'   => ContactMessage::unread()->count(),
        ]);
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();
        return back()->with('success', 'বার্তা মুছে ফেলা হয়েছে।');
    }
}
