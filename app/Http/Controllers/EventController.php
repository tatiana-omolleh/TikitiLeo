<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        $events = Auth::user()->events;
        return view('events.index', compact('events'));
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'date' => 'required|date',
            'location' => 'required',
            'price' => 'required|numeric',
            'available_tickets' => 'required|integer',
            'category' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->storeAs('assets/img/events', $request->file('image')->getClientOriginalName(), 'public');
        }

        // Convert checkbox value to integer
        $data['installments_enabled'] = $request->has('installments_enabled') ? 1 : 0;
        $data['event_organizer_id'] = Auth::id();
        $event = new Event();
        $event->name = $request->input('name');
        // ... other properties ...
        $event->installment_dates = $request->input('installment_dates');
        $event->save();
        

        Auth::user()->events()->create($data);

        return redirect()->route('events.index');
    }

    public function edit(Event $event)
    {
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'date' => 'required|date',
            'location' => 'required',
            'price' => 'required|numeric',
            'available_tickets' => 'required|integer',
            'category' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->storeAs('assets/img/events', $request->file('image')->getClientOriginalName(), 'public');
        }

        // Convert checkbox value to integer
        $data['installments_enabled'] = $request->has('installments_enabled') ? 1 : 0;
        $data['event_organizer_id'] = Auth::id();

        $event->update($data);

        return redirect()->route('events.index');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index');
    }
    public function search(Request $request)
    {
        $query = Event::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        $events = $query->get();

        return view('events.search', compact('events'));
    }
}
