<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()->can('staff-list')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $roles = Role::where('custom_role',1)->orderBy('id','DESC')->paginate(5);
        return view('staff.index',compact('roles'));
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
        if (!Auth::user()->can('staff-create')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }

        $validator = Validator::make($request->all(),[
            'role_id' => 'required|numeric',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'gender' => 'required',
            'mobile' => 'required|numeric|regex:/^[0-9]{7,16}$/',
            'image' => 'mimes:jpeg,png,jpg|image|max:2048',
            'dob' => 'required',
            'address' => 'nullable',

        ], ['mobile.regex' => 'The mobile number must be a length of 7 to 15 digits.'
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $role = Role::findOrFail($request->role_id);

            $check_user = User::where('email',$request->email)->onlyTrashed();
            if($check_user->count())
            {
                $user_exists = $check_user->first();
                DB::table('users')->where('id',$user_exists->id)->update(['deleted_at' => null]);

                $user = User::findOrFail($user_exists->id);

                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    // made file name with combination of current time
                    $file_name = time() . '-' . $image->getClientOriginalName();
                    //made file path to store in database
                    $file_path = 'staff/' . $file_name;
                    //resized image
                    resizeImage($image);
                    //stored image to storage/public/teachers folder
                    $destinationPath = storage_path('app/public/staff');
                    $image->move($destinationPath, $file_name);

                    $user->image = $file_path;
                } else {
                    $user->image = "";
                }

                $staff_plain_text_password = str_replace('-', '', date('d-m-Y', strtotime($request->dob)));
                $user->password = Hash::make($staff_plain_text_password);

                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                $user->gender = $request->gender;
                $user->mobile = $request->mobile;
                $user->dob = date('Y-m-d', strtotime($request->dob));
                $user->current_address = $request->address;
                $user->save();

                $check_staff = DB::table('staffs')->where('user_id',$user->id)->whereNotNull('deleted_at');
                if($check_staff->count()){
                    $staff_exists = $check_staff->first();
                    DB::table('staffs')->where('id',$staff_exists->id)->update(['deleted_at' => null]);
                    $staff = Staff::findOrFail($staff_exists->id);
                    $staff->user_id = $user->id;
                    $staff->update();
                }

                $user->assignRole($role);


                $school_name = getSettings('school_name');
                $data = [
                    'subject' => 'Welcome to ' . $school_name['school_name'],
                    'name' => $request->first_name .' '. $request->last_name,
                    'email' => $request->email,
                    'password' => $staff_plain_text_password,
                    'school_name' => $school_name['school_name'],
                    'role' => $role->name

                ];

                Mail::send('staff.email', $data, function ($message) use ($data) {
                    $message->to($data['email'])->subject($data['subject']);
                });

            }
            else
            {
                $user = new User;

                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    // made file name with combination of current time
                    $file_name = time() . '-' . $image->getClientOriginalName();
                    //made file path to store in database
                    $file_path = 'staff/' . $file_name;
                    //resized image
                    resizeImage($image);
                    //stored image to storage/public/teachers folder
                    $destinationPath = storage_path('app/public/staff');
                    $image->move($destinationPath, $file_name);

                    $user->image = $file_path;
                } else {
                    $user->image = "";
                }

                $staff_plain_text_password = str_replace('-', '', date('d-m-Y', strtotime($request->dob)));
                $user->password = Hash::make($staff_plain_text_password);

                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                $user->gender = $request->gender;
                $user->mobile = $request->mobile;
                $user->dob = date('Y-m-d', strtotime($request->dob));
                $user->current_address = $request->address;
                $user->save();

                $user->assignRole($role);

                $staff = new Staff;
                $staff->user_id = $user->id;
                $staff->save();

                $school_name = getSettings('school_name');
                $data = [
                    'subject' => 'Welcome to ' . $school_name['school_name'],
                    'name' => $request->first_name .' '. $request->last_name,
                    'email' => $request->email,
                    'password' => $staff_plain_text_password,
                    'school_name' => $school_name['school_name'],
                    'role' => $role->name

                ];

                Mail::send('staff.email', $data, function ($message) use ($data) {
                    $message->to($data['email'])->subject($data['subject']);
                });

            }

            $response = array(
                'error' => false,
                'message' => trans('data_store_successfully'),
            );
        } catch (\Throwable $e) {
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
        if (!Auth::user()->can('staff-list')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }

        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            $sort = $_GET['sort'];
        if (isset($_GET['order']))
            $order = $_GET['order'];


        $sql = Staff::with('user','user.roles');
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")
                ->orwhere('user_id', 'LIKE', "%$search%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('first_name', 'LIKE', "%$search%")
                        ->orwhere('last_name', 'LIKE', "%$search%")
                        ->orwhere('gender', 'LIKE', "%$search%")
                        ->orwhere('email', 'LIKE', "%$search%")
                        ->orwhere('dob', 'LIKE', "%" . date('Y-m-d', strtotime($search)) . "%")
                        ->orwhere('current_address', 'LIKE', "%$search%");
                });
        }
        $total = $sql->count();

        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $no = 1;
        foreach ($res as $row) {
            $operate = '<a class="btn btn-xs btn-gradient-primary btn-rounded btn-icon edit-data" data-id=' . $row->id . ' data-url=' . url('staff') . ' title="Edit" data-toggle="modal" data-target="#editModal"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;';
            $operate .= '<a class="btn btn-xs btn-gradient-danger btn-rounded btn-icon deletedata" data-id=' . $row->id . ' data-user_id=' . $row->user_id . ' data-url=' . url('staff', $row->user_id) . ' title="Delete"><i class="fa fa-trash"></i></a>';

            $data = getSettings('date_formate');

            $tempRow['id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['user_id'] = $row->user_id;
            $tempRow['role_id'] = $row->user->roles->pluck('id')->implode(', ');
            $tempRow['roles'] = $row->user->roles->pluck('name')->implode(', ');
            $tempRow['first_name'] = $row->user->first_name;
            $tempRow['last_name'] = $row->user->last_name;
            $tempRow['gender'] = $row->user->gender;
            $tempRow['address'] = $row->user->current_address;
            $tempRow['email'] = $row->user->email;
            $tempRow['dob'] = date($data['date_formate'], strtotime($row->user->dob));
            $tempRow['mobile'] = $row->user->mobile;
            $tempRow['image'] =  $row->user->image;
            $tempRow['operate'] = $operate;
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
        // dd($request->all());
        if (!Auth::user()->can('staff-edit')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $validator = Validator::make($request->all(),[
            'role_id' => 'required|numeric',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $request->user_id,
            'gender' => 'required',
            'mobile' => 'required|numeric|regex:/^[0-9]{7,16}$/',
            'image' => 'mimes:jpeg,png,jpg|image|max:2048',
            'dob' => 'required',
            'address' => 'nullable',

        ], ['mobile.regex' => 'The mobile number must be a length of 7 to 15 digits.'
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $role = Role::findOrFail($request->role_id);

            $user = User::findorFail($request->user_id);

            if ($request->hasFile('image')) {

                if (Storage::disk('public')->exists($user->getRawOriginal('image'))) {
                    Storage::disk('public')->delete($user->getRawOriginal('image'));
                }

                $image = $request->file('image');
                // made file name with combination of current time
                $file_name = time() . '-' . $image->getClientOriginalName();
                //made file path to store in database
                $file_path = 'staff/' . $file_name;
                //resized image
                resizeImage($image);
                //stored image to storage/public/teachers folder
                $destinationPath = storage_path('app/public/staff');
                $image->move($destinationPath, $file_name);

                $user->image = $file_path;
            }

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->gender = $request->gender;
            $user->mobile = $request->mobile;
            $user->dob = date('Y-m-d', strtotime($request->dob));
            $user->current_address = $request->address;
            $user->save();

            $user->syncRoles($role);

            $response = array(
                'error' => false,
                'message' => trans('data_store_successfully'),
            );
        } catch (\Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'data' => $e
                
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
        if (!Auth::user()->can('staff-delete')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        try {
            $user = User::find($id);
            if (Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
            }
            $user->delete();

            $staff = Staff::where('user_id', $id);
            $staff->delete();

            $response = [
                'error' => false,
                'message' => trans('data_delete_successfully')
            ];
        }  catch (\Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred')
            );
        }
        return response()->json($response);
    }
}
