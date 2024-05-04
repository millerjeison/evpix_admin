<?php

namespace App\Http\Controllers;

use Throwable;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Parents;
use App\Models\Teacher;
use App\Models\Students;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()->can('notification-list')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $users = User::where('deleted_at',null)->get();
        return view('notification.index', compact('users'));
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
       if (!Auth::user()->can('notification-create')) {
        $response = array(
            'message' => trans('no_permission_message')
        );
        return redirect(route('home'))->withErrors($response);
        }
        $validator = Validator::make($request->all(),[
            'send_to' => 'required',
            'title' => 'required',
            'message' => 'required'
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try{
            $send_to = $request->send_to;
            $type = "custom";
            $image = null;
            $userinfo = null;

            $notification = new Notification();
            $notification->send_to = $send_to;
            $notification->title = $request->title;
            $notification->message = $request->message;
            $notification->type = $type;
            $notification->date = Carbon::now();
            $notification->is_custom = 1;
            if($send_to == 1)
            {
                $user = User::select('id')->where('id','!=',0)->get()->pluck('id');
            }
            elseif($send_to == 2){
                $user=[];
                foreach($request->user_id as $user_id)
                {
                    if($user_id != 0)
                    {
                        $user[] = $user_id;
                    }

                }
            }
            elseif($send_to == 3){
                $user = Students::select('user_id')->where('user_id','!=',0)->get()->pluck('user_id');
            }
            elseif($send_to == 4){
                $user = Parents::select('user_id')->where('user_id','!=',0)->get()->pluck('user_id');

            }
            else{
                $user = Teacher::select('user_id')->where('user_id','!=',0)->get()->pluck('user_id');
            }

            if ($request->has('image')) {

                $notification_image = $request->file('image');

                // made file name with combination of current time
                $file_name = time() . '-' . $notification_image->getClientOriginalName();

                //made file path to store in database
                $file_path = 'notifications/' . $file_name;

                //resized image
                resizeImage($notification_image);

                //stored image to storage/public/parents folder
                $destinationPath = storage_path('app/public/notifications');
                $notification_image->move($destinationPath, $file_name);

                //saved file path to database
                $notification->image = $file_path;

                $image = asset('storage/'.$file_path);
            }

            $title = $request->title;
            $body =  $request->message;
            $type =  $type;
            $notification->save();
            foreach($user as $data)
            {
                $user_notification = new UserNotification();
                $user_notification->notification_id = $notification->id;
                $user_notification->user_id = $data;
                $user_notification->save();
            }

            send_notification($user, $title, $body, $type, $image, $userinfo);

            $response = array(
                'error' => false,
                'message' => trans('notification_sent_successfully')
            );

        }catch(Throwable $e){
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'data' => $e
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
        if (!Auth::user()->can('notification-list')) {
            $response = array(
                'error' => true,
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


        $sql = Notification::where('id','!=',0)->where('is_custom',1);
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")
                ->orwhere('name', 'LIKE', "%$search%")
                ->orwhere('title','LIKE', "%$search%")
                ->orwhere('date','LIKE',"%$search%");
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

            $operate = '<a href='.route('notifications.destroy',$row->id).' class="btn btn-xs btn-gradient-danger btn-rounded btn-icon delete-form" data-id=' . $row->id . '><i class="fa fa-trash"></i></a>';

            $tempRow['id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['title'] = $row->title;
            $tempRow['type'] = $row->type;
            $tempRow['message'] = $row->message;
            $tempRow['image'] = $row->image;
            $tempRow['date'] = $row->date;
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('notification-delete')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        try {
            $data = Notification::findOrFail($id);
            $data->delete();
            $response = [
                'error' => false,
                'message' => trans('data_delete_successfully')
            ];
        } catch (Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'data' => $e
            );
        }
        return response()->json($response);
    }
}
