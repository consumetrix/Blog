<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityController extends Controller
{
    public function __invoke(Request $request): View|StreamedResponse
    {
        $filters = $request->validate([
            'person' => ['nullable', 'regex:/^(system|\d+)$/'],
            'area' => ['nullable', 'in:'.implode(',', array_keys(ActivityLog::AREAS))],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $query = ActivityLog::query()->filtered($filters)->with('user')->latest('id');

        if ($request->query('export') === 'csv') {
            return $this->csv($query);
        }

        $entries = $query->paginate(50)->withQueryString();

        return view('dashboard.activity', [
            'entries' => $entries,
            'existing' => ActivityLog::existing($entries),
            'filters' => $filters,
            'people' => User::query()->orderBy('name')->get(['id', 'name']),
            'areas' => array_map(fn (array $area) => $area['label'], ActivityLog::AREAS),
        ]);
    }

    private function csv($query): StreamedResponse
    {
        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['When (GMT)', 'Person', 'Action', 'Subject', 'Detail', 'IP address']);
            $query->lazyByIdDesc(500)->each(function (ActivityLog $entry) use ($out) {
                fputcsv($out, array_map(fn (?string $value) => $this->cell($value), [
                    $entry->created_at?->format('Y-m-d H:i:s'),
                    $entry->user?->name ?? ($entry->user_id ? 'Deleted user' : 'System'),
                    $entry->label(),
                    $entry->subject,
                    $entry->detail(),
                    $entry->ip_address,
                ]));
            });
            fclose($out);
        }, 'activity-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Stops a spreadsheet from treating a cell (for example a page title) as a formula.
     */
    private function cell(?string $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }
}
