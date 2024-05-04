<?php

namespace App\Http\Controllers;

use App\Models\ClassSchool;
use App\Models\ClassSection;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\Mediums;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Models\ClassTeacher;

class ClassTeacherController extends Controller
{
    public function teacher()
    {
        if (!Auth::user()->can('class-teacher-list')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $class_section = ClassSection::with('class.medium', 'section', 'class.streams')->get();

        $class_teacher_ids = ClassTeacher::whereNot('class_teacher_id', null)->pluck('class_teacher_id');
        // $assign_teacher_id = ClassSection::select('class_teacher_id')->whereNotNull('class_teacher_id')->get()->pluck('class_teacher_id');
        // $teachers = Teacher::whereNotIn('id', $assign_teacher_id)->with('user')->get();
        $classes = ClassSchool::orderBy('id', 'DESC')->with('medium','streams')->get();

        return view('class.teacher', compact('class_section', 'classes'));
    }

    public function assign_teacher(Request $request)
    {
        if (!Auth::user()->can('class-teacher-edit')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message')
            );
            return response()->json($response);
        }

        $request->validate([
            'class_section_id' => 'required',
            'teacher_id' => 'required',
        ]);
        try {
            $teacher = Teacher::findorFail($request->teacher_id);
            $existingrow=ClassTeacher::where('class_section_id',$request->class_section_id)->where('class_teacher_id',$request->teacher_id)->first();
            if(!$existingrow)
            {
                $class_teacher = new ClassTeacher();
                $class_teacher->class_section_id=$request->class_section_id;
                $class_teacher->class_teacher_id=$request->teacher_id;
                $class_teacher->save();
                $teacher->user->givePermissionTo('class-teacher');
            }

            // $teacher = Teacher::findOrFail($request->teacher_id);
            // $assign_teacher = ClassSection::find($request->class_section_id);
            // if ($assign_teacher->class_teacher_id && $assign_teacher->class_teacher_id != $request->teacher_id) {
            //     //If Old teacher is removed and new teacher is assigned as class teacher then remove old teacher's permission
            //     $old_teacher = Teacher::find($request->teacher_id)->with('user')->first();
            //     $old_teacher->user->revokePermissionTo('class-teacher');
            // }
            // $assign_teacher->class_teacher_id = $request->teacher_id;
            // $assign_teacher->save();


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

    public function show()
    {
        if (!Auth::user()->can('class-teacher-list')) {
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

        $sql = ClassSection::with('class.medium', 'section', 'classTeachers');
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")
                ->orWhereHas('class.medium', function ($q) use ($search) {
                    $q->where('classes.name', 'LIKE', "%$search%")->orwhere('mediums.name', 'LIKE', "%$search%");
                })
                ->orWhereHas('section', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%");
                })
                ->orWhereHas('classTeachers.user', function ($q) use ($search) {
                    $q->whereRaw("concat(users.first_name,' ',users.last_name) LIKE '%" . $search . "%'")->orwhere('users.first_name', 'LIKE', "%$search%")->orwhere('users.last_name', 'LIKE', "%$search%");
                });
        }
        if ($_GET['class_id']) {
            $sql = $sql->where('class_id', $_GET['class_id']);
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
            $operate = '<a class="btn btn-xs btn-gradient-primary btn-rounded btn-icon editdata" data-id=' . $row->id . ' title="Edit" data-toggle="modal" data-target="#editModal"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;';

            $tempRow['id'] = $row->id;
            $tempRow['class_id'] = $row->class_id;
            $tempRow['section_id'] = $row->section_id;
            $tempRow['no'] = $no++;
            $tempRow['class'] = $row->class->name . ' - ' . $row->class->medium->name;
            $tempRow['stream_name'] = $row->class->streams->name ?? '-';
            $tempRow['section'] = $row->section->name;
            $class_teacher_ids=[];
            $class_teacher_name= [];
            foreach( $row->classTeachers as $class_teacher)
            {
                $class_teacher_ids[] = $class_teacher->pivot->class_teacher_id;
                $class_teacher_name[]= $class_teacher->user->first_name .' '. $class_teacher->user->last_name ?? '-';
            }
            $tempRow['teacher_id'] = $class_teacher_ids ?? '-';
            $tempRow['teachers'] = $class_teacher_name ?? '-';
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function removeClassTeacher($id, $class_teacher_id)
    {
        try {
            $class_teacher = ClassTeacher::where('class_section_id',$id)->where('class_teacher_id',$class_teacher_id)->first();
            // $class_section = ClassSection::find($id);

            $teacher_id = $class_teacher_id;
            $old_teacher = Teacher::where('id',$teacher_id)->with('user')->first();
            $old_teacher->user->revokePermissionTo('class-teacher');

            $class_teacher->delete();

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
    public function getClassTeacherlist($class_section_id)
    {
        $response = Teacher::with('user')->orWhereHas('classTeachers',function($q)use($class_section_id){
                $q->where('class_section_id',$class_section_id);
            })->get();

        return response()->json($response);
    }

    public function getNotClassTeacherList($class_section_id)
    {
        $response = Teacher::with('user')->whereDoesntHave('classTeachers',function($q)use($class_section_id){
            $q->where('class_section_id',$class_section_id);
        })->get();

        return response()->json($response);
    }

}
