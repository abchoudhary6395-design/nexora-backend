<?php

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Http\Requests\Work\StoreCalendarEventRequest;
use App\Models\CalendarEvent;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    use ApiResponse;

    /**
     * Returns events within [from, to] — the frontend's Calendar page
     * (src/pages/Calendar/Calendar.jsx) requests one month at a time.
     */
    public function index(Request $request)
    {
        $request->validate(['from' => 'required|date', 'to' => 'required|date']);

        $events = CalendarEvent::with(['creator', 'attendees'])
            ->whereBetween('starts_at', [$request->from, $request->to])
            ->orderBy('starts_at')
            ->get();

        return $this->success($events);
    }

    public function store(StoreCalendarEventRequest $request)
    {
        $event = CalendarEvent::create([...$request->safe()->except('attendee_ids'), 'created_by' => $request->user()->id]);

        if ($request->filled('attendee_ids')) {
            $event->attendees()->attach($request->attendee_ids);
        }

        return $this->success($event->load('attendees'), 'Event created', 201);
    }

    public function show(CalendarEvent $calendarEvent)
    {
        return $this->success($calendarEvent->load(['creator', 'attendees', 'eventable']));
    }

    public function update(Request $request, CalendarEvent $calendarEvent)
    {
        $this->authorize('update', $calendarEvent);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'type' => ['sometimes', 'in:meeting,call,deadline,reminder'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'all_day' => ['sometimes', 'boolean'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $calendarEvent->update($data);

        return $this->success($calendarEvent->fresh(), 'Event updated');
    }

    public function destroy(CalendarEvent $calendarEvent)
    {
        $this->authorize('delete', $calendarEvent);
        $calendarEvent->delete();

        return $this->success(null, 'Event deleted');
    }
}
