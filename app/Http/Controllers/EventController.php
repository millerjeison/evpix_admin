<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Event;
use App\Models\EventTiming;
use Illuminate\Http\Request;
use App\Models\MultipleEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!Auth::user()->can('event-list'))
        {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        return view('events.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        if(!Auth::user()->can('event-create'))
        {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $validator = Validator::make($request->all(), [
            'title'  => 'required',
            'image' => 'nullable|mimes:png,jpg, jpeg',
            'event_type' => 'required|in:single,multiple',
            'date' => 'nullable|required_if:event_type,single',
            'time' => ['nullable' ,'regex:/^(?:[01]\d|2[0-3]):(?:[0-5]\d):(?:[0-5]\d)\s*-\s*(?:[01]\d|2[0-3]):(?:[0-5]\d):(?:[0-5]\d)$/'],

            'date_range' => 'nullable|required_if:event_type,multiple',
            'events' => 'nullable|array',
            'events.*.title' => 'nullable|required_if:event_type,multiple',
            'events.*.date' => 'nullable|required_if:event_type,multiple',
            'events.*.timerange' => ['nullable','required_if:event_type,multiple','regex:/^(?:[01]\d|2[0-3]):(?:[0-5]\d):(?:[0-5]\d)\s*-\s*(?:[01]\d|2[0-3]):(?:[0-5]\d):(?:[0-5]\d)$/']
        ]);
        if($validator->fails())
        {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $event = new Event();
            if($request->event_type == 'multiple')
            {
                $dateRange = explode(' - ', $request->date_range);
                $startDate = $dateRange[0];
                $endDate = $dateRange[1];

                foreach ($request->events as $key => $value) {
                    if(isset($value['date']) && $value['date']) {

                        $date = Carbon::parse($value['date']);
                        $startDate = Carbon::parse($startDate); // Convert string date to Carbon instance
                        $endDate = Carbon::parse($endDate);

                        if (!$date->between($startDate,$endDate) ){

                            $response = array(
                                'error' => true,
                                'message' =>'Date must be within the specified date range.'
                            );
                        return response()->json($response);
                        }
                    }
                }
                $event->title = $request->title;
                $event->start_date = date('Y-m-d', strtotime($startDate));
                $event->end_date = date('Y-m-d', strtotime($endDate));

                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    // made file name with combination of current time
                    $file_name = time() . '-' . $image->getClientOriginalName();
                    //made file path to store in database
                    $file_path = 'events/' . $file_name;
                    //resized image
                    resizeImage($image);
                    //stored image to storage/public/teachers folder
                    $destinationPath = storage_path('app/public/events');
                    $image->move($destinationPath, $file_name);

                    $event->image = $file_path;
                }

                $event->type = $request->event_type;

                $event->description = $request->description;
                $event->save();

                foreach ($request->events as $key => $value) {
                    $timeRange = explode(' - ', $value['timerange']);
                    if($timeRange[0] && $timeRange[1])
                    {
                        $startTime = $timeRange[0];
                        $endTime = $timeRange[1];
                    }

                    $multiEvent = new MultipleEvent();
                    $multiEvent->event_id = $event->id;
                    $multiEvent->title = $value['title'];
                    $multiEvent->date = date('Y-m-d', strtotime($value['date']));
                    $multiEvent->start_time =  $startTime;
                    $multiEvent->end_time = $endTime;
                    $multiEvent->description = $value['description'];
                    $multiEvent->save();
                }
            }
            else{

                $event->title = $request->title;
                $event->start_date = date('Y-m-d', strtotime($request->date));

                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    // made file name with combination of current time
                    $file_name = time() . '-' . $image->getClientOriginalName();
                    //made file path to store in database
                    $file_path = 'events/' . $file_name;
                    //resized image
                    resizeImage($image);
                    //stored image to storage/public/teachers folder
                    $destinationPath = storage_path('app/public/events');
                    $image->move($destinationPath, $file_name);

                    $event->image = $file_path;
                }

                $event->type = $request->event_type;
                $event->description = $request->description;

                if($request->time)
                {
                    $timeRange = explode(' - ', $request->time);
                    $startTime = $timeRange[0];
                    $endTime = $timeRange[1];

                    $event->start_time =  $startTime;
                    $event->end_time =  $endTime;

                }
                $event->save();

            }

            $response = array(
                'error' => false,
                'message' => trans('data_store_successfully')
            );

        } catch (\Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred')
            );
        }
        return response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        if (!Auth::user()->can('event-list')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return response()->json($response);
        }
        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'DESC';
        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];
        if (isset($_GET['sort']))
            $sort = $_GET['sort'];
        if (isset($_GET['order']))
            $order = $_GET['order'];

        $sql = Event::with('multipleEvent')->where('id','!=', 0);
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('title', 'LIKE', "%$search%")->orwhere('date', 'LIKE', "%$search%");
        }
        $total = $sql->count();

        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $no=1;
        foreach ($res as $row) {
            $operate = '<a href='.route('events.update',$row->id).' class="btn btn-xs btn-gradient-primary btn-rounded btn-icon edit-data" data-id=' . $row->id . ' title="Edit" data-toggle="modal" data-target="#editModal"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;';
            $operate .= '<a href='.route('events.destroy',$row->id).' class="btn btn-xs btn-gradient-danger btn-rounded btn-icon delete-form" data-id=' . $row->id . '><i class="fa fa-trash"></i></a>';

            $tempRow['id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['title'] = $row->title;
            $tempRow['type'] = $row->type;
            $tempRow['image'] = $row->image;

            if($row->start_time){
                $tempRow['time'] = $row->start_time . ' to ' . $row->end_time;
            }else{
                $tempRow['time'] = null;
            }

            $tempRow['description'] = $row->description;

            if($row->end_date) {
                $tempRow['date'] = date('d-m-Y', strtotime($row->start_date)). ' to ' .date('d-m-Y', strtotime($row->end_date));
            } else {
                $tempRow['date'] = date('d-m-Y', strtotime($row->start_date));
            }

            if($row->type == 'multiple') {
                $tempRow['multipleEvents'] = $row->multipleEvent;
                $count = 1;
                $descriptions = [];
                $eventDetails = "<table class='event-table'><tr><th>No.</th><th>Title</th><th data-width='113'>Date</th><th>Timing</th><th>Description</th></tr>";
                if($row->multipleEvent->isNotEmpty())
                {
                    foreach($row->multipleEvent as $event) {
                        if($event){
                            $date = date('d-m-Y', strtotime($event->date));
                            $eventDetails .= "<tr><td>$count</td><td>$event->title</td><td>$date</td><td>$event->start_time to $event->end_time</td><td>$event->description</td></tr>";
                            $count++;
                        }else{
                            $eventDetails = [];
                        }

                    }
                    $descriptions[] = $eventDetails;
                    $descriptions[] .= "</table>";
                    $tempRow['schedule'] = implode('<br>', $descriptions);
                }else{
                    $tempRow['schedule'] = null;
                }


                $tempRow['start_time'] = null;
                $tempRow['end_time'] = null;
            }else {
                $tempRow['schedule'] = null;
            }

            $tempRow['operate'] = $operate;
            $tempRow['created_at'] = $row->created_at;
            $tempRow['updated_at'] = $row->updated_at;
            $rows[] = $tempRow;

        }
        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if(!Auth::user()->can('event-edit'))
        {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $validator = Validator::make($request->all(), [
            'title'  => 'required',
            'image' => 'nullable|mimes:png,jpg, jpeg',
            'edit_event_type' => 'required|in:single,multiple',
            'edit_date' => 'nullable|required_if:edit_event_type,single',
            'edit_time' => ['nullable' ,'regex:/^(?:[01]\d|2[0-3]):(?:[0-5]\d):(?:[0-5]\d)\s*-\s*(?:[01]\d|2[0-3]):(?:[0-5]\d):(?:[0-5]\d)$/'],

            'edit_events' => 'nullable|array',
            'edit_events.*.title' => 'nullable|required_if:edit_event_type,multiple',
            'edit_events.*.date' => 'nullable|required_if:edit_event_type,multiple',
            'edit_events.*.timerange' => ['nullable','required_if:edit_event_type,multiple','regex:/^(?:[01]\d|2[0-3]):(?:[0-5]\d):(?:[0-5]\d)\s*-\s*(?:[01]\d|2[0-3]):(?:[0-5]\d):(?:[0-5]\d)$/'],

            'events' => 'nullable|array',
            'events.*.title' => 'nullable|required_if:edit_event_type,multiple',
            'events.*.date' => 'nullable|required_if:edit_event_type,multiple',
            'events.*.timerange' => ['nullable','required_if:edit_event_type,multiple','regex:/^(?:[01]\d|2[0-3]):(?:[0-5]\d):(?:[0-5]\d)\s*-\s*(?:[01]\d|2[0-3]):(?:[0-5]\d):(?:[0-5]\d)$/']

        ]);
        if($validator->fails())
        {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $event = Event::findOrFail($request->edit_id);

            if ($request->edit_event_type == 'multiple') {
                $dateRange = explode(' - ', $request->date_range);
                $startDate = $dateRange[0];
                $endDate = $dateRange[1];

                if ($request->edit_events) {

                    foreach ($request->edit_events as $key => $value) {
                        if(isset($value['date']) && $value['date']) {

                            $date = Carbon::parse($value['date']);
                            $startDate = Carbon::parse($startDate); // Convert string date to Carbon instance
                            $endDate = Carbon::parse($endDate);

                            if (!$date->between($startDate,$endDate) ){

                                $response = array(
                                    'error' => true,
                                    'message' =>'Date must be within the specified date range.'
                                );
                            return response()->json($response);
                            }
                        }
                        if ($value['id']) {
                            $multiEvent = MultipleEvent::findOrFail($value['id']);
                            if($multiEvent)
                            {
                                $timeRange = explode(' - ', $value['timerange']);
                                if($timeRange[0] && $timeRange[1])
                                {
                                    $startTime = $timeRange[0];
                                    $endTime = $timeRange[1];
                                }
                                $multiEvent->event_id = $event->id;
                                $multiEvent->title = $value['title'];
                                $multiEvent->date = date('Y-m-d', strtotime($value['date']));
                                $multiEvent->start_time =  $startTime;
                                $multiEvent->end_time = $endTime;
                                $multiEvent->description = $value['description'];
                                $multiEvent->save();
                            }
                        }else {
                            $timeRange = explode(' - ', $value['timerange']);
                            if($timeRange[0] && $timeRange[1])
                            {
                                $startTime = $timeRange[0];
                                $endTime = $timeRange[1];
                            }

                            $multiEvent = new MultipleEvent();
                            $multiEvent->event_id = $event->id;
                            $multiEvent->title = $value['title'];
                            $multiEvent->date = date('Y-m-d', strtotime($value['date']));
                            $multiEvent->start_time =  $startTime;
                            $multiEvent->end_time = $endTime;
                            $multiEvent->description = $value['description'];
                            $multiEvent->save();
                        }
                    }
                }

                if($request->events)
                {
                    foreach ($request->events as $key => $value) {

                        if(isset($value['date']) && $value['date']) {

                            $date = Carbon::parse($value['date']);
                            $startDate = Carbon::parse($startDate); // Convert string date to Carbon instance
                            $endDate = Carbon::parse($endDate);

                            if (!$date->between($startDate,$endDate) ){

                                $response = array(
                                    'error' => true,
                                    'message' =>'Date must be within the specified date range.'
                                );
                            return response()->json($response);
                            }
                        }

                        $timeRange = explode(' - ', $value['timerange']);
                        if($timeRange[0] && $timeRange[1])
                        {
                            $startTime = $timeRange[0];
                            $endTime = $timeRange[1];
                        }

                        $multiEvent = new MultipleEvent();
                        $multiEvent->event_id = $event->id;
                        $multiEvent->title = $value['title'];
                        $multiEvent->date = date('Y-m-d', strtotime($value['date']));
                        $multiEvent->start_time =  $startTime;
                        $multiEvent->end_time = $endTime;
                        $multiEvent->description = $value['description'];
                        $multiEvent->save();
                    }
                }

                $event->title = $request->title;
                $event->start_date = date('Y-m-d', strtotime($startDate));
                $event->end_date = date('Y-m-d', strtotime($endDate));

                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    // made file name with combination of current time
                    $file_name = time() . '-' . $image->getClientOriginalName();
                    // made file path to store in database
                    $file_path = 'events/' . $file_name;
                    // resized image
                    resizeImage($image);
                    // stored image to storage/public/teachers folder
                    $destinationPath = storage_path('app/public/events');
                    $image->move($destinationPath, $file_name);

                    $event->image = $file_path;
                }

                $event->type = $request->edit_event_type;
                $event->description = $request->description;
                $event->save();


            }
            else{
                if($request->edit_time)
                {
                    $timeRange = explode(' - ', $request->edit_time);
                    $startTime = $timeRange[0];
                    $endTime = $timeRange[1];

                    $event->start_time =  $startTime;
                    $event->end_time =  $endTime;
                }else{
                    $event->start_time =  null;
                    $event->end_time =  null;
                }
                $event->title = $request->title;
                $event->start_date = date('Y-m-d', strtotime($request->edit_date));
                $event->end_date = null;

                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    // made file name with combination of current time
                    $file_name = time() . '-' . $image->getClientOriginalName();
                    //made file path to store in database
                    $file_path = 'events/' . $file_name;
                    //resized image
                    resizeImage($image);
                    //stored image to storage/public/teachers folder
                    $destinationPath = storage_path('app/public/events');
                    $image->move($destinationPath, $file_name);

                    $event->image = $file_path;
                }

                $event->type = $request->edit_event_type;
                $event->description = $request->description;

                $event->save();
                $multiEvents = MultipleEvent::where('event_id',$event->id)->get();
                if($multiEvents)
                {
                    foreach ($multiEvents as $multiEvent) {
                        $multiEvent->delete();
                    }

                }
            }
            $response = [
                'error' => false,
                'message' => trans('data_update_successfully'),
            ];

        } catch (\Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred')
            );
        }
        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!Auth::user()->can('event-delete'))
        {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        try {
            $event = Event::find($id);
            $event->delete();
            $response = array(
                'error' => false,
                'message' => trans('data_delete_successfully')
            );
        } catch (\Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred')
            );
        }
        return response()->json($response);
    }
    public function deleteMultipleEvent($id)
    {
        if(!Auth::user()->can('event-delete'))
        {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        try {
            $multiEvent = MultipleEvent::find($id);
            $multiEvent->delete();
            $response = array(
                'error' => false,
                'message' => trans('data_delete_successfully')
            );
        } catch (\Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred')
            );
        }
        return response()->json($response);
    }
}
