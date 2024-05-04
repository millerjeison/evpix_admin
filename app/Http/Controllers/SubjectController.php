<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Mediums;
use App\Models\Subject;
use App\Models\ClassSchool;
use App\Models\ClassSubject;
use App\Models\ExamMarks;
use App\Models\ExamTimetable;
use App\Models\StudentSubject;
use App\Models\SubjectTeacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $subjects = Subject::orderBy('id', 'DESC')->get();
        $mediums = Mediums::orderBy('id', 'DESC')->get();
        return response(view('subject.index', compact('subjects', 'mediums')));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'medium_id' => 'required|numeric',
            'name' => 'required',
            'type' => 'required|in:Practical,Theory',
            'bg_color' => 'required|not_in:transparent',
            'image' => 'max:2048',
        ])->setAttributeNames(
            ['bg_color' => 'Background Color'],
        );;

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }

        try {
            $subject = Subject::where(['name' => $request->name, 'medium_id' => $request->medium_id, 'type' => $request->type])->whereNot('id',$id)->count();
            if ($subject) {
                $response = array(
                    'error' => true,
                    'message' => trans('subject_already_exists')
                );
                return response()->json($response);
            } else {
                $validator = Validator::make($request->all(), [
                    'code' => 'nullable|unique:subjects,code,' . $id.',id,deleted_at,NULL'
                ]);
                if ($validator->fails()) {
                    $response = array(
                        'error' => true,
                        'message' => $validator->errors()->first()
                    );
                    return response()->json($response);
                }
                $subject = Subject::find($id);
                $subject->medium_id = $request->medium_id;
                $subject->name = $request->name;
                $subject->bg_color = $request->bg_color;
                $subject->code = $request->code;
                $subject->type = $request->type;
                if ($request->hasFile('image')) {
                    if (Storage::disk('public')->exists($subject->getRawOriginal('image'))) {
                        Storage::disk('public')->delete($subject->getRawOriginal('image'));
                    }

                    $image = $request->file('image');

                    // made file name with combination of current time
                    $file_name = time() . '-' . $image->getClientOriginalName();
                    //made file path to store in database
                    $file_path = 'subjects/' . $file_name;
                    if($image->getClientMimeType() != 'image/svg+xml' && $image->getClientMimeType() != 'image/svg'){
                        //resized image
                        resizeImage($image);
                    }
                    //stored image to storage/public/subjects folder
                    $destinationPath = storage_path('app/public/subjects');
                    $image->move($destinationPath, $file_name);
                    //saved file path to database
                    $subject->image = $file_path;
                }
                $subject->save();

                $response = array(
                    'error' => false,
                    'message' => trans('data_update_successfully'),
                );
            }
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
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'medium_id' => 'required|numeric',
            'name' => 'required',
            'type' => 'required|in:Practical,Theory',
            'bg_color' => 'required|not_in:transparent',
            'image' => 'max:2048',
        ])->setAttributeNames(
            ['bg_color' => 'Background Color'],
        );

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }

        try {
            $subject = Subject::where(['name' => $request->name, 'medium_id' => $request->medium_id, 'type' => $request->type])->count();
            if ($subject) {
                $response = array(
                    'error' => true,
                    'message' => trans('subject_already_exists')
                );
                return response()->json($response);
            } else {
                $validator = Validator::make($request->all(), [
                    'code' => 'nullable|unique:subjects,code,NULL,id,deleted_at,NULL',
                ]);
                if ($validator->fails()) {
                    $response = array(
                        'error' => true,
                        'message' => $validator->errors()->first()
                    );
                    return response()->json($response);
                }

                $image = $request->file('image');
                // made file name with combination of current time
                $file_name = time() . '-' . $image->getClientOriginalName();
                //made file path to store in database
                $file_path = 'subjects/' . $file_name;
                if($image->getClientMimeType() != 'image/svg+xml' && $image->getClientMimeType() != 'image/svg'){
                    //resized image
                    resizeImage($image);
                }
                //stored image to storage/public/subjects folder
                $destinationPath = storage_path('app/public/subjects');
                $image->move($destinationPath, $file_name);

                $subject = new Subject();
                $subject->medium_id = $request->medium_id;
                $subject->name = $request->name;
                $subject->bg_color = $request->bg_color;
                $subject->code = $request->code;
                $subject->type = $request->type;
                $subject->image = $file_path;
                $subject->save();

                $response = array(
                    'error' => false,
                    'message' => trans('data_store_successfully'),
                );
            }
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            //check wheather the subject exists in other table
            $assignments = Assignment::where('subject_id', $id)->count();
            $class_subjects = ClassSubject::where('subject_id', $id)->count();
            $exam_marks = ExamMarks::where('subject_id',$id)->count();
            $exam_timetables = ExamTimetable::where('subject_id',$id)->count();
            $student_subjects = StudentSubject::where('subject_id',$id)->count();
            $subject_teachers = SubjectTeacher::where('subject_id',$id)->count();

            if($assignments || $class_subjects || $exam_marks || $exam_timetables || $student_subjects || $subject_teachers){
                $response = array(
                    'error' => true,
                    'message' => trans('cannot_delete_beacuse_data_is_associated_with_other_data')
                );
            }else{
                $subject = Subject::find($id);
                $subject->delete();
                $response = array(
                    'error' => false,
                    'message' => trans('data_delete_successfully')
                );
            }

        } catch (\Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred')
            );
        }
        return response()->json($response);
    }

    public function show()
    {
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

        $sql = Subject::with('medium');
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql = $sql->where('id', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%")->orwhere('code', 'LIKE', "%$search%")->orwhere('type', 'LIKE', "%$search%");
        }
        if (isset($_GET['medium_id']) && !empty($_GET['medium_id'])) {
            $sql = $sql->where('medium_id', $_GET['medium_id']);
        }


        $total = $sql->count();

        $sql = $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $no = 1;

        foreach ($res as $row) {
            $operate = '<a href=' . route('subject.edit', $row->id) . ' class="btn btn-xs btn-gradient-primary btn-rounded btn-icon edit-data" data-id=' . $row->id . ' title="Edit" data-toggle="modal" data-target="#editModal"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;';
            $operate .= '<a href=' . route('subject.destroy', $row->id) . ' class="btn btn-xs btn-gradient-danger btn-rounded btn-icon delete-form" data-id=' . $row->id . '><i class="fa fa-trash"></i></a>';

            $tempRow['id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['name'] = $row->name;
            $tempRow['code'] = $row->code;
            $tempRow['bg_color'] = $row->bg_color;

            $tempRow['image'] = $row->image;

            $tempRow['medium_id'] = $row->medium_id;
            $tempRow['medium_name'] = $row->medium->name;
            $tempRow['type'] = $row->type;
            $tempRow['created_at'] = $row->created_at;
            $tempRow['updated_at'] = $row->updated_at;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
}
