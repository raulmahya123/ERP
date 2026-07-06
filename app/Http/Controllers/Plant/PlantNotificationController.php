<?php

namespace App\Http\Controllers\Plant;

use App\Http\Controllers\Controller;
use App\Models\Plant\PlantNotification;
use App\Models\Site;
use Illuminate\Http\Request;

class PlantNotificationController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = PlantNotification::query()
            ->with(['site', 'asset', 'recipient'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('notification_type'), fn($qq) => $qq->where('notification_type', $request->notification_type))
            ->when($request->filled('priority'), fn($qq) => $qq->where('priority', $request->priority))
            ->when($request->filled('is_read'), fn($qq) => $qq->where('is_read', $request->boolean('is_read')))
            ->when($request->filled('recipient_id'), fn($qq) => $qq->where('recipient_id', $request->recipient_id))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $notificationTypes = ['maintenance_due' => 'Maintenance Due', 'breakdown' => 'Breakdown', 'work_order' => 'Work Order', 'system' => 'System'];
        $priorities = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'];

        return view('admin.plant.plant-notifications.index', compact('items','sites','notificationTypes','priorities','siteId'));
    }
}
