<?php

namespace App\Http\Controllers\Api;

use Exception;
use Throwable;
use Carbon\Carbon;
use App\Models\Exam;
use App\Models\File;
use App\Models\User;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\Holiday;
use App\Models\Parents;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\ChatFile;
use App\Models\Students;
use App\Models\ExamClass;
use App\Models\ExamMarks;
use App\Models\Timetable;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\ExamResult;
use App\Models\ChatMessage;
use App\Models\ClassSchool;
use App\Models\LessonTopic;
use App\Models\ReadMessage;
use Illuminate\Support\Arr;
use App\Models\Announcement;
use App\Models\ClassSection;
use App\Models\ClassSubject;
use App\Models\ClassTeacher;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\ExamTimetable;
use App\Models\StudentSubject;
use App\Models\SubjectTeacher;
use App\Models\UserNotification;
use PhpParser\Node\Stmt\Foreach_;
use App\Rules\uniqueLessonInClass;
use App\Rules\uniqueTopicInLesson;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TeacherApiController extends Controller
{
    public function login(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $auth = Auth::user();

             if (!$auth->hasRole('Teacher')) {
                $response = array(
                    'error' => true,
                    'message' => 'Invalid Login Credentials',
                    'code' => 101
                );
                return response()->json($response, 200);
            }
            $token = $auth->createToken($auth->first_name)->plainTextToken;
            $user = $auth->load(['teacher']);

            if ($request->fcm_id) {
                $auth->fcm_id = $request->fcm_id;
                $auth->save();
            }
            if($request->device_type){
                $auth->device_type = $request->device_type;
                $auth->save();
            }

            $response = array(
                'error' => false,
                'message' => 'User logged-in!',
                'token' => $token,
                'data' => $user,
                'code' => 100,
            );
            return response()->json($response, 200);
        } else {
            $response = array(
                'error' => true,
                'message' => 'Invalid Login Credentials',
                'code' => 101
            );
            return response()->json($response, 200);
        }
    }

    public function classes(Request $request)
    {
        try {
            $user = $request->user()->teacher;
            $class_section_id=$user->class_sections->pluck('class_section_id');

            //Find the class in which teacher is assigns as Class Teacher
            if($user->class_sections){

                $class_teacher = ClassSection::whereIn('id',$class_section_id)->with('class.medium', 'section','class.streams','class.shifts')->get();
            }

            //Find the Classes in which teacher is taking subjects
            $class_section_ids = $user->classes()->pluck('class_section_id');

            $class_sections = ClassSection::whereIn('id', $class_section_ids)->with('class.medium', 'section','class.streams','class.shifts')->get();
            $class_section= $class_sections->diff($class_teacher);

            $response = array(
                'error' => false,
                'message' => 'Teacher Classes Fetched Successfully.',
                'data' => ['class_teacher' => $class_teacher ?? (object)null, 'other' => $class_section],
                'code' => 200,
            );
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
            return response()->json($response, 200);
        }
    }

    public function subjects(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_section_id' => 'nullable|numeric',
            'subject_id' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $user = $request->user();
            $teacher = $user->teacher;
            $subjects = $teacher->subjects();
            if ($request->class_section_id) {
                $subjects = $subjects->where('class_section_id', $request->class_section_id);
            }

            if ($request->subject_id) {
                $subjects = $subjects->where('subject_id', $request->subject_id);
            }
            $subjects = $subjects->with('subject', 'class_section')->get();

            $response = array(
                'error' => false,
                'message' => 'Teacher Subject Fetched Successfully.',
                'data' => $subjects,
                'code' => 200,
            );
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
            return response()->json($response, 200);
        }
    }


    public function getAssignment(Request $request)
    {
        if (!Auth::user()->can('assignment-list')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            'class_section_id' => 'nullable|numeric',
            'subject_id' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $sql = Assignment::assignmentteachers()->with('class_section', 'file', 'subject');
            if ($request->class_section_id) {
                $sql = $sql->where('class_section_id', $request->class_section_id);
            }

            if ($request->subject_id) {
                $sql = $sql->where('subject_id', $request->subject_id);
            }
            $data = $sql->orderBy('id', 'DESC')->paginate();
            $response = array(
                'error' => false,
                'message' => 'Assignment Fetched Successfully.',
                'data' => $data,
                'code' => 200,
            );
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
            return response()->json($response, 200);
        }
    }

    public function createAssignment(Request $request)
    {
        if (!Auth::user()->can('assignment-create')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            "class_section_id" => 'required|numeric',
            "subject_id" => 'required|numeric',
            "name" => 'required',
            "instructions" => 'nullable',
            "due_date" => 'required|date',
            "points" => 'nullable',
            "resubmission" => 'nullable|boolean',
            "extra_days_for_resubmission" => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {

            $session_year = getSettings('session_year');
            $session_year_id = $session_year['session_year'];

            $assignment = new Assignment();
            $assignment->class_section_id = $request->class_section_id;
            $assignment->subject_id = $request->subject_id;
            $assignment->name = $request->name;
            $assignment->instructions = $request->instructions;
            $assignment->due_date = Carbon::parse($request->due_date)->format('Y-m-d H:i:s');
            $assignment->points = $request->points;
            if ($request->resubmission) {
                $assignment->resubmission = 1;
                $assignment->extra_days_for_resubmission = $request->extra_days_for_resubmission;
            } else {
                $assignment->resubmission = 0;
                $assignment->extra_days_for_resubmission = null;
            }
            $assignment->session_year_id = $session_year_id;

            $subject_name = Subject::select('name')->where('id', $request->subject_id)->pluck('name')->first();
            $title = 'New assignment added in ' . $subject_name;
            $body = $request->name;
            $type = "assignment";
            $image = null;
            $userinfo = null;
            $user = Students::select('user_id')->where('class_section_id', $request->class_section_id)->get()->pluck('user_id');

            $notification = new Notification();
            $notification->send_to = 3;
            $notification->title = $title;
            $notification->message = $body;
            $notification->type = $type;
            $notification->date = Carbon::now();
            $notification->is_custom = 0;
            $notification->save();

            foreach($user as $data)
            {
                $user_notification = new UserNotification();
                $user_notification->notification_id = $notification->id;
                $user_notification->user_id = $data;
                $user_notification->save();
            }
            $assignment->save();
            send_notification($user, $title, $body, $type, $image, $userinfo);

            if ($request->hasFile('file')) {
                foreach ($request->file as $file_upload) {
                    $file = new File();
                    $file->file_name = $file_upload->getClientOriginalName();
                    $file->type = 1;
                    $file->file_url = $file_upload->store('assignment', 'public');
                    $file->modal()->associate($assignment);
                    $file->save();
                }
            }

            $response = array(
                'error' => false,
                'message' => trans('data_store_successfully'),
                'code' => 200,
            );
        } catch (Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

    public function updateAssignment(Request $request)
    {
        if (!Auth::user()->can('assignment-edit')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            "assignment_id" => 'required|numeric',
            "class_section_id" => 'required|numeric',
            "subject_id" => 'required|numeric',
            "name" => 'required',
            "instructions" => 'nullable',
            "due_date" => 'required|date',
            "points" => 'nullable',
            "resubmission" => 'nullable|boolean',
            "extra_days_for_resubmission" => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $session_year = getSettings('session_year');
            $session_year_id = $session_year['session_year'];

            $assignment = Assignment::find($request->assignment_id);
            $assignment->class_section_id = $request->class_section_id;
            $assignment->subject_id = $request->subject_id;
            $assignment->name = $request->name;
            $assignment->instructions = $request->instructions;
            $assignment->due_date = Carbon::parse($request->due_date)->format('Y-m-d H:i:s');;
            $assignment->points = $request->points;
            if ($request->resubmission) {
                $assignment->resubmission = 1;
                $assignment->extra_days_for_resubmission = $request->extra_days_for_resubmission;
            } else {
                $assignment->resubmission = 0;
                $assignment->extra_days_for_resubmission = null;
            }

            $assignment->session_year_id = $session_year_id;
            $subject_name = Subject::select('name')->where('id', $request->subject_id)->pluck('name')->first();
            $title = 'Update assignment in ' . $subject_name;
            $body = $request->name;
            $type = "assignment";
            $image = null;
            $userinfo = null;

            $user = Students::select('user_id')->where('class_section_id', $request->class_section_id)->get()->pluck('user_id');

            $notification = new Notification();
            $notification->send_to = 3;
            $notification->title = $title;
            $notification->message = $body;
            $notification->type = $type;
            $notification->date = Carbon::now();
            $notification->is_custom = 0;
            $notification->save();

            foreach($user as $data)
            {
                $user_notification = new UserNotification();
                $user_notification->notification_id = $notification->id;
                $user_notification->user_id = $data;
                $user_notification->save();
            }

            $assignment->save();
            send_notification($user, $title, $body, $type, $image, $userinfo);

            if ($request->hasFile('file')) {
                foreach ($request->file as $file_upload) {
                    $file = new File();
                    $file->file_name = $file_upload->getClientOriginalName();
                    $file->type = 1;
                    $file->file_url = $file_upload->store('assignment', 'public');
                    $file->modal()->associate($assignment);
                    $file->save();
                }
            }

            $response = array(
                'error' => false,
                'message' => trans('data_store_successfully'),
                'code' => 200,
            );
        } catch (Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

    public function deleteAssignment(Request $request)
    {
        if (!Auth::user()->can('assignment-delete')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }
        try {
            $assignment = Assignment::find($request->assignment_id);
            $assignment->delete();
            $response = array(
                'error' => false,
                'message' => trans('data_delete_successfully'),
                'code' => 200
            );
        } catch (\Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

    public function getAssignmentSubmission(Request $request)
    {
        if (!Auth::user()->can('assignment-submission')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            'assignment_id' => 'required|nullable|numeric'
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $sql = AssignmentSubmission::assignmentsubmissionteachers()->with('assignment.subject:id,name', 'student:id,user_id', 'student.user:first_name,last_name,id,image', 'file');
            $data = $sql->where('assignment_id', $request->assignment_id)->get();
            $response = array(
                'error' => false,
                'message' => 'Assignment Fetched Successfully.',
                'data' => $data,
                'code' => 200,
            );
        } catch (Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response, 200);
    }

    public function updateAssignmentSubmission(Request $request)
    {
        if (!Auth::user()->can('assignment-submission')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            'assignment_submission_id' => 'required|numeric',
            'status' => 'required|numeric|in:1,2',
            'points' => 'nullable|numeric',
            'feedback' => 'nullable',
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }

        try {
            $assignment_submission = AssignmentSubmission::findOrFail($request->assignment_submission_id);
            $assignment_submission->feedback = $request->feedback;
            if ($request->status == 1) {
                $assignment_submission->points = $request->points;
            } else {
                $assignment_submission->points = null;
            }

            $assignment_submission->status = $request->status;
            $assignment_submission->save();

            $assignment_data = Assignment::where('id', $assignment_submission->assignment_id)->with('subject')->first();
            $user = Students::select('user_id')->where('id', $assignment_submission->student_id)->get()->pluck('user_id');
            $title = '';
            $body = '';
            if ($request->status == 2) {
                $title = "Assignment rejected";
                $body = $assignment_data->name . " rejected in " . $assignment_data->subject->name . " subject";
            }
            if ($request->status == 1) {
                $title = "Assignment accepted";
                $body = $assignment_data->name . " accepted in " . $assignment_data->subject->name . " subject";
            }
            $type = "assignment";
            $image = null;
            $userinfo = null;

            $notification = new Notification();
            $notification->send_to = 3;
            $notification->title = $title;
            $notification->message = $body;
            $notification->type = $type;
            $notification->date = Carbon::now();
            $notification->is_custom = 0;
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
                'message' => trans('data_update_successfully'),
                'code' => 200,
            );
        } catch (\Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

    public function getLesson(Request $request)
    {
        if (!Auth::user()->can('lesson-list')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message')
            );
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            'lesson_id' => 'nullable|numeric',
            'class_section_id' => 'nullable|numeric',
            'subject_id' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $sql = Lesson::lessonteachers()->with('file')->withCount('topic');

            if ($request->lesson_id) {
                $sql = $sql->where('id', $request->lesson_id);
            }

            if ($request->class_section_id) {
                $sql = $sql->where('class_section_id', $request->class_section_id);
            }

            if ($request->subject_id) {
                $sql = $sql->where('subject_id', $request->subject_id);
            }
            $data = $sql->orderBy('id', 'DESC')->get();
            $response = array(
                'error' => false,
                'message' => 'Lesson Fetched Successfully.',
                'data' => $data,
                'code' => 200,
            );
            return response()->json($response);
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
            return response()->json($response, 200);
        }
    }

    public function createLesson(Request $request)
    {
        if (!Auth::user()->can('lesson-create')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'description' => 'required',
                'class_section_id' => 'required|numeric',
                'subject_id' => 'required|numeric',

                'file' => 'nullable|array',
                'file.*.type' => 'nullable|in:1,2,3,4',
                'file.*.name' => 'required_with:file.*.type',
                'file.*.thumbnail' => 'required_if:file.*.type,2,3,4',
                'file.*.file' => 'required_if:file.*.type,1,3',
                'file.*.link' => 'required_if:file.*.type,2,4',

                //            'file.*.type' => 'nullable|in:file_upload,youtube_link,video_upload,other_link',
                //            'file.*.name' => 'required_with:file.*.type',
                //            'file.*.thumbnail' => 'required_if:file.*.type,youtube_link,video_upload,other_link',
                //            'file.*.file' => 'required_if:file.*.type,file_upload,video_upload',
                //            'file.*.link' => 'required_if:file.*.type,youtube_link,other_link',
                //Regex for Youtube Link
                // 'file.*.link'=>['required_if:file.*.type,youtube_link','regex:/^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((?:\w|-){11})(?:&list=(\S+))?$/'],
                //Regex for Other Link
                // 'file.*.link'=>'required_if:file.*.type,other_link|url'
            ]
        );

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        $validator2 = Validator::make(
            $request->all(),
            [
                'name' => ['required', new uniqueLessonInClass($request->class_section_id,$request->subject_id)]
            ]
        );
        if ($validator2->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator2->errors()->first(),
                'code' => 113,
            );
            return response()->json($response);
        }
        try {
            $lesson = new Lesson();
            $lesson->name = $request->name;
            $lesson->description = $request->description;
            $lesson->class_section_id = $request->class_section_id;
            $lesson->subject_id = $request->subject_id;
            $lesson->save();

            if ($request->file) {
                foreach ($request->file as $key => $file) {
                    if ($file['type']) {
                        $lesson_file = new File();
                        $lesson_file->file_name = $file['name'];
                        $lesson_file->modal()->associate($lesson);

                        if ($file['type'] == "1") {
                            $lesson_file->type = 1;
                            $lesson_file->file_url = $file['file']->store('lessons', 'public');
                        } elseif ($file['type'] == "2") {
                            $lesson_file->type = 2;
                            $lesson_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                            $lesson_file->file_url = $file['link'];
                        } elseif ($file['type'] == "3") {
                            $lesson_file->type = 3;
                            $lesson_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                            $lesson_file->file_url = $file['file']->store('lessons', 'public');
                        } elseif ($file['type'] == "4") {
                            $lesson_file->type = 4;
                            $lesson_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                            $lesson_file->file_url = $file['link'];
                        }
                        $lesson_file->save();
                    }
                }
            }

            $response = array(
                'error' => false,
                'message' => trans('data_store_successfully'),
                'code' => 200,
            );
        } catch (Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

    public function updateLesson(Request $request)
    {
        if (!Auth::user()->can('lesson-edit')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'lesson_id' => 'required|numeric',
                'name' => 'required',
                'description' => 'required',
                'class_section_id' => 'required|numeric',
                'subject_id' => 'required|numeric',

                'edit_file' => 'nullable|array',
                'edit_file.*.id' => 'required|numeric',
                'edit_file.*.type' => 'nullable|in:1,2,3,4',
                'edit_file.*.name' => 'required_with:edit_file.*.type',
                'edit_file.*.link' => 'required_if:edit_file.*.type,2,4',

                'file' => 'nullable|array',
                'file.*.type' => 'nullable|in:1,2,3,4',
                'file.*.name' => 'required_with:file.*.type',
                'file.*.thumbnail' => 'required_if:file.*.type,2,3,4',
                'file.*.file' => 'required_if:file.*.type,1,3',
                'file.*.link' => 'required_if:file.*.type,2,4',

                //            'edit_file' => 'nullable|array',
                //            'edit_file.*.id' => 'required|numeric',
                //            'edit_file.*.type' => 'nullable|in:file_upload,youtube_link,video_upload,other_link',
                //            'edit_file.*.name' => 'required_with:edit_file.*.type',
                //            'edit_file.*.link' => 'required_if:edit_file.*.type,youtube_link,other_link',
                //
                //            'file' => 'nullable|array',
                //            'file.*.type' => 'nullable|in:file_upload,youtube_link,video_upload,other_link',
                //            'file.*.name' => 'required_with:file.*.type',
                //            'file.*.thumbnail' => 'required_if:file.*.type,youtube_link,video_upload,other_link',
                //            'file.*.file' => 'required_if:file.*.type,file_upload,video_upload',
                //            'file.*.link' => 'required_if:file.*.type,youtube_link,other_link',

                //Regex for Youtube Link
                // 'file.*.link'=>['required_if:file.*.type,youtube_link','regex:/^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((?:\w|-){11})(?:&list=(\S+))?$/'],
                //Regex for Other Link
                // 'file.*.link'=>'required_if:file.*.type,other_link|url'
            ]
        );
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }

        $validator2 = Validator::make(
            $request->all(),
            [
                'name' => ['required', new uniqueLessonInClass($request->class_section_id, $request->lesson_id)]
            ]
        );
        if ($validator2->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator2->errors()->first(),
                'code' => 113,
            );
            return response()->json($response);
        }
        try {
            $lesson = Lesson::find($request->lesson_id);
            $lesson->name = $request->name;
            $lesson->description = $request->description;
            $lesson->class_section_id = $request->class_section_id;
            $lesson->subject_id = $request->subject_id;
            $lesson->save();

            // Update the Old Files
            if ($request->edit_file) {
                foreach ($request->edit_file as $file) {
                    if ($file['type']) {
                        $lesson_file = File::find($file['id']);
                        if ($lesson_file) {
                            $lesson_file->file_name = $file['name'];

                            if ($file['type'] == "1") {
                                $lesson_file->type = 1;
                                if (!empty($file['file'])) {
                                    if (Storage::disk('public')->exists($lesson_file->getRawOriginal('file_url'))) {
                                        Storage::disk('public')->delete($lesson_file->getRawOriginal('file_url'));
                                    }
                                    $lesson_file->file_url = $file['file']->store('lessons', 'public');
                                }
                            } elseif ($file['type'] == "2") {
                                $lesson_file->type = 2;
                                if (!empty($file['thumbnail'])) {
                                    if (Storage::disk('public')->exists($lesson_file->getRawOriginal('file_url'))) {
                                        Storage::disk('public')->delete($lesson_file->getRawOriginal('file_url'));
                                    }
                                    $lesson_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                                }

                                $lesson_file->file_url = $file['link'];
                            } elseif ($file['type'] == "3") {
                                $lesson_file->type = 3;
                                if (!empty($file['file'])) {
                                    if (Storage::disk('public')->exists($lesson_file->getRawOriginal('file_url'))) {
                                        Storage::disk('public')->delete($lesson_file->getRawOriginal('file_url'));
                                    }
                                    $lesson_file->file_url = $file['file']->store('lessons', 'public');
                                }

                                if (!empty($file['thumbnail'])) {
                                    if (Storage::disk('public')->exists($lesson_file->getRawOriginal('file_url'))) {
                                        Storage::disk('public')->delete($lesson_file->getRawOriginal('file_url'));
                                    }
                                    $lesson_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                                }
                            } elseif ($file['type'] == "4") {
                                $lesson_file->type = 4;
                                if (!empty($file['thumbnail'])) {
                                    if (Storage::disk('public')->exists($lesson_file->getRawOriginal('file_url'))) {
                                        Storage::disk('public')->delete($lesson_file->getRawOriginal('file_url'));
                                    }
                                    $lesson_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                                }
                                $lesson_file->file_url = $file['link'];
                            }

                            $lesson_file->save();
                        }
                    }
                }
            }

            //Add the new Files
            if ($request->file) {
                foreach ($request->file as $file) {
                    if ($file['type']) {
                        $lesson_file = new File();
                        $lesson_file->file_name = $file['name'];
                        $lesson_file->modal()->associate($lesson);

                        if ($file['type'] == "1") {
                            $lesson_file->type = 1;
                            $lesson_file->file_url = $file['file']->store('lessons', 'public');
                        } elseif ($file['type'] == "2") {
                            $lesson_file->type = 2;
                            $lesson_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                            $lesson_file->file_url = $file['link'];
                        } elseif ($file['type'] == "3") {
                            $lesson_file->type = 3;
                            $lesson_file->file_url = $file['file']->store('lessons', 'public');
                            $lesson_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                        } elseif ($file['type'] == "4") {
                            $lesson_file->type = 4;
                            $lesson_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                            $lesson_file->file_url = $file['link'];
                        }
                        $lesson_file->save();
                    }
                }
            }

            $response = array(
                'error' => false,
                'message' => trans('data_store_successfully'),
                'code' => 200,
            );
        } catch (Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

    public function deleteLesson(Request $request)
    {
        if (!Auth::user()->can('lesson-delete')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }

        $validator = Validator::make($request->all(), [
            'lesson_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $lesson = Lesson::lessonteachers()->where('id', $request->lesson_id)->firstOrFail();
            $lesson->delete();
            $response = array(
                'error' => false,
                'message' => trans('data_delete_successfully'),
                'code' => 200,
            );
        } catch (Throwable) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

    public function getTopic(Request $request)
    {
        if (!Auth::user()->can('topic-list')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            'lesson_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $sql = LessonTopic::lessontopicteachers()->with('lesson.class_section', 'lesson.subject', 'file');
            $data = $sql->where('lesson_id', $request->lesson_id)->orderBy('id', 'DESC')->get();
            $response = array(
                'error' => false,
                'message' => 'Topic Fetched Successfully.',
                'data' => $data,
                'code' => 200,
            );
            return response()->json($response);
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
            return response()->json($response, 200);
        }
    }

    public function createTopic(Request $request)
    {
        if (!Auth::user()->can('topic-create')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'description' => 'required',
                'class_section_id' => 'required|numeric',
                'subject_id' => 'required|numeric',
                'lesson_id' => 'required|numeric',
                'file' => 'nullable|array',
                'file.*.type' => 'nullable|in:1,2,3,4',
                'file.*.name' => 'required_with:file.*.type',
                'file.*.thumbnail' => 'required_if:file.*.type,2,3,4',
                'file.*.file' => 'required_if:file.*.type,1,3',
                'file.*.link' => 'required_if:file.*.type,2,4',
                //            'file' => 'nullable|array',
                //            'file.*.type' => 'nullable|in:file_upload,youtube_link,video_upload,other_link',
                //            'file.*.name' => 'required_with:file.*.type',
                //            'file.*.thumbnail' => 'required_if:file.*.type,youtube_link,video_upload,other_link',
                //            'file.*.file' => 'required_if:file.*.type,file_upload,video_upload',
                //            'file.*.link' => 'required_if:file.*.type,youtube_link,other_link',
                //Regex for Youtube Link
                // 'file.*.link'=>['required_if:file.*.type,youtube_link','regex:/^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((?:\w|-){11})(?:&list=(\S+))?$/'],
                //Regex for Other Link
                // 'file.*.link'=>'required_if:file.*.type,other_link|url'
            ]
        );

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102
            );
            return response()->json($response);
        }
        $validator2 = Validator::make(
            $request->all(),
            [
                'name' => ['required', new uniqueTopicInLesson($request->lesson_id)]
            ]
        );
        if ($validator2->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator2->errors()->first(),
                'code' => 113,
            );
            return response()->json($response);
        }

        try {
            $topic = new LessonTopic();
            $topic->name = $request->name;
            $topic->description = $request->description;
            $topic->lesson_id = $request->lesson_id;
            $topic->save();

            if ($request->file) {
                foreach ($request->file as $data) {
                    if ($data['type']) {
                        $file = new File();
                        $file->file_name = $data['name'];
                        $file->modal()->associate($topic);

                        if ($data['type'] == "1") {
                            $file->type = 1;
                            $file->file_url = $data['file']->store('lessons', 'public');
                        } elseif ($data['type'] == "2") {
                            $file->type = 2;
                            $file->file_thumbnail = $data['thumbnail']->store('lessons', 'public');
                            $file->file_url = $data['link'];
                        } elseif ($data['type'] == "3") {
                            $file->type = 3;
                            $file->file_thumbnail = $data['thumbnail']->store('lessons', 'public');
                            $file->file_url = $data['file']->store('lessons', 'public');
                        } elseif ($data['type'] == "other_link") {
                            $file->type = 4;
                            $file->file_thumbnail = $data['thumbnail']->store('lessons', 'public');
                            $file->file_url = $data['link'];
                        }

                        $file->save();
                    }
                }
            }

            $response = array(
                'error' => false,
                'message' => trans('data_store_successfully'),
                'code' => 200
            );
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
            return response()->json($response, 200);
        }
        return response()->json($response);
    }

    public function updateTopic(Request $request)
    {
        if (!Auth::user()->can('topic-edit')) {
            $response = array(
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return redirect(route('home'))->withErrors($response);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'topic_id' => 'required|numeric',
                'name' => 'required',
                'description' => 'required',
                'class_section_id' => 'required|numeric',
                'subject_id' => 'required|numeric',
                'edit_file' => 'nullable|array',
                'edit_file.*.type' => 'nullable|in:1,2,3,4',
                'edit_file.*.name' => 'required_with:edit_file.*.type',
                'edit_file.*.link' => 'required_if:edit_file.*.type,2,',

                'file' => 'nullable|array',
                'file.*.type' => 'nullable|in:1,2,3,4',
                'file.*.name' => 'required_with:file.*.type',
                'file.*.thumbnail' => 'required_if:file.*.type,2,3,4',
                'file.*.file' => 'required_if:file.*.type,1,3',
                'file.*.link' => 'required_if:file.*.type,2,4',


                //            'edit_file' => 'nullable|array',
                //            'edit_file.*.type' => 'nullable|in:file_upload,youtube_link,video_upload,other_link',
                //            'edit_file.*.name' => 'required_with:edit_file.*.type',
                //            'edit_file.*.link' => 'required_if:edit_file.*.type,youtube_link,',
                //
                //            'file' => 'nullable|array',
                //            'file.*.type' => 'nullable|in:file_upload,youtube_link,video_upload,other_link',
                //            'file.*.name' => 'required_with:file.*.type',
                //            'file.*.thumbnail' => 'required_if:file.*.type,youtube_link,video_upload,other_link',
                //            'file.*.file' => 'required_if:file.*.type,file_upload,video_upload',
                //            'file.*.link' => 'required_if:file.*.type,youtube_link,other_link',
            ]
        );
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102
            );
            return response()->json($response);
        }
        $validator2 = Validator::make(
            $request->all(),
            [
                'name' => ['required', new uniqueTopicInLesson($request->lesson_id, $request->topic_id)],
            ]
        );
        if ($validator2->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator2->errors()->first(),
                'code' => 113,
            );
            return response()->json($response);
        }
        try {
            $topic = LessonTopic::find($request->topic_id);

            $topic->name = $request->name;
            $topic->description = $request->description;
            $topic->save();

            // Update the Old Files
            if ($request->edit_file) {
                foreach ($request->edit_file as $key => $file) {
                    if ($file['type']) {
                        $topic_file = File::find($file['id']);
                        $topic_file->file_name = $file['name'];

                        if ($file['type'] == "1") {
                            // Type File :- File Upload
                            $topic_file->type = 1;
                            if (!empty($file['file'])) {
                                if (Storage::disk('public')->exists($topic_file->getRawOriginal('file_url'))) {
                                    Storage::disk('public')->delete($topic_file->getRawOriginal('file_url'));
                                }
                                $topic_file->file_url = $file['file']->store('lessons', 'public');
                            }
                        } elseif ($file['type'] == "2") {
                            // Type File :- Youtube Link Upload
                            $topic_file->type = 2;
                            if (!empty($file['thumbnail'])) {
                                if (Storage::disk('public')->exists($topic_file->getRawOriginal('file_url'))) {
                                    Storage::disk('public')->delete($topic_file->getRawOriginal('file_url'));
                                }
                                $topic_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                            }

                            $topic_file->file_url = $file['link'];
                        } elseif ($file['type'] == "3") {
                            // Type File :- Vedio Upload
                            $topic_file->type = 3;
                            if (!empty($file['file'])) {
                                if (Storage::disk('public')->exists($topic_file->getRawOriginal('file_url'))) {
                                    Storage::disk('public')->delete($topic_file->getRawOriginal('file_url'));
                                }
                                $topic_file->file_url = $file['file']->store('lessons', 'public');
                            }

                            if (!empty($file['thumbnail'])) {
                                if (Storage::disk('public')->exists($topic_file->getRawOriginal('file_url'))) {
                                    Storage::disk('public')->delete($topic_file->getRawOriginal('file_url'));
                                }
                                $topic_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                            }
                        } elseif ($file['type'] == "4") {
                            $topic_file->type = 4;
                            if (!empty($file['thumbnail'])) {
                                if (Storage::disk('public')->exists($topic_file->getRawOriginal('file_url'))) {
                                    Storage::disk('public')->delete($topic_file->getRawOriginal('file_url'));
                                }
                                $topic_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                            }
                            $topic_file->file_url = $file['link'];
                        }

                        $topic_file->save();
                    }
                }
            }

            //Add the new Files
            if ($request->file) {
                foreach ($request->file as $file) {
                    $topic_file = new File();
                    $topic_file->file_name = $file['name'];
                    $topic_file->modal()->associate($topic);

                    if ($file['type'] == "1") {
                        $topic_file->type = 1;
                        $topic_file->file_url = $file['file']->store('lessons', 'public');
                    } elseif ($file['type'] == "2") {
                        $topic_file->type = 2;
                        $topic_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                        $topic_file->file_url = $file['link'];
                    } elseif ($file['type'] == "3") {
                        $topic_file->type = 3;
                        $topic_file->file_url = $file['file']->store('lessons', 'public');
                        $topic_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                    } elseif ($file['type'] == "4") {
                        $topic_file->type = 4;
                        $topic_file->file_thumbnail = $file['thumbnail']->store('lessons', 'public');
                        $topic_file->file_url = $file['link'];
                    }
                    $topic_file->save();
                }
            }

            $response = array(
                'error' => false,
                'message' => trans('data_store_successfully'),
                'code' => 200
            );
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
            return response()->json($response, 200);
        }
        return response()->json($response);
    }

    public function deleteTopic(Request $request)
    {
        if (!Auth::user()->can('topic-delete')) {
            $response = array(
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return redirect(route('home'))->withErrors($response);
        }
        try {
            $topic = LessonTopic::LessonTopicTeachers()->findOrFail($request->topic_id);
            $topic->delete();
            $response = array(
                'error' => false,
                'message' => trans('data_delete_successfully'),
                'code' => 200
            );
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
            return response()->json($response, 200);
        }
        return response()->json($response);
    }

    public function updateFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $file = File::find($request->file_id);
            $file->file_name = $request->name;


            if ($file->type == "1") {
                // Type File :- File Upload

                if (!empty($request->file)) {
                    if (Storage::disk('public')->exists($file->getRawOriginal('file_url'))) {
                        Storage::disk('public')->delete($file->getRawOriginal('file_url'));
                    }

                    if ($file->modal_type == "App\Models\Lesson") {

                        $file->file_url = $request->file->store('lessons', 'public');
                    } else if ($file->modal_type == "App\Models\LessonTopic") {

                        $file->file_url = $request->file->store('topics', 'public');
                    } else {

                        $file->file_url = $request->file->store('other', 'public');
                    }
                }
            } elseif ($file->type == "2") {
                // Type File :- Youtube Link Upload

                if (!empty($request->thumbnail)) {
                    if (Storage::disk('public')->exists($file->getRawOriginal('file_url'))) {
                        Storage::disk('public')->delete($file->getRawOriginal('file_url'));
                    }

                    if ($file->modal_type == "App\Models\Lesson") {

                        $file->file_thumbnail = $request->thumbnail->store('lessons', 'public');
                    } else if ($file->modal_type == "App\Models\LessonTopic") {

                        $file->file_thumbnail = $request->thumbnail->store('topics', 'public');
                    } else {

                        $file->file_thumbnail = $request->thumbnail->store('other', 'public');
                    }
                }
                $file->file_url = $request->link;
            } elseif ($file->type == "3") {
                // Type File :- Vedio Upload

                if (!empty($request->file)) {
                    if (Storage::disk('public')->exists($file->getRawOriginal('file_url'))) {
                        Storage::disk('public')->delete($file->getRawOriginal('file_url'));
                    }

                    if ($file->modal_type == "App\Models\Lesson") {

                        $file->file_url = $request->file->store('lessons', 'public');
                    } else if ($file->modal_type == "App\Models\LessonTopic") {

                        $file->file_url = $request->file->store('topics', 'public');
                    } else {

                        $file->file_url = $request->file->store('other', 'public');
                    }
                }

                if (!empty($request->thumbnail)) {
                    if (Storage::disk('public')->exists($file->getRawOriginal('file_url'))) {
                        Storage::disk('public')->delete($file->getRawOriginal('file_url'));
                    }
                    if ($file->modal_type == "App\Models\Lesson") {

                        $file->file_thumbnail = $request->thumbnail->store('lessons', 'public');
                    } else if ($file->modal_type == "App\Models\LessonTopic") {

                        $file->file_thumbnail = $request->thumbnail->store('topics', 'public');
                    } else {

                        $file->file_thumbnail = $request->thumbnail->store('other', 'public');
                    }
                }
            }
            $file->save();

            $response = array(
                'error' => false,
                'message' => trans('data_store_successfully'),
                'data' => $file,
                'code' => 200
            );
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
            return response()->json($response, 200);
        }
    }

    public function deleteFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $file = File::findOrFail($request->file_id);
            $file->delete();
            $response = array(
                'error' => false,
                'message' => trans('data_delete_successfully'),
                'code' => 200
            );
            return response()->json($response);
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
            return response()->json($response, 200);
        }
    }

    public function getAnnouncement(Request $request)
    {
        if (!Auth::user()->can('announcement-list')) {
            $response = array(
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            'class_section_id' => 'nullable|numeric',
            'subject_id' => 'nullable|numeric',
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $teacher = Auth::user()->teacher;
            $subject_teacher_ids = SubjectTeacher::where('teacher_id', $teacher->id);
            if ($request->class_section_id) {
                $subject_teacher_ids = $subject_teacher_ids->where('class_section_id', $request->class_section_id);
            }
            if ($request->subject_id) {
                $subject_teacher_ids = $subject_teacher_ids->where('subject_id', $request->subject_id);
            }
            $subject_teacher_ids = $subject_teacher_ids->get()->pluck('id');
            $sql = Announcement::with('table.subject', 'file')->where('table_type', 'App\Models\SubjectTeacher')->whereIn('table_id', $subject_teacher_ids);

            $data = $sql->orderBy('id', 'DESC')->paginate();
            $response = array(
                'error' => false,
                'message' => 'Announcement Fetched Successfully.',
                'data' => $data,
                'code' => 200,
            );
            return response()->json($response);
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
            return response()->json($response, 200);
        }
    }

    public function sendAnnouncement(Request $request)
    {
        if (!Auth::user()->can('announcement-create')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            'class_section_id' => 'required|numeric',
            'subject_id' => 'required|numeric',
            'title' => 'required',
            'description' => 'nullable',
            'file' => 'nullable'
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $data = getSettings('session_year');
            $teacher_id = Auth::user()->teacher->id;
            $announcement = new Announcement();
            $announcement->title = $request->title;
            $announcement->description = $request->description;
            $announcement->session_year_id = $data['session_year'];

            $subject_teacher = SubjectTeacher::where(['teacher_id' => $teacher_id, 'class_section_id' => $request->class_section_id, 'subject_id' => $request->subject_id])->with('subject')->firstOrFail();
            if ($subject_teacher) {
                $announcement->table()->associate($subject_teacher);
            }
            $user = Students::select('user_id')->where('class_section_id', $request->class_section_id)->get()->pluck('user_id');


            $title = 'New announcement in ' . $subject_teacher->subject->name;
            $body = $request->title;
            $image = null;
            $userinfo = null;

            $announcement->save();
            send_notification($user, $title, $body, 'class_section', $image, $userinfo);
            if ($request->hasFile('file')) {
                foreach ($request->file as $file_upload) {
                    $file = new File();
                    $file->file_name = $file_upload->getClientOriginalName();
                    $file->type = 1;
                    $file->file_url = $file_upload->store('announcement', 'public');
                    $file->modal()->associate($announcement);
                    $file->save();
                }
            }

            $response = array(
                'error' => false,
                'message' => trans('data_store_successfully'),
                'code' => 200,
            );
        } catch (\Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

    public function updateAnnouncement(Request $request)
    {
        if (!Auth::user()->can('announcement-edit')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            'announcement_id' => 'required|numeric',
            'class_section_id' => 'required|numeric',
            'subject_id' => 'required|numeric',
            'title' => 'required'
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $teacher_id = Auth::user()->teacher->id;
            $announcement = Announcement::findOrFail($request->announcement_id);
            $announcement->title = $request->title;
            $announcement->description = $request->description;

            $subject_teacher = SubjectTeacher::where(['teacher_id' => $teacher_id, 'class_section_id' => $request->class_section_id, 'subject_id' => $request->subject_id])->with('subject')->firstOrFail();
            $announcement->table()->associate($subject_teacher);
            $user = Students::select('user_id')->where('class_section_id', $request->class_section_id)->get()->pluck('user_id');

            $title = 'Update announcement in ' . $subject_teacher->subject->name;
            $body = $request->title;
            $image = null;
            $userinfo = null;

            $announcement->save();
            send_notification($user, $title, $body, 'class_section',$image, $userinfo);
            if ($request->hasFile('file')) {
                foreach ($request->file as $file_upload) {
                    $file = new File();
                    $file->file_name = $file_upload->getClientOriginalName();
                    $file->type = 1;
                    $file->file_url = $file_upload->store('announcement', 'public');
                    $file->modal()->associate($announcement);
                    $file->save();
                }
            }
            $response = [
                'error' => false,
                'message' => trans('data_update_successfully'),
                'code' => 200,
            ];
        } catch (Throwable $e) {
            $response = [
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            ];
        }
        return response()->json($response);
    }

    public function deleteAnnouncement(Request $request)
    {
        if (!Auth::user()->can('announcement-delete')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message'),
                'code' => 111
            );
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            'announcement_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $announcement = Announcement::findorFail($request->announcement_id);
            $announcement->delete();
            $response = array(
                'error' => false,
                'message' => trans('data_delete_successfully'),
                'code' => 200
            );
        } catch (Throwable $e) {
            $response = [
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            ];
        }
        return response()->json($response);
    }

    public function getAttendance(Request $request)
    {


        if (!Auth::user()->can('attendance-list')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return response()->json($response);
        }

        $class_section_id = $request->class_section_id;
        $attendance_type = $request->type;
        $date = date('Y-m-d', strtotime($request->date));

        $validator = Validator::make($request->all(), [
            'class_section_id' => 'required',
            'date' => 'required|date',
            'type' => 'in:0,1',
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $sql = Attendance::where('class_section_id', $class_section_id)->where('date', $date);
            if (isset($attendance_type) && $attendance_type != '') {
                $sql->where('type', $attendance_type);
            }
            $data = $sql->get();
            $holiday = Holiday::where('date', $date)->get();
            if ($holiday->count()) {
                $response = array(
                    'error' => false,
                    'data' => $data,
                    'is_holiday' => true,
                    'holiday' => $holiday,
                    'message' => "Data Fetched Successfully",
                );
            } else {
                if ($data->count()) {
                    $response = array(
                        'error' => false,
                        'data' => $data,
                        'is_holiday' => false,
                        'message' => "Data Fetched Successfully",
                    );
                } else {
                    $response = array(
                        'error' => false,
                        'data' => $data,
                        'is_holiday' => false,
                        'message' => "Attendance not recorded",
                    );
                }
            }
            return response()->json($response);
        } catch (\Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'data' => $e
            );
        }
    }


    public function submitAttendance(Request $request)
    {
        if (!Auth::user()->can('attendance-create') || !Auth::user()->can('attendance-edit')) {
            $response = array(
                'error' => true,
                'message' => trans('no_permission_message')
            );
            return response()->json($response);
        }
        $validator = Validator::make($request->all(), [
            'class_section_id' => 'required',
            // 'student_id' => 'required',
            'attendance.*.student_id' => 'required',
            'attendance.*.type' => 'required|in:0,1',
            'date' => 'required|date',
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()

            );
            return response()->json($response);
        }
        try {
            $session_year = getSettings('session_year');
            $session_year_id = $session_year['session_year'];
            $class_section_id = $request->class_section_id;
            $date = date('Y-m-d', strtotime($request->date));
            $getid = Attendance::select('id')->where(['date' => $date, 'class_section_id' => $class_section_id])->get()->toArray();
            for ($i = 0; $i < count($request->attendance); $i++) {

                if (count($getid) > 0 && isset($getid[$i]['id'])) {
                    $attendance = Attendance::find($getid[$i]['id']);
                } else {
                    $attendance = new Attendance();
                }


                $std_id = $request->attendance[$i]['student_id'];
                $type = $request->attendance[$i]['type'];
                $attendance->class_section_id = $class_section_id;
                $attendance->student_id = $std_id;
                $attendance->session_year_id = $session_year_id;
                if ($request->holiday != '' && $request->holiday == 3) {
                    $attendance->type = $request->holiday;
                } else {
                    $attendance->type = $type;

                    if($attendance->status == 0){
                        if($request->$type == 0)
                        {
                            $student = Students::with('user')->where('id',$std_id)->first();
                            $father_id = Students::where('id',$std_id)->pluck('father_id');
                            $mother_id = Students::where('id',$std_id)->pluck('mother_id');

                            $user = Parents::where('id',$father_id)->orwhere('id',$mother_id)->pluck('user_id');
                            $title ='Attendance Alert';
                            $body = $student->user->first_name .' '. $student->user->last_name .' '.'is Absent on' .' '.date('d-m-Y', strtotime($date));;
                            $type = 'attendance';
                            $image = null;
                            $userinfo = null;

                            $notification = new Notification();
                            $notification->send_to = 3;
                            $notification->title = $title;
                            $notification->message = $body;
                            $notification->type = $type;
                            $notification->date = Carbon::now();
                            $notification->is_custom = 0;
                            $notification->save();

                            foreach($user as $data)
                            {
                                $user_notification = new UserNotification();
                                $user_notification->notification_id = $notification->id;
                                $user_notification->user_id = $data;
                                $user_notification->save();
                            }
                            send_notification($user, $title, $body, $type, $image, $userinfo);

                        }
                    }
                }
                $attendance->status = 1;
                $attendance->date = $date;
                $attendance->save();

                $response = [
                    'error' => false,
                    'message' => trans('data_store_successfully')
                ];
            }
        } catch (Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'data' => $e

            );
        }
        return response()->json($response);
    }
    public function getStudentList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_section_id' => 'required|numeric',
            'subject_id' => 'nullable',
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {

            $user = Auth::user()->teacher;
            $class_section_id = $request->class_section_id;
            $get_class_section_id = ClassTeacher::where('class_section_id', $class_section_id)->where('class_teacher_id', $user->id)->get()->pluck('class_section_id');
            $sql = Students::with('user:id,first_name,last_name,image,gender,dob,current_address,permanent_address', 'class_section')->whereIn('class_section_id', $get_class_section_id);

            $data = $sql->orderBy('id')->get();

            if (isset($request->subject_id)) {
                $class_id= ClassSection::where('id',$class_section_id)->pluck('class_id');
                $class_subject = ClassSubject::where('subject_id',$request->subject_id)->where('class_id',$class_id)->first();

                if($class_subject->type == "Elective")
                {
                    foreach($data as $student)
                    {
                        $student_id[]=$student->id;

                    }
                    $student_subject = StudentSubject::whereIn('student_id', $student_id)->where('subject_id', $request->subject_id)->where('class_section_id', $class_section_id)->pluck('student_id');

                   if($student_subject)
                   {
                        $sql = Students::with('user:id,first_name,last_name,image,gender,dob,current_address,permanent_address', 'class_section')->whereIn('id', $student_subject);
                        $data = $sql->orderBy('id')->get();
                   }

                }
                else
                {
                    $sql = Students::with('user:id,first_name,last_name,image,gender,dob,current_address,permanent_address', 'class_section')->where('class_section_id', $class_section_id);
                    $data = $sql->orderBy('id')->get();
                }
            }

            $response = array(
                'error' => false,
                'message' => "Student Details Fetched Successfully",
                'data' => $data,
                'code' => 200,
            );
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }
    public function getStudentDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $student_data_ids = Students::select('user_id', 'class_section_id', 'father_id', 'mother_id', 'guardian_id')->where('id', $request->student_id)->get();
            $student_total_present = Attendance::where('student_id', $request->student_id)->where('type', 1)->count();
            $student_total_absent = Attendance::where('student_id', $request->student_id)->where('type', 0)->count();

            $today_date_string = Carbon::now();
            $today_date_string->toDateTimeString();
            $today_date = date('Y-m-d', strtotime($today_date_string));

            $student_today_attendance = Attendance::where('student_id', $request->student_id)->where('date', $today_date)->get();
            if ($student_today_attendance->count()) {
                foreach ($student_today_attendance as $student_attendance) {
                    if ($student_attendance['type'] == 1) {
                        $today_attendance = 'Present';
                    } else {
                        $today_attendance = 'Absent';
                    }
                }
            } else {
                $today_attendance = 'Not Taken';
            }
            foreach ($student_data_ids as $student_data_ids) {
                $father_data = Parents::where('id', $student_data_ids['father_id'])->get();
                $mother_data = Parents::where('id', $student_data_ids['mother_id'])->get();
                if ($student_data_ids['guardian_id'] != 0) {
                    $guardian_data = Parents::where('id', $student_data_ids['guardian_id'])->get();
                    $response = array(
                        'error' => false,
                        'message' => "Student Details Fetched Successfully",
                        'gurdian_data' => $guardian_data,
                        'father_data' => $father_data,
                        'mother_data' => $mother_data,
                        'total_present' => $student_total_present,
                        'total_absent' => $student_total_absent,
                        'today_attendance' => $today_attendance,
                        'code' => 200,
                    );
                } else {
                    $response = array(
                        'error' => false,
                        'message' => "Student Details Fetched Successfully",
                        'father_data' => $father_data,
                        'mother_data' => $mother_data,
                        'total_present' => $student_total_present,
                        'total_absent' => $student_total_absent,
                        'today_attendance' => $today_attendance,
                        'code' => 200,
                    );
                }
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
    }

    public function getTeacherTimetable(Request $request)
    {
        try {
            $teacher = $request->user()->teacher;
            $subject_id = SubjectTeacher::where('teacher_id',$teacher->id)->pluck('id');
            $timetable = Timetable::whereIn('subject_teacher_id', $subject_id)->with('class_section', 'subject')->get();

            $response = array(
                'error' => false,
                'message' => "Timetable Fetched Successfully",
                'data' => $timetable,
                'code' => 200,
            );
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

    public function submitExamMarksBySubjects(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_section_id' => 'required|numeric',
            'exam_id' => 'required|numeric',
            'subject_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }

        try {
            $exam_published = Exam::where(['id' => $request->exam_id, 'publish' => 1])->first();
            if (isset($exam_published)) {
                $response = array(
                    'error' => true,
                    'message' => trans('exam_published'),
                    'code' => 400,
                );
                return response()->json($response);
            }

            $teacher_id = Auth::user()->teacher->id;
            $class_id = ClassSection::where('id', $request->class_section_id)->pluck('class_id');


            //check exam status
            $starting_date_db = ExamTimetable::select(DB::raw("min(date)"))->where(['exam_id' => $request->exam_id, 'class_id' => $class_id])->first();
            $starting_date = $starting_date_db['min(date)'];
            $ending_date_db = ExamTimetable::select(DB::raw("max(date)"))->where(['exam_id' => $request->exam_id, 'class_id' => $class_id])->first();
            $ending_date = $ending_date_db['max(date)'];
            $currentTime = Carbon::now();
            $current_date = date($currentTime->toDateString());
            if ($current_date >= $starting_date && $current_date <= $ending_date) {
                $exam_status = "1"; // Upcoming = 0 , On Going = 1 , Completed = 2
            } elseif ($current_date < $starting_date) {
                $exam_status = "0"; // Upcoming = 0 , On Going = 1 , Completed = 2
            } else {
                $exam_status = "2"; // Upcoming = 0 , On Going = 1 , Completed = 2
            }
            if ($exam_status != 2) {
                $response = array(
                    'error' => true,
                    'message' => trans('exam_not_completed_yet'),
                    'code' => 400
                );
                return response()->json($response);
            } else {
                $grades = Grade::orderBy('ending_range', 'desc')->get();
                $exam_timetable = ExamTimetable::where('exam_id', $request->exam_id)->where('subject_id', $request->subject_id)->firstOrFail();
                foreach ($request->marks_data as $marks) {
                    $passing_marks = $exam_timetable->passing_marks;
                    if ($marks['obtained_marks'] >= $passing_marks) {
                        $status = 1;
                    } else {
                        $status = 0;
                    }
                    $marks_percentage = ($marks['obtained_marks'] / $exam_timetable['total_marks']) * 100;

                    $exam_grade = findExamGrade($marks_percentage);
                    if ($exam_grade == null) {
                        $response = array(
                            'error' => true,
                            'message' => trans('grades_data_does_not_exists'),
                        );
                        return response()->json($response);
                    }

                    $exam_marks = ExamMarks::where(['exam_timetable_id' => $exam_timetable->id, 'subject_id' => $request->subject_id, 'student_id' => $marks['student_id']])->first();
                    if ($exam_marks) {
                        $exam_marks_db = ExamMarks::find($exam_marks->id);
                        $exam_marks_db->obtained_marks = $marks['obtained_marks'];
                        $exam_marks_db->passing_status = $status;
                        $exam_marks_db->grade = $exam_grade;
                        $exam_marks_db->save();

                        $response = array(
                            'error' => false,
                            'message' => trans('data_update_successfully'),
                            'code' => 200
                        );
                    } else {
                        $exam_result_marks[] = array(
                            'exam_timetable_id' => $exam_timetable->id,
                            'student_id' => $marks['student_id'],
                            'subject_id' => $request->subject_id,
                            'obtained_marks' => $marks['obtained_marks'],
                            'passing_status' => $status,
                            'session_year_id' => $exam_timetable->session_year_id,
                            'grade' => $exam_grade,
                        );
                    }
                }
                if (isset($exam_result_marks)) {
                    ExamMarks::insert($exam_result_marks);
                    $response = array(
                        'error' => false,
                        'message' => trans('data_store_successfully'),
                        'code' => 200
                    );
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }


    public function submitExamMarksByStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_section_id' =>'required',
            'exam_id' => 'required|numeric',
            'student_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $exam_published = Exam::where(['id' => $request->exam_id, 'publish' => 1])->first();
            if (isset($exam_published)) {
                $response = array(
                    'error' => true,
                    'message' => trans('exam_published'),
                    'code' => 400,
                );
                return response()->json($response);
            }

            $teacher_id = Auth::user()->teacher->id;
            // $class_section_id = Students::where('id',$request->student_id)->pluck('class_section_id');
            $class_id = ClassSection::where('id', $request->class_section_id)->pluck('class_id');

            //exam status
            $starting_date_db = ExamTimetable::select(DB::raw("min(date)"))->where(['exam_id' => $request->exam_id, 'class_id' => $class_id])->first();
            $starting_date = $starting_date_db['min(date)'];
            $ending_date_db = ExamTimetable::select(DB::raw("max(date)"))->where(['exam_id' => $request->exam_id, 'class_id' => $class_id])->first();
            $ending_date = $ending_date_db['max(date)'];
            $currentTime = Carbon::now();
            $current_date = date($currentTime->toDateString());
            if ($current_date >= $starting_date && $current_date <= $ending_date) {
                $exam_status = "1"; // Upcoming = 0 , On Going = 1 , Completed = 2
            } elseif ($current_date < $starting_date) {
                $exam_status = "0"; // Upcoming = 0 , On Going = 1 , Completed = 2
            } else {
                $exam_status = "2"; // Upcoming = 0 , On Going = 1 , Completed = 2
            }

            if ($exam_status != 2) {
                $response = array(
                    'error' => true,
                    'message' => trans('exam_not_completed_yet'),
                    'code' => 400
                );
                return response()->json($response);
            } else {
                $grades = Grade::orderBy('ending_range', 'desc')->get();

                foreach ($request->marks_data as $marks) {
                    $exam_timetable = ExamTimetable::where(['exam_id' => $request->exam_id, 'subject_id' => $marks['subject_id']])->firstOrFail();
                    $passing_marks = $exam_timetable->passing_marks;
                    if ($marks['obtained_marks'] >= $passing_marks) {
                        $status = 1;
                    } else {
                        $status = 0;
                    }
                    $marks_percentage = ($marks['obtained_marks'] / $exam_timetable->total_marks) * 100;

                    $exam_grade = findExamGrade($marks_percentage);
                    if ($exam_grade == null) {
                        $response = array(
                            'error' => true,
                            'message' => trans('grades_data_does_not_exists'),
                        );
                        return response()->json($response);
                    }

                    $exam_marks = ExamMarks::where(['exam_timetable_id' => $exam_timetable->id, 'student_id' => $request->student_id, 'subject_id' => $marks['subject_id']])->first();
                    if ($exam_marks) {
                        $exam_marks_db = ExamMarks::find($exam_marks->id);
                        $exam_marks_db->obtained_marks = $marks['obtained_marks'];
                        $exam_marks_db->passing_status = $status;
                        $exam_marks_db->grade = $exam_grade;
                        $exam_marks_db->save();

                        $response = array(
                            'error' => false,
                            'message' => trans('data_update_successfully'),
                            'code' => 200,
                        );
                    } else {
                        $exam_result_marks[] = array(
                            'exam_timetable_id' => $exam_timetable->id,
                            'student_id' => $request->student_id,
                            'subject_id' => $marks['subject_id'],
                            'obtained_marks' => $marks['obtained_marks'],
                            'passing_status' => $status,
                            'session_year_id' => $exam_timetable->session_year_id,
                            'grade' => $exam_grade,
                        );
                    }
                }
                if (isset($exam_result_marks)) {
                    ExamMarks::insert($exam_result_marks);
                    $response = array(
                        'error' => false,
                        'message' => trans('data_store_successfully'),
                        'code' => 200,
                    );
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }


    public function GetStudentExamResult(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|nullable'
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {

            $teacher_id = Auth::user()->teacher->id;
            $class_section_id = Students::where('id',$request->student_id)->pluck('class_section_id');

            $class_data = ClassSection::where('id', $class_section_id)->with('class.medium', 'section')->get()->first();

            $exam_marks_db = ExamClass::with(['exam.timetable' => function ($q) use ($request, $class_data) {
                $q->where('class_id', $class_data->class_id)->with(['exam_marks' => function ($q) use ($request) {
                    $q->where('student_id', $request->student_id);
                }])->with('subject:id,name,type,image,code');
            }])->with(['exam.results' => function ($q) use ($request) {
                $q->where('student_id', $request->student_id)->with(['student' => function ($q) {
                    $q->select('id', 'user_id', 'roll_number')->with('user:id,first_name,last_name');
                }])->with('session_year:id,name');
            }])->where('class_id', $class_data->class_id)->get();

            if (sizeof($exam_marks_db)) {
                foreach ($exam_marks_db as $data_db) {
                    $starting_date_db = ExamTimetable::select(DB::raw("min(date)"))->where(['exam_id' => $data_db->exam_id, 'class_id' => $class_data->class_id])->first();
                    $starting_date = $starting_date_db['min(date)'];
                    $ending_date_db = ExamTimetable::select(DB::raw("max(date)"))->where(['exam_id' => $data_db->exam_id, 'class_id' => $class_data->class_id])->first();
                    $ending_date = $ending_date_db['max(date)'];
                    $currentTime = Carbon::now();
                    $current_date = date($currentTime->toDateString());
                    if ($current_date >= $starting_date && $current_date <= $ending_date) {
                        $exam_status = "1"; // Upcoming = 0 , On Going = 1 , Completed = 2
                    } elseif ($current_date < $starting_date) {
                        $exam_status = "0"; // Upcoming = 0 , On Going = 1 , Completed = 2
                    } else {
                        $exam_status = "2"; // Upcoming = 0 , On Going = 1 , Completed = 2
                    }

                    // check wheather exam is completed or not
                    if ($exam_status == 2) {
                        $marks_array = array();

                        // check wheather timetable exists or not
                        if (sizeof($data_db->exam->timetable)) {
                            foreach ($data_db->exam->timetable as $timetable_db) {
                                $total_marks = $timetable_db->total_marks;
                                $exam_marks = array();
                                if (sizeof($timetable_db->exam_marks)) {
                                    foreach ($timetable_db->exam_marks as $marks_data) {
                                        $exam_marks = array(
                                            'marks_id' => $marks_data->id,
                                            'subject_name' => $marks_data->subject->name,
                                            'subject_type' => $marks_data->subject->type,
                                            'total_marks' => $total_marks,
                                            'obtained_marks' => $marks_data->obtained_marks,
                                            'grade' => $marks_data->grade,
                                        );
                                    }
                                } else {
                                    $exam_marks = (object)[];

                                }
                                if($exam_marks != (object)[] )
                                {
                                    $marks_array[] = array(
                                        'subject_id' => $timetable_db->subject->id,
                                        'subject_name' => $timetable_db->subject->name,
                                        'subject_type' => $timetable_db->subject->type,
                                        'total_marks' => $total_marks,
                                        'subject_code' => $timetable_db->subject->code,
                                        'marks' => $exam_marks
                                    );
                                }


                            }

                            $exam_result = array();
                            if (sizeof($data_db->exam->results)) {
                                foreach ($data_db->exam->results as $result_data) {
                                    $exam_result = array(
                                        'result_id' => $result_data->id,
                                        'exam_id' => $result_data->exam_id,
                                        'exam_name' => $data_db->exam->name,
                                        'class_name' => $class_data->class->name . '-' . $class_data->section->name . ' ' . $class_data->class->medium->name,
                                        'student_name' => $result_data->student->user->first_name . ' ' . $result_data->student->user->last_name,
                                        'exam_date' => $starting_date,
                                        'total_marks' => $result_data->total_marks,
                                        'obtained_marks' => $result_data->obtained_marks,
                                        'percentage' => $result_data->percentage,
                                        'grade' => $result_data->grade,
                                        'session_year' => $result_data->session_year->name,
                                    );
                                }
                            } else {
                                $exam_result = (object)[];
                            }
                            if($marks_array != null && $exam_result != null)
                            {
                                $data[] = array(
                                    'exam_id' => $data_db->exam_id,
                                    'exam_name' => $data_db->exam->name,
                                    'exam_date' => $starting_date,
                                    'marks_data' => $marks_array,
                                    'result' => $exam_result
                                );
                            }

                        }
                    }
                }
                $response = array(
                    'error' => false,
                    'message' => "Exam Marks Fetched Successfully",
                    'data' => isset($data) ? $data : [],
                    'code' => 200,
                );

            } else {
                $response = array(
                    'error' => false,
                    'message' => "Exam Marks Fetched Successfully",
                    'data' => [],
                    'code' => 200,
                );
            }
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

    public function GetStudentExamMarks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|nullable'
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $teacher_id = Auth::user()->teacher->id;
            $class_section_id = Students::where('id',$request->student_id)->pluck('class_section_id');

            $class_data = ClassSection::where('id', $class_section_id)->with('class.medium', 'section')->get()->first();

            $exam_marks_db = ExamClass::with(['exam.timetable' => function ($q) use ($request, $class_data) {
                $q->where('class_id', $class_data->class_id)->with(['exam_marks' => function ($q) use ($request) {
                    $q->where('student_id', $request->student_id);
                }])->with('subject:id,name,type,image');
            }])->where('class_id', $class_data->class_id)->get();

            if (sizeof($exam_marks_db)) {
                foreach ($exam_marks_db as $data_db) {
                    $marks_array = array();
                    foreach ($data_db->exam->timetable as $marks_db) {
                        $exam_marks = array();
                        if (sizeof($marks_db->exam_marks)) {
                            foreach ($marks_db->exam_marks as $marks_data) {
                                $exam_marks = array(
                                    'marks_id' => $marks_data->id,
                                    'subject_name' => $marks_data->subject->name,
                                    'subject_type' => $marks_data->subject->type,
                                    'total_marks' => $marks_data->timetable->total_marks,
                                    'obtained_marks' => $marks_data->obtained_marks,
                                    'grade' => $marks_data->grade,
                                );
                            }
                        } else {
                            $exam_marks = [];
                        }
                        if($exam_marks != [])
                        {
                            $marks_array[] = array(
                                'subject_id' => $marks_db->subject->id,
                                'subject_name' => $marks_db->subject->name,
                                'marks' => $exam_marks
                            );
                        }

                    }
                    $data[] = array(
                        'exam_id' => $data_db->exam_id,
                        'exam_name' => $marks_db->exam->name,
                        'marks_data' => $marks_array
                    );
                }
                $response = array(
                    'error' => false,
                    'message' => "Exam Marks Fetched Successfully",
                    'data' => $data,
                    'code' => 200,
                );
            } else {
                $response = array(
                    'error' => false,
                    'message' => "Exam Marks Fetched Successfully",
                    'data' => [],
                    'code' => 200,
                );
            }
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

   public function getExamList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'in:0,1,2,3',
            'publish' => 'in:0,1',
            'class_section_id' => 'nullable',
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {

            $teacher = Auth::user()->teacher;

            if (isset($request->class_section_id)) {

                $class_ids = ClassSection::with('class')->where('id', $request->class_section_id)->pluck('class_id');
                $class_section = ClassSection::with('class','section','class.medium','class.streams')->where('id', $request->class_section_id)->first();
            } else {

                $class_section_ids = ClassTeacher::where('class_teacher_id', $teacher->id)->pluck('class_section_id');
                $class_ids = ClassSection::with('class')->whereIn('id', $class_section_ids)->pluck('class_id');
                $class_sections = ClassSection::with('class','section','class.medium','class.streams')->whereIn('id', $class_section_ids)->get();

            }

            $sql = ExamClass::with('exam.session_year:id,name','class','class.medium','class.streams')->whereIn('class_id', $class_ids);

            if (isset($request->publish)) {
                $publish = $request->publish;
                $sql->whereHas('exam', function ($q) use ($publish) {
                    $q->where('publish', $publish);
                });
            }
            $exam_data_db = $sql->get();
            foreach ($exam_data_db as $data) {

                // date status
                $starting_date_db = ExamTimetable::select(DB::raw("min(date)"))->where('exam_id',$data->exam_id)->whereIn( 'class_id',$class_ids)->first();
                $starting_date = $starting_date_db['min(date)'];

                $ending_date_db = ExamTimetable::select(DB::raw("max(date)"))->where('exam_id', $data->exam_id)->whereIn( 'class_id',$class_ids)->first();
                $ending_date = $ending_date_db['max(date)'];

                $currentTime = Carbon::now();
                $current_date = date($currentTime->toDateString());
                if ($current_date >= $starting_date && $current_date <= $ending_date) {
                    $exam_status = "1"; // Upcoming = 0 , On Going = 1 , Completed = 2
                } elseif ($current_date < $starting_date) {
                    $exam_status = "0"; // Upcoming = 0 , On Going = 1 , Completed = 2
                } else {
                    $exam_status = "2"; // Upcoming = 0 , On Going = 1 , Completed = 2
                }

                // $request->status  =  0 :- all exams , 1 :- Upcoming , 2 :- On Going , 3 :- Completed

                if (isset($request->status)) {
                    if ($request->status == 0) {
                        $exam_data[] = array(
                            'id' => $data->exam->id,
                            'name' => $data->exam->name,
                            'description' => $data->exam->description,
                            'publish' => $data->exam->publish,
                            'session_year' => $data->exam->session_year->name,
                            'exam_starting_date' => $starting_date,
                            'exam_ending_date' => $ending_date,
                            'exam_status' => $exam_status,
                            'class_id' => $data->class_id,
                            'class_name' => $data->class->name .'-'. $data->class->medium->name,
                            'class_streams' => $data->class->streams->name ?? null,
                        );
                    } else if ($request->status == 1) {
                        if ($exam_status == 0) {
                            $exam_data[] = array(
                                'id' => $data->exam->id,
                                'name' => $data->exam->name,
                                'description' => $data->exam->description,
                                'publish' => $data->exam->publish,
                                'session_year' => $data->exam->session_year->name,
                                'exam_starting_date' => $starting_date,
                                'exam_ending_date' => $ending_date,
                                'exam_status' => $exam_status,
                                'class_id' => $data->class_id,
                                'class_name' => $data->class->name .'-'. $data->class->medium->name,
                                'class_streams' => $data->class->streams->name ?? null,
                            );
                        }
                    } else if ($request->status == 2) {
                        if ($exam_status == 1) {
                            $exam_data[] = array(
                                'id' => $data->exam->id,
                                'name' => $data->exam->name,
                                'description' => $data->exam->description,
                                'publish' => $data->exam->publish,
                                'session_year' => $data->exam->session_year->name,
                                'exam_starting_date' => $starting_date,
                                'exam_ending_date' => $ending_date,
                                'exam_status' => $exam_status,
                                'class_id' => $data->class_id,
                                'class_name' => $data->class->name .'-'. $data->class->medium->name,
                                'class_streams' => $data->class->streams->name ?? null,
                            );
                        }
                    } else {
                        if ($exam_status == 2) {
                            $exam_data[] = array(
                                'id' => $data->exam->id,
                                'name' => $data->exam->name,
                                'description' => $data->exam->description,
                                'publish' => $data->exam->publish,
                                'session_year' => $data->exam->session_year->name,
                                'exam_starting_date' => $starting_date,
                                'exam_ending_date' => $ending_date,
                                'exam_status' => $exam_status,
                                'class_id' => $data->class_id,
                                'class_name' => $data->class->name .'-'. $data->class->medium->name,
                                'class_streams' => $data->class->streams->name ?? null,
                            );
                        }
                    }
                }
               else {
                    $exam_data[] = array(
                        'id' => $data->exam->id,
                        'name' => $data->exam->name,
                        'description' => $data->exam->description,
                        'publish' => $data->exam->publish,
                        'session_year' => $data->exam->session_year->name,
                        'exam_starting_date' => $starting_date,
                        'exam_ending_date' => $ending_date,
                        'exam_status' => $exam_status,
                        'class_id' => $data->class_id,
                        'class_name' => $data->class->name .'-'. $data->class->medium->name,
                        'class_streams' => $data->class->streams->name ?? null,
                    );
                }
            }

            $response = array(
                'error' => false,
                'data' => isset($exam_data) ? $exam_data : [],
                'code' => 200,
            );
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

    public function getExamDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|nullable',
            'class_id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try {
            $teacher = Auth::user()->teacher;
            $class_id = $request->class_id;
            $class_section = ClassSection::with('class','section','class.medium','class.streams')->where('class_id', $class_id)->first();

            $exam_data = Exam::with(['timetable' => function ($q) use ($request, $class_id) {
                $q->where(['exam_id' => $request->exam_id, 'class_id' => $class_id])->with('subject');
            }])->where('id', $request->exam_id)->get();
            $response = array(
                'error' => false,
                'class_id' => $class_id,
                'class_section_id' => $class_section->id,
                'class_name' => $class_section->class->name .'-'. $class_section->section->name .' '.$class_section->class->medium->name,
                'stream_name' => $class_section->class->streams->name ?? null,
                'data' => $exam_data,
                'code' => 200,
            );
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }
    public function getProfileDetails()
    {
        try{
            $user = Auth::user()->load(['teacher']);
            $response = array(
                'error' => false,
                'message' => 'Data Fetched Successfully',
                'data' => $user,
                'code' => 200,
            );
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

    public function getNotifications(Request $request){
        try{
            $user = $request->user()->id;
            $notification_id = UserNotification::where('user_id',$user)->pluck('notification_id');
            $notification = Notification::whereIn('id',$notification_id)->latest()->paginate();
            $response = array(
                'error' => false,
                'data' => $notification ?? '',
                'code' => 200,
            );
        }catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);

    }

    public function getChatUserList(Request $request)
    {
        try {

            $offset = $request->offset;
            $limit = $request->limit;
            $user_type = $request->isParent;
            $search = $request->search;

            $session_year = getSettings('session_year');
            $subject_teacher_section_ids = [];
            $class_teacher_section_ids = [];

            $teacher = $request->user()->teacher;

            $class_section_ids = ClassTeacher::with('class_section')->where('class_teacher_id', $teacher->id)->pluck('class_section_id')->toArray();

            $subject_teachers = SubjectTeacher::with('class_section')->where('teacher_id', $teacher->id)->whereNotIn('class_section_id', $class_section_ids)->groupBy('class_section_id')->get();
            $data = [];
            $parents_ids = [];


            if($class_section_ids)
            {
                $students = Students::with(['user','class_section.class','student_subjects.subject'])->whereIn('class_section_id', $class_section_ids)->get();

                foreach ($students as $student) {
                    $parents_ids[] = $student->father_id;
                    $parents_ids[] = $student->mother_id;
                    $parents_ids[] = $student->guardian_id;
                }

               $parents_ids = array_filter(array_unique($parents_ids));

                if($user_type == 0)
                {
                    foreach ($students as $student) {
                        $unreadCount = 0;
                        if($student->user_id != 0)
                        {
                            $lastMessage = ChatMessage::with('file')->where(function ($query) use ($student,$teacher) {
                                $query->where('modal_id', $student->user_id)
                                    ->where('sender_id', $teacher->user->id);
                            })
                            ->orWhere(function ($query) use ($student,$teacher) {
                                $query->where('modal_id', $teacher->user->id)
                                    ->where('sender_id', $student->user_id);
                            })
                            ->select('id','body', 'date')
                            ->latest()
                            ->first();

                            $lastReadMessage = ReadMessage::where('modal_id',$teacher->user->id)->where('user_id',$student->user_id)->first();

                            if ($lastReadMessage) {

                                $lastReadMessageId = $lastReadMessage->last_read_message_id;
                                if(!empty($lastReadMessageId))
                                {
                                    $unreadCount = ChatMessage::where('sender_id',$student->user_id)->where('modal_id',$teacher->user->id)->where('id', '>', $lastReadMessageId)->count();
                                }else{
                                    $unreadCount = ChatMessage::where('sender_id',$student->user_id)->where('modal_id',$teacher->user->id)->count();
                                }

                            }

                            $student_subject = $student->subjects();

                            $core_subjects= array_column($student_subject["core_subject"],'subject_id');

                            $elective_subjects = $student_subject["elective_subject"] ?? [];
                            if ($elective_subjects) {
                                $elective_subjects = $elective_subjects->pluck('subject_id')->toArray();
                            }
                            $subject_id = array_merge($core_subjects,$elective_subjects);


                            $subjects = Subject::whereIn('id',$subject_id)->select('id','name')->get();

                            $data[] = [
                                'id' => $student->id,
                                'user_id' => $student->user_id, // Assuming this is the correct property name
                                'first_name' => $student->user->first_name ?? '',
                                'last_name' => $student->user->last_name ?? '',
                                'image' => $student->user->image ?? '',
                                'roll_no' => $student->roll_number,
                                'admission_no' => $student->admission_no,
                                'gender' => $student->user->gender,
                                'dob' => $student->user->dob,
                                'subjects' => $subjects,
                                'address' => $student->user->current_address,
                                'last_message' => $lastMessage ?? null,
                                'class_name' => $student->class_section->class->name .' '.$student->class_section->section->name .' '.$student->class_section->class->medium->name,
                                'isParent' => $user_type,
                                'unread_message' => $unreadCount ?? 0
                            ];

                        }
                    }
                }
                if($user_type == 1)
                {

                    $parents = Parents::with('user')->whereIn('id',$parents_ids)->get();
                    foreach ($parents as $parent) {
                        $unreadCount= 0;
                        $childArray = [];
                        if($parent->user_id != 0)
                        {
                            $children = $parent->children()->with('user', 'class_section')->get();

                            foreach($children as $child)
                            {
                                $child_subject=$child->subjects();

                                $core_subjects= array_column($child_subject["core_subject"],'subject_id');

                                $elective_subjects = $child_subject["elective_subject"] ?? [];

                                if ($elective_subjects) {
                                    $elective_subjects = $elective_subjects->pluck('subject_id')->toArray();
                                }

                                $subject_id = array_merge($core_subjects,$elective_subjects);

                                $subjects = Subject::whereIn('id',$subject_id)->select('id','name')->get();

                                $childArray[] = [
                                    'id' => $child->id,
                                    'user_id' => $child->user_id,
                                    'child_name' => $child->user->first_name .' '.$child->user->last_name,
                                    'class_name' => $child->class_section->class->name .' '.$child->class_section->section->name .' '.$child->class_section->class->medium->name,
                                    'admission_no' => $child->admission_no,
                                    'image' => $child->user->image,
                                    'subject' => $subjects ?? []
                                ];
                            }

                            $lastMessage = ChatMessage::with('file')->where(function ($query) use ($parent,$teacher) {
                                $query->where('modal_id', $parent->user_id)
                                    ->where('sender_id', $teacher->user->id);
                            })
                            ->orWhere(function ($query) use ($parent,$teacher) {
                                $query->where('modal_id', $teacher->user->id)
                                    ->where('sender_id', $parent->user_id);
                            })
                            ->select('body', 'date')
                            ->latest()
                            ->first();

                            $lastReadMessage = ReadMessage::where('modal_id',$teacher->user->id)->where('user_id', $parent->user_id)->first();

                            if ($lastReadMessage) {

                                $lastReadMessageId = $lastReadMessage->last_read_message_id;
                                if(!empty($lastReadMessageId))
                                {
                                    $unreadCount = ChatMessage::where('sender_id',$parent->user_id)->where('modal_id',$teacher->user->id)->where('id', '>', $lastReadMessageId)->count();
                                }else{
                                    $unreadCount = ChatMessage::where('sender_id',$parent->user_id)->where('modal_id',$teacher->user->id)->count();
                                }

                            }
                            $data[] = [
                                'id' => $parent->id,
                                'user_id' => $parent->user_id, // Assuming this is the correct property name
                                'first_name' => $parent->user->first_name ?? '',
                                'last_name' => $parent->user->last_name ?? '',
                                'email' => $parent->user->email ?? '',
                                'mobile_no' => $parent->user->mobile ?? '',
                                'occupation' =>$parent->occupation ?? '',
                                'image' =>$parent->user->image ?? '',
                                'last_message' => $lastMessage ?? null,
                                'children' => $childArray ?? [],
                                'isParent' => $user_type,
                                'unread_message' => $unreadCount ?? 0
                            ];
                        }
                    }
                }

            }

            if($subject_teachers)
            {

                foreach($subject_teachers as $subject_teacher)
                {
                    $class_subject = ClassSubject::where('subject_id', $subject_teacher->subject_id)->where('class_id', $subject_teacher->class_section->class->id)->first();
                    $students = Students::with(['user','class_section.class','student_subjects.subject'])->where('class_section_id', $subject_teacher->class_section_id)->get();
                    $parents_id =[];

                    $parents_id = $students->groupBy(['father_id', 'mother_id', 'guardian_id'])->keys()->all();

                    $common_parents_ids = array_intersect($parents_ids, $parents_id);

                    $unique_parents_ids = array_diff($parents_id, $common_parents_ids);

                    if($user_type == 0)
                    {
                        foreach ($students as $student) {
                            $unreadCount = 0;
                            if($student->user_id != 0)
                            {
                                $lastMessage = ChatMessage::with('file')->where(function ($query) use ($student,$teacher) {
                                    $query->where('modal_id', $student->user_id)
                                        ->where('sender_id', $teacher->user->id);
                                })
                                ->orWhere(function ($query) use ($student,$teacher) {
                                    $query->where('modal_id', $teacher->user->id)
                                        ->where('sender_id', $student->user_id);
                                })
                                ->select('id','body', 'date')
                                ->latest()
                                ->first();

                                $lastReadMessage = ReadMessage::where('modal_id',$teacher->user->id)->where('user_id',$student->user_id)->first();

                                if ($lastReadMessage) {

                                    $lastReadMessageId = $lastReadMessage->last_read_message_id;
                                    if(!empty($lastReadMessageId))
                                    {
                                        $unreadCount = ChatMessage::where('sender_id',$student->user_id)->where('modal_id',$teacher->user->id)->where('id', '>', $lastReadMessageId)->count();
                                    }else{
                                        $unreadCount = ChatMessage::where('sender_id',$student->user_id)->where('modal_id',$teacher->user->id)->count();
                                    }

                                }

                                $student_subject = $student->subjects();

                                $core_subjects= array_column($student_subject["core_subject"],'subject_id');

                                $elective_subjects = $student_subject["elective_subject"] ?? [];
                                if ($elective_subjects) {
                                    $elective_subjects = $elective_subjects->pluck('subject_id')->toArray();
                                }
                                $subject_id = array_merge($core_subjects,$elective_subjects);


                                $subjects = Subject::whereIn('id',$subject_id)->select('id','name')->get();
                                $subjectArray = [];
                                foreach($subjects as $subject)
                                {
                                    $subjectArray[] = array(
                                        'id' => $subject->id,
                                        'name' => $subject->name
                                    );
                                }
                                if ($class_subject->type == "Elective") {
                                    // dd($student_subject['elective_subject']->pluck('subject_id'));

                                    $student_subject = $student->student_subjects->where('subject_id',$class_subject->subject_id);

                                    if(!empty($student_subject->toArray()))
                                    {
                                        $data[] = [
                                            'id' => $student->id,
                                            'user_id' => $student->user_id, // Assuming this is the correct property name
                                            'first_name' => $student->user->first_name ?? '',
                                            'last_name' => $student->user->last_name ?? '',
                                            'image' => $student->user->image ?? '',
                                            'roll_no' => $student->roll_number,
                                            'admission_no' => $student->admission_no,
                                            'gender' => $student->user->gender,
                                            'dob' => $student->user->dob,
                                            'subjects' =>  $subjectArray,
                                            'address' => $student->user->current_address,
                                            'last_message' => $lastMessage ?? null,
                                            'class_name' => $student->class_section->class->name .' '.$student->class_section->section->name .' '.$student->class_section->class->medium->name,
                                            'isParent' => $user_type,
                                            'unread_message' => $unreadCount ?? 0
                                        ];
                                    }
                                }else {

                                    $data[] = [
                                        'id' => $student->id,
                                        'user_id' => $student->user_id, // Assuming this is the correct property name
                                        'first_name' => $student->user->first_name ?? '',
                                        'last_name' => $student->user->last_name ?? '',
                                        'image' => $student->user->image ?? '',
                                        'roll_no' => $student->roll_number,
                                        'admission_no' => $student->admission_no,
                                        'gender' => $student->user->gender,
                                        'dob' => $student->user->dob,
                                        'subjects' => $subjects,
                                        'address' => $student->user->current_address,
                                        'last_message' => $lastMessage ?? null,
                                        'class_name' => $student->class_section->class->name .' '.$student->class_section->section->name .' '.$student->class_section->class->medium->name,
                                        'isParent' => $user_type,
                                        'unread_message' => $unreadCount ?? 0
                                    ];
                                }

                            }
                        }
                    }

                    if($user_type == 1)
                    {
                        $parents = Parents::with('user')->whereIn('id',$unique_parents_ids)->get();
                        foreach ($parents as $parent) {
                            $unreadCount= 0;
                            $childArray = [];
                            if($parent->user_id != 0)
                            {
                                $lastMessage = ChatMessage::with('file')->where(function ($query) use ($parent,$teacher) {
                                    $query->where('modal_id', $parent->user_id)
                                        ->where('sender_id', $teacher->user->id);
                                })
                                ->orWhere(function ($query) use ($parent,$teacher) {
                                    $query->where('modal_id', $teacher->user->id)
                                        ->where('sender_id', $parent->user_id);
                                })
                                ->select('body', 'date')
                                ->latest()
                                ->first();

                                $lastReadMessage = ReadMessage::where('modal_id',$teacher->user->id)->where('user_id', $parent->user_id)->first();

                                if ($lastReadMessage) {

                                    $lastReadMessageId = $lastReadMessage->last_read_message_id;
                                    if(!empty($lastReadMessageId))
                                    {
                                        $unreadCount = ChatMessage::where('sender_id',$parent->user_id)->where('modal_id',$teacher->user->id)->where('id', '>', $lastReadMessageId)->count();
                                    }else{
                                        $unreadCount = ChatMessage::where('sender_id',$parent->user_id)->where('modal_id',$teacher->user->id)->count();
                                    }

                                }
                                $children = $parent->children()->with('user', 'class_section')->get();

                                foreach($children as $child)
                                {
                                    $child_subject=$child->subjects();

                                    $core_subjects= array_column($child_subject["core_subject"],'subject_id');

                                    $elective_subjects = $child_subject["elective_subject"] ?? [];

                                    if ($elective_subjects) {
                                        $elective_subjects = $elective_subjects->pluck('subject_id')->toArray();
                                    }

                                    $subject_id = array_merge($core_subjects,$elective_subjects);

                                    $subjects = Subject::whereIn('id',$subject_id)->select('id','name')->get();

                                    $childArray[] = [
                                        'id' => $child->id,
                                        'user_id' => $child->user_id,
                                        'child_name' => $child->user->first_name .' '.$child->user->last_name,
                                        'class_name' => $child->class_section->class->name .' '.$child->class_section->section->name .' '.$child->class_section->class->medium->name,
                                        'admission_no' => $child->admission_no,
                                        'image' => $child->user->image,
                                        'subject' => $subjects ?? []
                                    ];
                                }

                                if($class_subject->type == "Elective")
                                {
                                    $student_subject = $child->student_subjects->where('subject_id',$class_subject->subject_id);

                                    if(!empty($student_subject->toArray()))
                                    {
                                        $data[] = [
                                            'id' => $parent->id,
                                            'user_id' => $parent->user_id, // Assuming this is the correct property name
                                            'first_name' => $parent->user->first_name ?? '',
                                            'last_name' => $parent->user->last_name ?? '',
                                            'email' => $parent->user->email ?? '',
                                            'mobile_no' => $parent->user->mobile ?? '',
                                            'occupation' =>$parent->occupation ?? '',
                                            'image' =>$parent->user->image ?? '',
                                            'last_message' => $lastMessage ?? null,
                                            'children' => $childArray ?? [],
                                            'isParent' => $user_type,
                                            'unread_message' => $unreadCount ?? 0
                                        ];
                                    }
                                }else{
                                    $data[] = [
                                        'id' => $parent->id,
                                        'user_id' => $parent->user_id, // Assuming this is the correct property name
                                        'first_name' => $parent->user->first_name ?? '',
                                        'last_name' => $parent->user->last_name ?? '',
                                        'email' => $parent->user->email ?? '',
                                        'mobile_no' => $parent->user->mobile ?? '',
                                        'occupation' =>$parent->occupation ?? '',
                                        'image' =>$parent->user->image ?? '',
                                        'last_message' => $lastMessage ?? null,
                                        'children' => $childArray ?? [],
                                        'isParent' => $user_type,
                                        'unread_message' => $unreadCount ?? 0
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            $total_items = count($data) ?? 0;

            $unreadusers = array_filter($data, function ($user) {
                return $user['unread_message'] > 0;
            });

            $totalunreadusers = count($unreadusers);


            if($search)
            {
                $filteredData = array_filter($data, function ($teacher) use ($search) {
                    $name = $teacher['first_name'] . ' ' . $teacher['last_name'];
                    return stristr($name, $search) !== false;
                });
                $data = collect($filteredData)->sortByDesc(function ($user) {
                    return optional($user['last_message'])->date ?? 0;
                })->splice($offset, $limit)->values();

            }else {
                $data = collect($data)->sortByDesc(function ($user) {
                    return optional($user['last_message'])->date ?? 0;
                })
                ->splice($offset, $limit)
                ->values();
            }

            $response = array(
                'error' => false,
                'message' => 'Data Fetched Successfully',
                'data' => [
                    'items' => $data,
                    'total_items' => $total_items,
                    'total_unread_users' => $totalunreadusers,
                ],
                'code' => 100,
            );

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
            return response()->json($response, 200);
        }
    }

    public function sendMessage(Request $request){
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|numeric',
            'message' => 'required_without:file',
            'file.*' => 'nullable'
        ]);
        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first(),
                'code' => 102,
            );
            return response()->json($response);
        }
        try{
            $sender_id = $request->user()->id;
            $receiver_id = $request->receiver_id;

            $message = new ChatMessage();
            $message->modal_id = $receiver_id;
            $message->modal_type = 'App/Models/User';
            $message->sender_id = $sender_id;
            $message->body = $request->message ?? '';
            $message->date = Carbon::now();
            $message->save();

            $count = 0;
            $unreadCount = 0;

            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $uploadedFile) {

                    $originalName = $uploadedFile->getClientOriginalName();
                    $filePath = $uploadedFile->storeAs('chatfile', $originalName, 'public');

                    $file = new ChatFile();
                    $file->file_type = 1;
                    $file->file_name =  $filePath;
                    $file->message_id = $message->id;
                    $file->save();
                    $count++;
                }
            }

            $readMessage = ReadMessage::where('modal_id',$receiver_id)->where('user_id',$sender_id)->first();
            if(empty($readMessage))
            {
                $readMessage = new ReadMessage();
                $readMessage->modal_id = $receiver_id;
                $readMessage->modal_type = 'App/Models/User';
                $readMessage->user_id = $sender_id;
                $readMessage->save();
            }

            $message = ChatMessage::with('file')->where('id',$message->id)->select('id','sender_id','body','date')->get();

            foreach ($message as $message) {
                $chatfile = [];
                foreach ($message->file as $file) {
                    if(!empty($file)){
                        $chatfile[] =  asset('storage/' . $file->file_name);
                    }else{
                        $chatfile[] = '';
                    }

                }

                $data = array(
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'body' => $message->body,
                    'date' => $message->date,
                    'files' => $chatfile
                );
            }

            $teacher = Teacher::with('user','subjects.subject')->where('user_id',$sender_id)->first();

            $subjectData = [];

            if ($teacher) {
                foreach ($teacher->subjects as $subject) {
                    $subjectData[] = [
                        'id' => $subject->subject->id,
                        'name' => $subject->subject->name,
                    ];
                }
            }


            $lastReadMessage = ReadMessage::where('modal_id',$receiver_id)->where('user_id',$teacher->user_id)->first();

            if ($lastReadMessage) {

                $lastReadMessageId = $lastReadMessage->last_read_message_id;
                if(!empty($lastReadMessageId))
                {
                    $unreadCount = ChatMessage::where('modal_id',$receiver_id)->where('sender_id',$teacher->user_id)->where('id', '>', $lastReadMessageId)->count();
                }else{
                    $unreadCount = ChatMessage::where('modal_id',$receiver_id)->where('sender_id',$teacher->user_id)->count();
                }

            }

            $userinfo = [
                'id' => $teacher->id,
                'user_id' => $teacher->user->id,
                'first_name' => $teacher->user->first_name,
                'last_name' => $teacher->user->last_name,
                'email' => $teacher->user->email,
                'qualification' => $teacher->qualification,
                'image' =>  $teacher->user->image,
                'mobile_no' => $teacher->user->mobile,
                'subjects' => $subjectData,
                'last_message' => $data ?? null,
                'unread_message' => $unreadCount ?? 0
            ];

            $title = $teacher->user->first_name .' '.$teacher->user->last_name;
            $body = $request->message ??  $count ." Files Received";
            $type = "chat";
            $image = null;
            $user[] = $receiver_id;

            $userinfo = (object)$userinfo;
            send_notification($user, $title, $body, $type, $image, $userinfo);


            $response = array(
                'error' => false,
                'message' => trans('message_sent_successfully'),
                'data' => $data,
                'code' => 200,
            );
        }catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);

    }

    public function getUserChatMessage(Request $request){
        try{

            $offset = $request->offset;
            $limit = $request->limit;

            $messages = ChatMessage::with(['file' => function ($query) {
                $query->select('message_id', 'file_name');
            }])
            ->where(function($query) use ($request) {
                $query->where('modal_id', $request->user_id)
                      ->orWhere('modal_id', Auth::id());
            })
            ->where(function($query) use ($request) {
                $query->where('sender_id', $request->user_id)
                      ->orWhere('sender_id', Auth::id());
            })
            ->select('id', 'sender_id', 'body', 'date')
            ->latest('date');


            $total_items = $messages->count();

            $messages =$messages->offset($offset)->limit($limit)->get()->toArray();


            foreach ($messages as &$message) {
                $message['files'] = collect($message['file'])->map(function ($file) {
                    return  asset('storage/' . $file['file_name']);
                })->toArray();

                unset($message['file']);
            }

            $response = array(
                'error' => false,
                'message' => 'Data Fetched Successfully',
                'data' => [
                    'items' => $messages ?? [],
                    'total_items' => $total_items,
                ],
                'code' => 100,
            );
        }catch(\Exception $e){
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);

    }

    public function readAllMessages(Request $request){
        try {
            $auth = Auth::id();
            $user = $request->user_id;


            $lastMessage = ChatMessage::where('sender_id',$user)->where('modal_id',$auth)->latest()->first();
            if($lastMessage){
                $message_id = $lastMessage->id;
            }

            // Update Read Message id
            $readMessage = ReadMessage::where('modal_id',$auth)->where('user_id',$user)->first();

            if ($readMessage) {
                $readMessage->last_read_message_id = $message_id;
                $readMessage->save();
            }

            $response = array(
                'error' => false,
                'message' => 'Message Read',
                'code' => 200,
            );
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
            return response()->json($response, 200);
        }
    }
}
