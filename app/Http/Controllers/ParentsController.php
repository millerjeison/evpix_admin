<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\User;
use App\Models\Parents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ParentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()->can('parents-list')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        return view('parents.index');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    //    public function store(Request $request) {
    //        if (!Auth::user()->can('parents-create') || !Auth::user()->can('parents-edit')) {
    //            $response = array(
    //                'error' => true,
    //                'message' => trans('no_permission_message')
    //            );
    //            return response()->json($response);
    //        }
    //        $request->validate([
    //            'first_name' => 'required',
    //            'last_name' => 'required',
    //            'gender' => 'required',
    //            'email' => 'required|unique:users,email',
    //            'mobile' => 'required',
    //            'dob' => 'required',
    //        ]);
    //        try {
    //
    //            if (isset($request->user_id) && $request->user_id != '') {
    //                $user = User::find($request->user_id);
    //                if ($request->hasFile('image')) {
    //                    if ($user->image != "" && Storage::disk('public')->exists($user->image)) {
    //                        Storage::disk('public')->delete($user->image);
    //                    }
    //                    $user->image = $request->file('image')->store('parents', 'public');
    //                }
    //            } else {
    //                $user = new User();
    //                if ($request->hasFile('image')) {
    //                    $user->image = $request->file('image')->store('parents', 'public');
    //                } else {
    //                    $user->image = "";
    //                }
    //                $user->password = Hash::make('parents');
    //            }
    //            $user->first_name = $request->first_name;
    //            $user->last_name = $request->last_name;
    //            $user->gender = $request->gender;
    //            $user->current_address = $request->current_address;
    //            $user->permanent_address = $request->permanent_address;
    //            $user->email = $request->email;
    //            $user->mobile = $request->mobile;
    //            $user->dob = date('Y-m-d', strtotime($request->dob));
    //            $user->save();
    //
    //            if (isset($request->id) && $request->id != '') {
    //                $parents = Parents::find($request->id);
    //            } else {
    //                $parents = new Parents();
    //            }
    //            $parents->user_id = $user->id;
    //            $parents->save();
    //
    //            $response = [
    //                'error' => false,
    //                'message' => trans('data_store_successfully')
    //            ];
    //        } catch (Throwable $e) {
    //            $response = array(
    //                'error' => true,
    //                'message' => trans('error_occurred'),
    //                'data' => $e
    //            );
    //        }
    //        return response()->json($response);
    //    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        if (!Auth::user()->can('parents-list')) {
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

        $sql = Parents::with('user:id,current_address,permanent_address');
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")
                ->orwhere('first_name', 'LIKE', "%$search%")
                ->orwhere('last_name', 'LIKE', "%$search%")
                ->orwhere('gender', 'LIKE', "%$search%")
                ->orwhere('email', 'LIKE', "%$search%")
                ->orwhere('mobile', 'LIKE', "%$search%")
                ->orwhere('occupation', 'LIKE', "%$search%")
                ->orwhere('dob', 'LIKE', "%" . $search . "%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('id', 'LIKE', "%$search%")
                        ->orwhere('current_address', 'LIKE', "%$search%")
                        ->orwhere('permanent_address', 'LIKE', "%$search%");
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
            $operate = '<a class="btn btn-xs btn-gradient-primary btn-rounded btn-icon editdata" data-id=' . $row->id . ' data-url=' . url('parents') . ' title="Edit" data-toggle="modal" data-target="#editModal"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;';

            $data = getSettings('date_formate');

            $tempRow['id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['user_id'] = $row->user_id;
            $tempRow['first_name'] = $row->first_name;
            $tempRow['last_name'] = $row->last_name;
            $tempRow['gender'] = $row->gender;
            $tempRow['email'] = $row->email;
            $tempRow['dob'] = date($data['date_formate'], strtotime($row->dob));
            $tempRow['mobile'] = $row->mobile;
            $tempRow['occupation'] = $row->occupation;
            if ($row->user) {
                $tempRow['current_address'] = $row->user->current_address;
                $tempRow['permanent_address'] = $row->user->permanent_address;
            }
            $tempRow['image'] = '<img src="' . $row->image . '" onerror="onErrorImage(event)">';
            $tempRow['image'] =  $row->image;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('parents-create') || !Auth::user()->can('parents-edit')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message')
            );
            return response()->json($response);
        }
        $request->validate([
            'edit_id' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'email' => 'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|unique:parents,email,' . $id,
            'mobile' => 'required',
            'dob' => 'required',
        ]);
        try {
            $parents = Parents::findOrFail($id);

            //checks the unique email in user tabel
            $validator = Validator::make($request->all(), [
                'email' => 'required|unique:users,email,' . $parents->user_id,
            ]);
            if ($validator->fails()) {
                $response = array(
                    'error' => true,
                    'message' => $validator->errors()->first()
                );
                return response()->json($response);
            }

            if ($parents->user) {
                $user = $parents->user;
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->gender = $request->gender;
                $user->current_address = $request->current_address;
                $user->permanent_address = $request->permanent_address;
                $user->email = $request->email;
                $user->mobile = $request->mobile;
                $user->dob = date('Y-m-d', strtotime($request->dob));
                $user->image = $parents->getRawOriginal('image');
                $user->update();
            }
            $parents->first_name = $request->first_name;
            $parents->last_name = $request->last_name;
            $parents->gender = $request->gender;
            $parents->email = $request->email;
            $parents->mobile = $request->mobile;
            $parents->dob = date('Y-m-d', strtotime($request->dob));
            $parents->occupation = $request->occupation;
            if ($request->hasFile('image')) {
                if ($parents->image != "" && Storage::disk('public')->exists($parents->getRawOriginal('image'))) {
                    Storage::disk('public')->delete($parents->getRawOriginal('image'));
                }

                $image = $request->file('image');

                // made file name with combination of current time
                $file_name = time() . '-' . $image->getClientOriginalName();

                //made file path to store in database
                $file_path = 'parents/' . $file_name;

                //resized image
                resizeImage($image);

                //stored image to storage/public/parents folder
                $destinationPath = storage_path('app/public/parents');
                $image->move($destinationPath, $file_name);

                //saved file path to database
                $parents->image = $file_path;
            }

            $parents->save();
            if ($parents->user) {
                $user = $parents->user;
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->gender = $request->gender;
                $user->current_address = $request->current_address;
                $user->permanent_address = $request->permanent_address;
                $user->email = $request->email;
                $user->mobile = $request->mobile;
                $user->dob = date('Y-m-d', strtotime($request->dob));
                $user->image = $parents->getRawOriginal('image');
                $user->update();
            }
            $response = [
                'error' => false,
                'message' => trans('data_store_successfully')
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

    public function search(Request $request)
    {
        if ($request->type == "father") {
            $parent = Parents::where(function ($query) use ($request) {
                $query->orWhere('email', 'like', '%' . $request->email . '%')
                    ->orWhere('first_name', 'like', '%' . $request->email . '%')
                    ->orWhere('last_name', 'like', '%' . $request->email . '%');
            })
                ->where('gender', 'Male')->get();
        } elseif ($request->type == "mother") {
            $parent = Parents::where(function ($query) use ($request) {
                $query->orWhere('email', 'like', '%' . $request->email . '%')
                    ->orWhere('first_name', 'like', '%' . $request->email . '%')
                    ->orWhere('last_name', 'like', '%' . $request->email . '%');
            })
                ->where('gender', 'Female')->get();
        } else {
            $parent = Parents::where('email', 'like', '%' . $request->email . '%')
                ->orWhere('first_name', 'like', '%' . $request->email . '%')
                ->orWhere('last_name', 'like', '%' . $request->email . '%')->get();
        }

        if (!empty($parent)) {
            $response = [
                'error' => false,
                'data' => $parent
            ];
        } else {
            $response = [
                'error' => true,
                'message' => trans('no_data_found')
            ];
        }
        return response()->json($response);
    }
}
