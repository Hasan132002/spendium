<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(): Renderable
    {
        $notifications = Auth::user()->notifications()->paginate(20);

        return view('backend.pages.notifications.index', compact('notifications'));
    }

    public function latest(): JsonResponse
    {
        $user = Auth::user();

        $recent = $user->notifications()->limit(10)->get()->map(function ($n) {
            $data = $n->data ?? [];
            return [
                'id'      => $n->id,
                'type'    => $data['type'] ?? $n->type,
                'title'   => $data['title'] ?? 'Notification',
                'message' => $data['message'] ?? '',
                'icon'    => $data['icon'] ?? 'bi-bell',
                'url'     => $data['url'] ?? '#',
                'read'    => $n->read_at !== null,
                'ago'     => $n->created_at?->diffForHumans(),
            ];
        });

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'items'        => $recent,
        ]);
    }

    public function markRead(string $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();

        if ($notification && $notification->read_at === null) {
            $notification->markAsRead();
        }

        if ($notification && isset($notification->data['url'])) {
            return redirect($notification->data['url']);
        }

        return redirect()->route('admin.notifications.index');
    }

    public function markAllRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(string $id): RedirectResponse
    {
        Auth::user()->notifications()->where('id', $id)->delete();

        return back()->with('success', 'Notification deleted.');
    }
}
