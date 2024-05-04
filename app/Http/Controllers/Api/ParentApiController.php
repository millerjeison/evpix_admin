<?php

namespace App\Http\Controllers\Api;

use Throwable;
use Carbon\Carbon;
use App\Models\Exam;
use App\Models\User;
use App\Models\Grade;
use App\Models\Shift;
use Razorpay\Api\Api;
use App\Models\Lesson;
use App\Models\Holiday;
use App\Models\Parents;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\ChatFile;
use App\Models\FeesPaid;
use App\Models\Students;
use Stripe\StripeClient;
use App\Models\ExamClass;
use App\Models\ExamMarks;
use App\Models\FeesClass;
use App\Models\Timetable;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\ExamResult;
use App\Models\OnlineExam;
use App\Models\ChatMessage;
use App\Models\ClassSchool;
use App\Models\LessonTopic;
use App\Models\ReadMessage;
use App\Models\SessionYear;
use App\Models\Announcement;
use App\Models\ClassSection;
use App\Models\ClassSubject;
use App\Models\ClassTeacher;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\ExamTimetable;
use App\Models\FeesChoiceable;
use App\Models\InstallmentFee;
use App\Models\SubjectTeacher;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\UserNotification;
use App\Models\PaidInstallmentFee;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\OnlineExamStudentAnswer;
use App\Models\StudentOnlineExamStatus;
use App\Models\OnlineExamQuestionAnswer;
use App\Models\OnlineExamQuestionChoice;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\TimetableCollection;
use Unicodeveloper\Paystack\Facades\Paystack;

class ParentApiController extends Controller
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
            if (!$auth->hasRole('Parent')) {
                $response = array(
                    'error' => true,
                    'message' => 'Invalid Login Credentials',
                    'code' => 101
                );
                return response()->json($response, 200);
            }
            if ($request->fcm_id) {
                $auth->fcm_id = $request->fcm_id;
                $auth->save();
            }
            if($request->device_type){
                $auth->device_type = $request->device_type;
                $auth->save();
            }

            $token = $auth->createToken($auth->first_name)->plainTextToken;
            $user = $auth->load(['parent']);

            $children = Students::where('father_id', $user->parent->id)->orWhere('mother_id', $user->parent->id)->orWhere('guardian_id', $user->parent->id)->with('class_section')->get();
            $user = flattenMyModel($user);

            foreach ($children as $child) {
                $child->first_name = $child->user->first_name;
                $child->last_name = $child->user->last_name;
                $child->image = $child->user->image;
                $child->dob = $child->user->dob;
                $child->current_address = $child->user->current_address;
                $child->permanent_address = $child->user->permanent_address;

                $dynamicFields = [];
                $dynamicField = $child->dynamic_fields;

                $data = json_decode($dynamicField, true);

                if (is_array($data)) {
                    foreach ($data as $item) {
                        if($item != null){
                            foreach ($item as $key => $value) {
                                $dynamicFields[$key] = $value;
                            }
                        }
                    }
                }else{
                    $dynamicFields = $data;
                }
                $child->dynamic_fields =  $dynamicFields;
                unset($child->user);


                $classSectionName = $child->class_section->class->name . " " . $child->class_section->section->name;


                // Set Stream name
                $streamName = $child->class_section->class->streams->name ?? null;
                if ($streamName !== null) {
                    $child->class_section_name = $classSectionName . " " . $streamName;
                } else {
                    $child->class_section_name = $classSectionName;
                }

                //Set Medium name
                $child->medium_name = $child->class_section->class->medium->name;


                //Set Shift name
                $child->shift_id = $child->class_section->class->shifts->id ?? '';
                $child->shift = Shift::find($child->shift_id);
                if ($child->shift) {
                    $child->shift->id;
                    $child->shift->title;
                    $child->shift->start_time;

                }

                unset($child->class_section);

                //Set Category
                $child->category_name = $child->category->name;
                unset($child->category);
            }
            $data = array_merge($user, ['children' => $children->toArray()]);
            $response = array(
                'error' => false,
                'message' => 'User logged-in!',
                'token' => $token,
                'data' => $data,
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

    // public function getChildren(Request $request) {
    //     try {
    //         $user = $request->user();
    //         $children = $user->parent->children->load(['user:id,first_name,last_name']);
    //         $response = array(
    //             'error' => false,
    //             'message' => 'Children Fetched Successfully.',
    //             'data' => $children,
    //             'code' => 200,
    //         );
    //         return response()->json($response, 200);
    //     } catch (\Exception $e) {
    //         $response = array(
    //             'error' => true,
    //             'message' => trans('error_occurred'),
    //             'code' => 103,
    //         );
    //         return response()->json($response, 200);
    //     }
    // }

    public function subjects(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|numeric',
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
            DB::enableQueryLog();
            $user = $request->user();
            $children = $user->parent->children()->first()->where('id', $request->child_id)->first();
            $subjects = $children->subjects();

            $response = array(
                'error' => false,
                'message' => 'Student Subject Fetched Successfully.',
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

    public function classSubjects(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|numeric',
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
            $children = $user->parent->children()->first()->where('id', $request->child_id)->first();
            $subjects = $children->classSubjects();
            $response = array(
                'error' => false,
                'message' => 'Class Subject Fetched Successfully.',
                'data' => $subjects,
                'code' => 103
            );
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103
            );
            return response()->json($response, 200);
        }
    }

    public function getTimetable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|numeric',
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
            $children = $user->parent->children()->first()->where('id', $request->child_id)->first();
            $child_subject=$children->subjects();

            $core_subjects= array_column($child_subject["core_subject"],'subject_id');

            $elective_subjects = $child_subject["elective_subject"] ?? [];

            if ($elective_subjects) {
                $elective_subjects = $elective_subjects->pluck('subject_id')->toArray();
            }

            $subject_id = array_merge($core_subjects,$elective_subjects);

            $timetables = Timetable::where('class_section_id', $children->class_section_id)->with(['subject_teacher' => function ($q) use ($subject_id) {
                $q->whereIn('subject_id', $subject_id);
            }])->orderBy('day', 'asc')->orderBy('start_time', 'asc')->get();

            $new_timetable=[];
            foreach($timetables as $timetable)
            {
                if($timetable->subject_teacher != null)
                {
                    $new_timetable[]=$timetable;
                }
            }

            $response = array(
                'error' => false,
                'message' => "Timetable Fetched Successfully",
                'data' => new TimetableCollection($new_timetable),
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

    /**
     * @param
     * subject_id : 2
     * lesson_id : 1 //OPTIONAL
     */
    public function getLessons(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|numeric',
            'lesson_id' => 'nullable|numeric',
            'subject_id' => 'required',
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
            $children = $user->parent->children()->first()->where('id', $request->child_id)->first();
            $data = Lesson::where('class_section_id', $children->class_section_id)->where('subject_id', $request->subject_id)->with('topic', 'file');
            if ($request->lesson_id) {
                $data->where('id', $request->lesson_id);
            }
            $data = $data->get();

            $response = array(
                'error' => false,
                'message' => "Lessons Fetched Successfully",
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

    /**
     * @param
     * lesson_id : 1
     * topic_id : 1    //OPTIONAL
     */
    public function getLessonTopics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|numeric',
            'lesson_id' => 'required|numeric',
            'topic_id' => 'nullable|numeric',
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
            //Not Used Anywhere
            //            $user = $request->user();
            //            $children = $user->parent->children()->where('id',$request->child_id)->first();
            //            $subjects = $children->subjects();
            //
            //            $student = $request->user()->student;

            $data = LessonTopic::where('lesson_id', $request->lesson_id)->with('file');
            if ($request->topic_id) {
                $data->where('id', $request->topic_id);
            }
            $data = $data->get();

            $response = array(
                'error' => false,
                'message' => "Topics Fetched Successfully",
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

    /**
     * @param
     * assignment_id : 1    //OPTIONAL
     * subject_id : 1       //OPTIONAL
     */
    public function getAssignments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|numeric',
            'assignment_id' => 'nullable|numeric',
            'subject_id' => 'nullable|numeric',
            'is_submitted' => 'nullable|numeric',
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
            $children = $user->parent->children()->first()->where('id', $request->child_id)->first();
            $child_subject=$children->subjects();
            $child_id= $request->child_id;
            $core_subjects= array_column($child_subject["core_subject"],'subject_id');

            $elective_subjects = $child_subject["elective_subject"] ?? [];

            if ($elective_subjects) {
                $elective_subjects = $elective_subjects->pluck('subject_id')->toArray();
            }

            $subject_id = array_merge($core_subjects,$elective_subjects);

            $data = Assignment::where('class_section_id', $children->class_section_id)->whereIn('subject_id',$subject_id)->with('file', 'subject', 'submission.file');

            if ($request->assignment_id) {
                $data->where('id', $request->assignment_id);
            }
            if ($request->subject_id) {
                $data->where('subject_id', $request->subject_id);
            }
            if ($request->is_submitted) {
                if ($request->is_submitted == 1) {
                    $data->whereHas('submission',function($q)use($child_id){
                        $q->where('student_id',$child_id);
                    })->get();
                }else if ($request->is_submitted == 0) {
                    $data->has('submission', '<', 1)->get();
                }
            }

            $data = $data->orderBy('id', 'desc')->paginate();
            $response = array(
                'error' => false,
                'message' => "Assignments Fetched Successfully",
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

    /**
     * @param
     * month : 4 //OPTIONAL
     * year : 2022 //OPTIONAL
     */
    public function getAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|numeric',
            'month' => 'nullable|numeric',
            'year' => 'nullable|numeric',
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
            $children = $user->parent->children()->first()->where('id', $request->child_id)->first();
            $session_year = getSettings('session_year');
            $session_year_id = $session_year['session_year'];

            $attendance = Attendance::where('student_id', $children->id)->where('session_year_id', $session_year_id);
            $holidays = new Holiday;
            $session_year_data = SessionYear::find($session_year_id);
            if (isset($request->month)) {
                $attendance = $attendance->whereMonth('date', $request->month);
                $holidays = $holidays->whereMonth('date', $request->month);
            }

            if (isset($request->year)) {
                $attendance = $attendance->whereYear('date', $request->year);
                $holidays = $holidays->whereYear('date', $request->year);
            }
            $attendance = $attendance->get();
            $holidays = $holidays->get();

            $response = array(
                'error' => false,
                'message' => "Attendance Details Fetched Successfully",
                'data' => ['attendance' => $attendance, 'holidays' => $holidays, 'session_year' => $session_year_data],
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

    public function getAnnouncements(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|in:subject,noticeboard,class',
            'child_id' => 'required_if:type,subject,class|numeric',
            'subject_id' => 'required_if:type,subject|numeric'
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
            $children = null;
            if ($request->type !== "noticeboard") {
                $user = $request->user();
                $children = $user->parent->children()->first()->where('id', $request->child_id)->first();
                if (empty($children)) {
                    $response = array(
                        'error' => true,
                        'message' => "Invalid Child  ID",
                        'code' => 106,
                    );
                    return response()->json($response);
                }
                $class_id = $children->class_section->class->id;
            }


            $session_year = getSettings('session_year');
            $session_year_id = $session_year['session_year'];
            if (isset($request->type) && $request->type == "subject") {
                $table = SubjectTeacher::where('class_section_id', $children->class_section_id)->where('subject_id', $request->subject_id)->get()->pluck('id');
                if (empty($table)) {
                    $response = array(
                        'error' => true,
                        'message' => "Invalid Subject ID",
                        'code' => 106,
                    );
                    return response()->json($response);
                }
            }
            $data = Announcement::with('file')->where('session_year_id', $session_year_id);

            if (isset($request->type) && $request->type == "noticeboard") {
                $data = $data->where('table_type', "");
            }

            if (isset($request->type) && $request->type == "class") {
                $data = $data->where('table_type', "App\Models\ClassSchool")->where('table_id', $class_id);
            }

            if (isset($request->type) && $request->type == "subject") {
                $data = $data->where('table_type', "App\Models\SubjectTeacher")->whereIn('table_id', $table);
            }

            $data = $data->orderBy('id', 'desc')->paginate();
            $response = array(
                'error' => false,
                'message' => "Announcement Details Fetched Successfully",
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

    public function getTeachers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|numeric',
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
            $children = Students::where('id', $request->child_id)->first();
            $student_subject = $children->subjects();
            $core_subjects= array_column($student_subject["core_subject"],'subject_id');

            $elective_subjects = $student_subject["elective_subject"] ?? [];
            if ($elective_subjects) {
                $elective_subjects = $elective_subjects->pluck('subject_id')->toArray();
            }
            $subject_id = array_merge($core_subjects,$elective_subjects);
            $class_section_id = $children->class_section->id;
            $class_teachers_id = ClassTeacher::where('class_section_id', $class_section_id)->pluck('class_teacher_id')->toArray();
            $subject_teacher_id = SubjectTeacher::where('class_section_id',$class_section_id)->whereIn('subject_id',$subject_id)->pluck('teacher_id')->toArray();
            $teacher_ids = array_merge($class_teachers_id, $subject_teacher_id);
            $teachers = Teacher::whereIn('id', $teacher_ids)->with([
                'user:id,first_name,last_name,image,mobile,email,dob',
                'subjects' => function ($query) use ($class_section_id) {
                    // Add a condition to filter subjects by class_section_id
                    $query->where('class_section_id', $class_section_id)->with('subject');
                },
            ])->get();
            foreach ($teachers as $teacher) {
                $subjectData = [];
                foreach ($teacher->subjects as $subject) {
                    $subjectData[] = $subject->subject->name;
                }
                $subjects = implode(', ', $subjectData);

                $data[] = [
                    'id' => $teacher->id,
                    'user_id' => $teacher->user->id,
                    'first_name' => $teacher->user->first_name,
                    'last_name' => $teacher->user->last_name,
                    'qualification' => $teacher->qualification,
                    'email' => $teacher->user->email,
                    'image' =>  $teacher->user->image,
                    'dob' => $teacher->user->dob,
                    'mobile_no' => $teacher->user->mobile,
                    'subjects' => $subjects,
                ];

            }

            $response = array(
                'error' => false,
                'message' => "Teacher Details Fetched Successfully",
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

    public function getExamList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|nullable',
            'status' => 'nullable:digits:0,1,2,3'
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
            $student = Students::with('class_section')->where('id', $request->child_id)->first();
            $class_id = $student->class_section->class_id;

            $child_subject=$student->subjects();

            $core_subjects= array_column($child_subject["core_subject"],'subject_id');

            $elective_subjects = $child_subject["elective_subject"] ?? [];

            if ($elective_subjects) {
                $elective_subjects = $elective_subjects->pluck('subject_id')->toArray();
            }

            $subject_id = array_merge($core_subjects,$elective_subjects);

            $exam_data_db = ExamClass::with('exam.session_year:id,name', 'exam.timetable')
                ->where('class_id', $class_id)
                ->whereHas('exam.timetable', function ($query) use ($subject_id) {
                    $query->whereIn('subject_id', $subject_id);
                })->get();

            foreach ($exam_data_db as $data) {

                // date status
                $starting_date_db = ExamTimetable::select(DB::raw("min(date)"))->where(['exam_id' => $data->exam->id, 'class_id' => $class_id])->first();
                $starting_date = $starting_date_db['min(date)'];
                $ending_date_db = ExamTimetable::select(DB::raw("max(date)"))->where(['exam_id' => $data->exam->id, 'class_id' => $class_id])->first();
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
                            );
                        }
                    }
                } else {
                    $exam_data[] = array(
                        'id' => $data->exam->id,
                        'name' => $data->exam->name,
                        'description' => $data->exam->description,
                        'publish' => $data->exam->publish,
                        'session_year' => $data->exam->session_year->name,
                        'exam_starting_date' => $starting_date,
                        'exam_ending_date' => $ending_date,
                        'exam_status' => $exam_status,
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
            'child_id' => 'required|nullable',
            'exam_id' => 'required|nullable',
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
            $student = Students::with('class_section')->where('id', $request->child_id)->first();
            $student_subject=$student->subjects();

            $core_subjects= array_column($student_subject["core_subject"],'subject_id');
            $elective_subjects = $student_subject["elective_subject"] == null ? [] : $student_subject["elective_subject"]->pluck('subject_id')->toArray();

            $subject_id = array_merge($core_subjects,$elective_subjects);

            $class_id = $student->class_section->class_id;
            $exam_data = Exam::with(['timetable' => function ($q) use ($request, $class_id, $subject_id) {
                $q->where(['exam_id' => $request->exam_id, 'class_id' => $class_id])->whereIn('subject_id', $subject_id)->with(['subject'])->orderby('date');
            }])->where('id', $request->exam_id)->first();
            $data = isset($exam_data) ? $exam_data->timetable : [];

            $response = array(
                'error' => false,
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

    public function getExamMarks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|nullable',
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
            $exam_result_db = ExamResult::with(['student' => function ($q) {
                $q->select('id', 'user_id', 'roll_number')->with('user:id,first_name,last_name');
            }])->with('exam', 'session_year:id,name')->where('student_id', $request->child_id)->get();

            if (sizeof($exam_result_db)) {
                foreach ($exam_result_db as $result) {
                    $exam_timetable_id = ExamTimetable::where('exam_id', $result->exam_id)->pluck('id');

                    $exam_marks_db = ExamMarks::whereIn('exam_timetable_id', $exam_timetable_id)->where('student_id', $result->student_id)->get();

                    $class_data = ClassSection::where('id', $result->class_section_id)->with('class.medium', 'section')->first();

                    $starting_date_db = ExamTimetable::select(DB::raw("min(date)"))->where(['exam_id' => $result->exam_id, 'class_id' => $class_data->class_id])->first();
                    $starting_date = $starting_date_db['min(date)'];

                    $exam_result = array();
                    $exam_result = array(
                        'result_id' => $result->id,
                        'exam_id' => $result->exam_id,
                        'exam_name' => $result->exam->name,
                        'class_name' => $class_data->class->name . '-' . $class_data->section->name . ' ' . $class_data->class->medium->name,
                        'student_name' => $result->student->user->first_name . ' ' . $result->student->user->last_name,
                        'exam_date' => $starting_date,
                        'total_marks' => $result->total_marks,
                        'obtained_marks' => $result->obtained_marks,
                        'percentage' => $result->percentage,
                        'grade' => $result->grade,
                        'session_year' => $result->session_year->name,
                    );

                    $exam_marks = array();
                    foreach ($exam_marks_db as $marks) {
                        $exam_marks[] = array(
                            'marks_id' => $marks->id,
                            'subject_name' => $marks->subject->name,
                            'subject_type' => $marks->subject->type,
                            'total_marks' => $marks->timetable->total_marks,
                            'passing_marks' => $marks->timetable->passing_marks,
                            'obtained_marks' => $marks->obtained_marks,
                            'teacher_review' => $marks->teacher_review,
                            'grade' => $marks->grade,
                        );
                    }
                    $data[] = array(
                        'result' => $exam_result,
                        'exam_marks' => $exam_marks,
                    );
                }

                $response = array(
                    'error' => false,
                    'message' => "Exam Result Fetched Successfully",
                    'data' => $data,
                    'code' => 200,
                );
            } else {
                $response = array(
                    'error' => false,
                    'message' => "Exam Result Fetched Successfully",
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

    //Get Fees Details
    public function getFeesDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required',
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
            $current_date = Carbon::now('UTC');
            $student_data = Students::where('id', $request->child_id)->with('class_section')->first();
            $class_id = $student_data->class_section->class_id;

            $session_year = getSettings('session_year');
            $session_year_id = $session_year['session_year'];

            //Total optional fees amount
            $optional_fees_amount = FeesClass::select(DB::raw("SUM(amount) as optional_fees_amount"))->where(['class_id' => $class_id, 'choiceable' => 1])->first();
            $optional_fees_amount = $optional_fees_amount['optional_fees_amount'];

            //Total optional fees amount
            $compulsory_fees_amount = FeesClass::select(DB::raw("SUM(amount) as compulsory_fees_amount"))->where(['class_id' => $class_id, 'choiceable' => 0])->first();
            $compulsory_fees_amount = $compulsory_fees_amount['compulsory_fees_amount'];

            // Fees Class Data
            $fees_class = FeesClass::where('class_id', $class_id)->with('fees_type')->get();

            //arrays for data
            $compulsory_fees_data = array();
            $optional_fees_data = array();
            $installment_data = array();

            $payment_transaction_db = PaymentTransaction::where(['student_id' => $request->child_id,'class_id' => $class_id, 'session_year_id' => $session_year_id])->latest()->first();

            if($payment_transaction_db){
                if($payment_transaction_db->date)
                {
                    $datetime = Carbon::parse($payment_transaction_db->date);
                    $time = $datetime->format('H:i:s');
                    $addHour = $datetime->copy()->addHour();
                    if (Carbon::now()->gt($addHour)) {
                        $payment_transaction_db->payment_status = 0;
                        $payment_transaction_db->save();
                    }
                }
                $payment_transaction_status = $payment_transaction_db->payment_status;
            }
            // Get the Compulsory Fees Data and Optional Fees Data
            foreach ($fees_class as $data) {
                if ($data->choiceable == 1) {
                    $paid_optional_data = FeesChoiceable::where(['student_id' => $request->child_id, 'class_id' => $class_id, 'session_year_id' => $session_year_id, 'fees_type_id' => $data['fees_type_id'] , 'status' => 1])->first();
                    $optional_fees_data[] = array(
                        'id' => $data->fees_type_id,
                        'name' => $data->fees_type->name,
                        'amount' =>  $data->amount,
                        'is_paid' => $paid_optional_data->status ?? 0,
                        'paid_date' => $paid_optional_data->date ?? null
                    );

                } else {
                    $is_fully_paid_data = FeesPaid::where(['student_id' => $request->child_id, 'class_id' => $class_id, 'session_year_id' => $session_year_id, 'is_fully_paid' => 1]);
                    $compulsory_fees_data[] = array(
                        'id' => $data->fees_type_id,
                        'name' => $data->fees_type->name,
                        'amount' =>  $data->amount,
                        'is_paid' => !empty($is_fully_paid_data->count()) ? 1 : 0,
                        'paid_on' => !empty($is_fully_paid_data->count()) ? ((isset($is_fully_paid_data->first()->date) && !empty($is_fully_paid_data->first()->date)) ? date('Y-m-d', strtotime($is_fully_paid_data->first()->date)) : null) : "",
                    );
                }
            }

            // Checking for Due Charges Paid with Fully Compulsory Amount and Add it to Compulsory Fees Data Array
            if (isset($compulsory_fees_data) && !empty($compulsory_fees_data)) {
                $paid_charges_due = FeesChoiceable::where(['student_id' => $request->child_id, 'class_id' => $class_id, 'session_year_id' => $session_year_id, 'is_due_charges' => 1]);
                if ($paid_charges_due->count()) {
                    array_push($compulsory_fees_data, array(
                        'id' => "",
                        'name' => 'Due Charges',
                        'amount' => $paid_charges_due->first()->total_amount,
                        'is_paid' => 1
                    ));
                }
            }

            // DB::enableQueryLog();
            //check the installments data for current session year
            $installment_db = InstallmentFee::where('session_year_id', $session_year_id);
            if ($installment_db->count()) {
                $installment_db_data = $installment_db->get();
                foreach ($installment_db_data as $data) {
                    $paid_installment_data = PaidInstallmentFee::where(['student_id' => $request->child_id, 'class_id' => $class_id, 'session_year_id' => $session_year_id, 'installment_fee_id' => $data['id'], 'status'=> 1])->first();
                    $installment_data[] = array(
                        'id' => $data->id,
                        'name' => $data->name,
                        'due_date' => date('Y-m-d', strtotime($data->due_date)),
                        'due_charges' => $data->due_charges,
                        'is_paid' => $paid_installment_data->status ?? 0,
                        'paid_date' => $paid_installment_data->date ?? null,
                        "paid_due_charges" => !empty($paid_installment_data) ? number_format($paid_installment_data->due_charges, 2) : ""
                    );
                }
            }
            //Due Date And Due Charges From Session Year For Fully pay Compulsory Amount (Without Installments)
            $session_year_data = SessionYear::where('id', $session_year_id)->first();
            $due_date = date('Y-m-d', strtotime($session_year_data->fee_due_date));
            $due_charges = $session_year_data->fee_due_charges;

            $response = array(
                'error' => false,
                'compulsory_fees_data' => $compulsory_fees_data ?? array(""),
                'optional_fees_data' => $optional_fees_data ?? array(""),
                'installment_data' => $installment_data ?? (object)null,
                'compulsory_fees_total' => $compulsory_fees_amount ?? 0,
                'optional_fees_total' => $optional_fees_amount ?? 0,
                'compulsory_due_date' => $due_date,
                'compulsory_due_charges' => $due_charges,
                'current_date' => $current_date,
                'is_fee_pending' => (isset($payment_transaction_status) && $payment_transaction_status == 2) ? 1 : 0,
                'code' => 200,
            );
            // dd($response);
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

    //Store Fees Transaction
    public function storeFeesTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required',
            'amount' => 'required',
            'type_of_fee' => 'required|in:0,1,2',
            'is_fully_paid' => 'required|in:0,1'
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

            $parent_id = Auth::user()->parent->id;
            $email = Auth::user()->email;
            $session_year = getSettings('session_year');
            $session_year_id = $session_year['session_year'];

            //variables for storing the data
            $payment_gateway_details = array();

            $class_id = Students::where('id', $request->child_id)->with('class_section')->first()->class_section->class_id;


            //variables for storing the data
            $optional_fees_store = array();
            $installment_fees_store = array();
            $paid_installment_id = [];
            $optional_fees_id = [];


            $razorpay_status = getSettings('razorpay_status');
            $razorpay_status = $razorpay_status['razorpay_status'];

            // get currency code from settings
            $setting_currency_code = getSettings('currency_code');
            $currency_code = $setting_currency_code['currency_code'];

            if ($razorpay_status) {
                // get api key from settings
                $razorpay_setting_api_key = getSettings('razorpay_api_key');
                $razorpay_api_key = $razorpay_setting_api_key['razorpay_api_key'];

                // get secret key from settings
                $razorpay_setting_secret_key = getSettings('razorpay_secret_key');
                $razorpay_secret_key = $razorpay_setting_secret_key['razorpay_secret_key'];

                $currency_code = strtoupper($currency_code);

                // add the data to transaction table local
                $payment_transaction_db = new PaymentTransaction();
                $payment_transaction_db->student_id = $request->child_id;
                $payment_transaction_db->class_id = $class_id;
                $payment_transaction_db->parent_id = $parent_id;
                $payment_transaction_db->mode = 2;
                $payment_transaction_db->type_of_fee = $request->type_of_fee;
                $payment_transaction_db->payment_gateway = 1;
                $payment_transaction_db->payment_status = 2;
                $payment_transaction_db->total_amount = $request->amount;
                $payment_transaction_db->date = date('Y-m-d H:i:s');
                $payment_transaction_db->session_year_id = $session_year_id;
                $payment_transaction_db->save();

                // If Optional Fees Passed then insert data
                if (isset($request->optional_fees_data) && !empty($request->optional_fees_data)) {
                    foreach ($request->optional_fees_data as $data) {
                        $optional_fees_store = array(
                            'student_id' => $request->child_id,
                            'class_id' => $class_id,
                            'fees_type_id' => $data['id'],
                            'is_due_charges' => 0,
                            'total_amount' => $data['amount'],
                            'session_year_id' => $session_year_id,
                            'date' => date('Y-m-d'),
                            'payment_transaction_id' => $payment_transaction_db->id,
                            'status' => 0
                        );
                        $optional_fees_id[] =  FeesChoiceable::insertGetId($optional_fees_store);
                    }
                }

                // If Installment Fees Passed then insert data
                if (isset($request->installment_data) && !empty($request->installment_data)) {
                    foreach ($request->installment_data as $data) {
                        $installment_fees_store = array(
                            'class_id' => $class_id,
                            'student_id' => $request->child_id,
                            'parent_id' => $parent_id,
                            'installment_fee_id' => $data['id'],
                            'session_year_id' => $session_year_id,
                            'amount' => $data['amount'],
                            'due_charges' => $data['due_charges'] ?? null,
                            'date' => date('Y-m-d'),
                            'payment_transaction_id' => $payment_transaction_db->id,
                            'status' => 0
                        );
                        $paid_installment_id[] = PaidInstallmentFee::insertGetId($installment_fees_store);
                    }
                }

                $api = new Api($razorpay_api_key, $razorpay_secret_key);
                $order = $api->order->create(array('amount' => $request->amount * 100, 'currency' => $currency_code, 'notes' => array(
                    'student_id' => $request->child_id,
                    'parent_id' => $parent_id,
                    'class_id' => $class_id,
                    'session_year_id' => $session_year_id,
                    'payment_transaction_id' => $payment_transaction_db->id,
                    'is_fully_paid' => $request->is_fully_paid,
                    'type_of_fee' => $request->type_of_fee,
                    'is_due_charges' => (isset($request->due_charges) && $request->due_charges >=0) ? 1 : 0,
                    'due_charges' => $request->due_charges,
                    'optional_fees_paid' => json_encode($optional_fees_id) ?? "",
                    'installment_fees_paid' => json_encode($paid_installment_id) ?? "",
                )));

                // update the order id in trasaction table local
                $payemnt_transaction_update = PaymentTransaction::find($payment_transaction_db->id);
                $payemnt_transaction_update->order_id = $order->id;
                $payemnt_transaction_update->save();

                $payment_gateway_details = array(
                    'order_id' => $order->id,
                    'amount' => $order->amount,
                    'payment_transaction_id' => $payment_transaction_db->id,
                );
            }

            $stripe_status = getSettings('stripe_status');
            $stripe_status = $stripe_status['stripe_status'];

            if ($stripe_status) {
                $stripe_setting_secret_key = getSettings('stripe_secret_key');
                $stripe_secret_key = $stripe_setting_secret_key['stripe_secret_key'];

                $currency_code = strtolower($currency_code);

                // add the data to transaction table local
                $payment_transaction_db = new PaymentTransaction();
                $payment_transaction_db->student_id = $request->child_id;
                $payment_transaction_db->class_id = $class_id;
                $payment_transaction_db->parent_id = $parent_id;
                $payment_transaction_db->mode = 2;
                $payment_transaction_db->type_of_fee = $request->type_of_fee;
                $payment_transaction_db->payment_gateway = 2;
                $payment_transaction_db->payment_status = 2;
                $payment_transaction_db->total_amount = $request->amount;
                $payment_transaction_db->date = date('Y-m-d H:i:s');
                $payment_transaction_db->session_year_id = $session_year_id;
                $payment_transaction_db->save();

                 // If Optional Fees Passed then insert data
                if (isset($request->optional_fees_data) && !empty($request->optional_fees_data)) {
                    foreach ($request->optional_fees_data as $data) {
                        $optional_fees_store = array(
                            'student_id' => $request->child_id,
                            'class_id' => $class_id,
                            'fees_type_id' => $data['id'],
                            'is_due_charges' => 0,
                            'total_amount' => $data['amount'],
                            'session_year_id' => $session_year_id,
                            'date' => date('Y-m-d'),
                            'payment_transaction_id' => $payment_transaction_db->id,
                            'status' => 0
                        );
                        $optional_fees_id[] =  FeesChoiceable::insertGetId($optional_fees_store);
                    }
                }

                // If Installment Fees Passed then insert data
                if (isset($request->installment_data) && !empty($request->installment_data)) {
                    foreach ($request->installment_data as $data) {
                        $installment_fees_store = array(
                            'class_id' => $class_id,
                            'student_id' => $request->child_id,
                            'parent_id' => $parent_id,
                            'installment_fee_id' => $data['id'],
                            'session_year_id' => $session_year_id,
                            'amount' => $data['amount'],
                            'due_charges' => $data['due_charges'] ?? null,
                            'date' => date('Y-m-d'),
                            'payment_transaction_id' => $payment_transaction_db->id,
                            'status' => 0
                        );
                        $paid_installment_id[] = PaidInstallmentFee::insertGetId($installment_fees_store);
                    }
                }


                $stripe = new StripeClient($stripe_secret_key);
                $stripe_data = $stripe->paymentIntents->create(
                    [
                        'amount' => $request->amount * 100,
                        'currency' => $currency_code,
                        'metadata' => [
                            'student_id' => $request->child_id,
                            'parent_id' => $parent_id,
                            'class_id' => $class_id,
                            'session_year_id' => $session_year_id,
                            'payment_transaction_id' => $payment_transaction_db->id,
                            'is_fully_paid' => $request->is_fully_paid,
                            'type_of_fee' => $request->type_of_fee,
                            'is_due_charges' => (isset($request->due_charges) && $request->due_charges >=0) ? 1 : 0,
                            'due_charges' => $request->due_charges,
                            'optional_fees_paid' => json_encode($optional_fees_id) ?? "",
                            'installment_fees_paid' => json_encode($paid_installment_id) ?? "",
                        ],
                    ]
                );

                // update the order id in trasaction table local
                $payemnt_transaction_update = PaymentTransaction::find($payment_transaction_db->id);
                $payemnt_transaction_update->order_id = $stripe_data->id;
                $payemnt_transaction_update->save();

                $payment_gateway_details = array(
                    'payment_intent_id' => $stripe_data->id,
                    'amount' => $stripe_data->amount,
                    'client_secret' => $stripe_data->client_secret,
                    'payment_transaction_id' => $payment_transaction_db->id,
                );
            }

            $paystack_status = getSettings('paystack_status');
            $paystack_status = $paystack_status['paystack_status'];

            if($paystack_status)
            {
                $paystack_setting_secret_key = getSettings('paystack_secret_key');
                $paystack_secret_key = $paystack_setting_secret_key['paystack_secret_key'];

                $currency_code = strtoupper($currency_code);

                // add the data to transaction table local
                $payment_transaction_db = new PaymentTransaction();
                $payment_transaction_db->student_id = $request->child_id;
                $payment_transaction_db->class_id = $class_id;
                $payment_transaction_db->parent_id = $parent_id;
                $payment_transaction_db->mode = 2;
                $payment_transaction_db->type_of_fee = $request->type_of_fee;
                $payment_transaction_db->payment_gateway = 3;
                $payment_transaction_db->payment_status = 2;
                $payment_transaction_db->total_amount = $request->amount;
                $payment_transaction_db->date = date('Y-m-d H:i:s');
                $payment_transaction_db->session_year_id = $session_year_id;
                $payment_transaction_db->save();

                 // If Optional Fees Passed then insert data
                if (isset($request->optional_fees_data) && !empty($request->optional_fees_data)) {
                    foreach ($request->optional_fees_data as $data) {
                        $optional_fees_store = array(
                            'student_id' => $request->child_id,
                            'class_id' => $class_id,
                            'fees_type_id' => $data['id'],
                            'is_due_charges' => 0,
                            'total_amount' => $data['amount'],
                            'session_year_id' => $session_year_id,
                            'date' => date('Y-m-d'),
                            'payment_transaction_id' => $payment_transaction_db->id,
                            'status' => 0
                        );
                        $optional_fees_id[] =  FeesChoiceable::insertGetId($optional_fees_store);
                    }
                }

                // If Installment Fees Passed then insert data
                if (isset($request->installment_data) && !empty($request->installment_data)) {
                    foreach ($request->installment_data as $data) {
                        $installment_fees_store = array(
                            'class_id' => $class_id,
                            'student_id' => $request->child_id,
                            'parent_id' => $parent_id,
                            'installment_fee_id' => $data['id'],
                            'session_year_id' => $session_year_id,
                            'amount' => $data['amount'],
                            'due_charges' => $data['due_charges'] ?? null,
                            'date' => date('Y-m-d'),
                            'payment_transaction_id' => $payment_transaction_db->id,
                            'status' => 0
                        );
                        $paid_installment_id[] = PaidInstallmentFee::insertGetId($installment_fees_store);
                    }
                }

                $payment_gateway_details = array(
                    'payment_transaction_id' => $payment_transaction_db->id,
                    'is_fully_paid' => $request->is_fully_paid,
                    'amount' => $request->amount * 100,
                    'currency' => $currency_code,
                    'email' => $email,
                    'optional_fees_id' => $optional_fees_id ?? [],
                    'paid_installment_id' =>  $paid_installment_id ?? []
                );
            }
            //validating the enable of gateways ..
            if ($razorpay_status == 0 && $stripe_status == 0 && $paystack_status == 0) {
                $response = array(
                    'error' => true,
                    'message' => 'Please enable the payment gateway in panel',
                    'code' => 404,
                );
                return response()->json($response);
            }
            $response = array(
                'error' => false,
                'message' => trans('data_store_successfully'),
                'payment_gateway_details' => $payment_gateway_details,
                'code' => 200,
            );
        } catch (\Exception $e) {
            Log::error($e);
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
    }

    // add the transaction data in transaction table
    public function storeFees(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required',
            'payment_id' => 'nullable',
            'payment_signature' => 'nullable',
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
            // Updating the Payment Transaction
            $transaction_db = PaymentTransaction::findOrFail($request->transaction_id);
            if ($request->payment_id) {
                $transaction_db->payment_id = $request->payment_id;
            }
            if ($request->payment_signature) {
                $transaction_db->payment_signature = $request->payment_signature;
            }
            $transaction_db->save();
            $response = array(
                'error' => false,
                'message' => trans('data_update_successfully'),
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

    //get the fees paid list
    public function feesPaidList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required',
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
            $fees_paid = FeesPaid::where(['student_id' => $request->child_id])->with('session_year:id,name', 'class.medium')->get();

            $response = array(
                'error' => false,
                'data' => $fees_paid,
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

    //Generate The Reciept
    public function feesPaidReceiptPDF(Request $request)
    {
        try {
            $parent = Auth::user();
            $parent_name = $parent->first_name . ' ' . $parent->last_name;
            $logo = env('LOGO2');
            $logo = public_path('/storage/' . $logo);
            $school_name = env('APP_NAME');
            $school_address = getSettings('school_address');
            $school_address = $school_address['school_address'];

            $currency_symbol = getSettings('currency_symbol');
            if (isset($currency_symbol) && sizeof($currency_symbol)) {
                $currency_symbol = $currency_symbol['currency_symbol'];
            } else {
                $currency_symbol = null;
            }

            //Getting the Fees Paid Data
            $fees_paid = FeesPaid::where('id', $request->fees_paid_id)->with('student.user:id,first_name,last_name', 'class', 'session_year')->get()->first();

            // Check That Fees Paid Data Exists Or Not
            if (!$fees_paid) {
                $response = array(
                    'error' => true,
                    'message' => "No Fees Paid Found",
                );
                return response()->json($response);
            }

            // Variables
            $student_id = $fees_paid->student_id;
            $class_id = $fees_paid->class_id;
            $session_year_id = $fees_paid->session_year_id;


            // Paid Installment Data
            $paid_installment = PaidInstallmentFee::where(['student_id' => $student_id, 'class_id' => $class_id, 'session_year_id' => $session_year_id , 'status' => 1])->with('installment_fee')->get();

            //Fees Choiceable Data
            $optional_fees_type_id = FeesClass::where(['class_id' => $class_id ,'choiceable' => 1])->pluck('fees_type_id');
            $fees_choiceable = FeesChoiceable::whereIn('fees_type_id',$optional_fees_type_id)->where(['student_id' => $student_id, 'class_id' => $class_id, 'session_year_id' => $session_year_id , 'status' => 1])->with('fees_type')->orderby('id', 'asc')->get();

            //Fees Class Data
            $fees_class = FeesClass::where(['class_id' => $class_id, 'choiceable' => 0])->with('fees_type')->get();

            //Session Year Data
            $session_year = SessionYear::where('id', $session_year_id)->first();

            //Load the HTML
            $pdf = Pdf::loadView('fees.fees_receipt', compact('logo', 'school_name', 'fees_paid', 'paid_installment', 'fees_choiceable', 'currency_symbol', 'school_address', 'fees_class', 'session_year'));

            //Get The Output Of PDF
            $output = $pdf->output();

            $response = array(
                'error' => false,
                'pdf' => base64_encode($output),
            );
        } catch (Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
            );
        }
        return response()->json($response);
    }

    public function getOnlineExamList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|numeric',
            'subject_id' => 'nullable|numeric'
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
            $student = Students::where('id', $request->child_id)->first();

            $student_subject=$student->subjects();
            $class_subject = $student->classSubjects();

            $core_subjects= array_column($student_subject["core_subject"],'subject_id');

            $elective_subjects = $student_subject["elective_subject"] ?? [];
            if ($elective_subjects) {
                $elective_subjects = $elective_subjects->pluck('subject_id')->toArray();
            }

            $subject_id = array_merge($core_subjects,$elective_subjects);

            $class_section_id = $student->class_section->id;
            $class_id = $student->class_section->class_id;
            $session_year = getSettings('session_year');
            $session_year_id = $session_year['session_year'];

            //get current
            $time_data = Carbon::now()->toArray();
            $current_date_time = $time_data['formatted'];

            // checks the subject id param is passed or not .
            // query meets the condition for both class section and class
            if (isset($request->subject_id) && !empty($request->subject_id)) {
                $exam_data_db = OnlineExam::where(['model_type' => 'App\Models\ClassSection', 'model_id' => $class_section_id, 'subject_id' => $request->subject_id, 'session_year_id' => $session_year_id,  ['end_date', '>=', $current_date_time]])->has('question_choice')->with('subject')->whereDoesntHave('student_attempt', function ($q) use ($student) {
                    $q->where('student_id', $student->id);
                })->orWhere(function ($query) use ($class_id, $session_year_id, $current_date_time, $student, $request) {
                    $query->where(['model_type' => 'App\Models\ClassSchool', 'model_id' => $class_id, 'subject_id' => $request->subject_id, 'session_year_id' => $session_year_id,  ['end_date', '>=', $current_date_time]])->with('subject')->whereDoesntHave('student_attempt', function ($q) use ($student) {
                        $q->where('student_id', $student->id);
                    });
                })->orderby('start_date')->paginate(15)->toArray();
            } else{
                $exam_data_db = OnlineExam::where(['model_type' => 'App\Models\ClassSection' , 'model_id' => $class_section_id , 'session_year_id' => $session_year_id ,  ['end_date', '>=', $current_date_time]])->whereIn('subject_id', $subject_id )->has('question_choice')->with('subject')->whereDoesntHave('student_attempt' , function($q) use($student){
                    $q->where('student_id',$student->id);
                })->orWhere(function($query) use ($class_id,$subject_id,$session_year_id,$current_date_time,$student) {
                    $query->where(['model_type' => 'App\Models\ClassSchool' , 'model_id' => $class_id, 'session_year_id' => $session_year_id ,  ['end_date', '>=', $current_date_time]])->whereIn('subject_id', $subject_id )->with('subject')->whereDoesntHave('student_attempt' , function($q) use($student){
                        $q->where('student_id',$student->id);
                    });
                })->orderby('start_date')->paginate(15)->toArray();
            }
            if (isset($exam_data_db) && !empty($exam_data_db)) {

                $exam_data = array();
                $exam_list = array();
                // making the array of exam data
                foreach ($exam_data_db['data'] as $data) {

                    // total marks of exams
                    $total_marks = OnlineExamQuestionChoice::select(DB::raw("sum(marks)"))->where('online_exam_id', $data['id'])->first();
                    $total_marks = $total_marks['sum(marks)'];

                    if ($data['model_type'] == 'App\Models\ClassSection') {
                        $class_section_data = ClassSection::where('id', $data['model_id'])->with('class.medium', 'section')->first();
                        $class_name = $class_section_data->class->name . ' - ' . $class_section_data->section->name . ' ' . $class_section_data->class->medium->name;
                    } else {
                        $class_data = ClassSchool::where('id', $data['model_id'])->with('medium')->first();
                        $class_name = $class_data->name . ' ' . $class_data->medium->name;
                    }

                    $exam_list[] = array(
                        'exam_id' => $data['id'],
                        'class' => array(
                            'id' => $data['model_id'],
                            'name' => $class_name
                        ),
                        'subject' => array(
                            'id' => $data['subject_id'],
                            'name' => $data['subject']['name'] . ' - ' . $data['subject']['type']
                        ),
                        'title' => $data['title'],
                        'exam_key' => $data['exam_key'],
                        'duration' => $data['duration'],
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                        'total_marks' => $total_marks,
                    );
                }

                //adding the exam data with pagination data
                $exam_data = array(
                    'current_page' => $exam_data_db['current_page'],
                    'data' => $exam_list,
                    'from' => $exam_data_db['from'],
                    'last_page' => $exam_data_db['last_page'],
                    'per_page' => $exam_data_db['per_page'],
                    'to' => $exam_data_db['to'],
                    'total' => $exam_data_db['total'],
                );
            } else {
                //if no data found
                $exam_data = null;
            }
            $response = array(
                'error' => false,
                'message' => trans('data_fetch_successfully'),
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

    public function getOnlineExamResultList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|numeric',
            'subject_id' => 'nullable|numeric'
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
            $student = Students::where('id', $request->child_id)->first();
            $class_section_id = $student->class_section_id;
            $class_id = $student->class_section->class_id;

            // current session year id
            $session_year = getSettings('session_year');
            $session_year_id = $session_year['session_year'];

            // get the class subject id on the basis of subject id passed
            // query meets the condition for both class section and class
            if (isset($request->subject_id) && !empty($request->subject_id)) {
                $online_exam_db = OnlineExam::where(['model_type' => 'App\Models\ClassSection', 'model_id' => $class_section_id, 'subject_id' => $request->subject_id, 'session_year_id' => $session_year_id])->whereHas('student_attempt', function ($q) use ($student) {
                    $q->where('student_id', $student->id);
                })->orWhere(function ($query) use ($class_id, $session_year_id, $request, $student) {
                    $query->where(['model_type' => 'App\Models\ClassSchool', 'model_id' => $class_id, 'session_year_id' => $session_year_id, 'subject_id' => $request->subject_id])->with('subject')->whereHas('student_attempt', function ($q) use ($student) {
                        $q->where('student_id', $student->id);
                    });
                })->with('subject')->paginate(10)->toArray();
            } else {
                $online_exam_db = OnlineExam::where(['model_type' => 'App\Models\ClassSection', 'model_id' => $class_section_id, 'session_year_id' => $session_year_id])->whereHas('student_attempt', function ($q) use ($student) {
                    $q->where('student_id', $student->id);
                })->orWhere(function ($query) use ($class_id, $session_year_id, $student) {
                    $query->where(['model_type' => 'App\Models\ClassSchool', 'model_id' => $class_id, 'session_year_id' => $session_year_id])->with('subject')->whereHas('student_attempt', function ($q) use ($student) {
                        $q->where('student_id', $student->id);
                    });
                })->with('subject')->paginate(10)->toArray();
            }
            $exam_list_data = array();
            foreach ($online_exam_db['data'] as $data) {
                //get the choice question id
                $exam_submitted_question_ids = OnlineExamStudentAnswer::where(['student_id' => $student->id, 'online_exam_id' => $data['id']])->pluck('question_id');
                $exam_submitted_date = OnlineExamStudentAnswer::where(['student_id' => $student->id, 'online_exam_id' => $data['id']])->pluck('submitted_date')->first();

                $question_ids = OnlineExamQuestionChoice::whereIn('id', $exam_submitted_question_ids)->pluck('question_id');


                $exam_attempted_answers = OnlineExamStudentAnswer::where(['student_id' => $student->id, 'online_exam_id' => $data['id']])->pluck('option_id');

                //removes the question id of the question if one of the answer of particular question is wrong
                foreach ($question_ids as $question_id) {
                    $check_questions_answers_exists = OnlineExamQuestionAnswer::where('question_id', $question_id)->whereNotIn('answer', $exam_attempted_answers)->count();
                    if ($check_questions_answers_exists) {
                        unset($question_ids[array_search($question_id, $question_ids->toArray())]);
                    }
                }

                $exam_correct_answers_question_id = OnlineExamQuestionAnswer::whereIn('question_id', $question_ids)->whereIn('answer', $exam_attempted_answers)->pluck('question_id');

                // get the data of only attempted data
                $total_obtained_marks_exam = OnlineExamQuestionChoice::select(DB::raw("sum(marks)"))->where('online_exam_id', $data['id'])->whereIn('question_id', $exam_correct_answers_question_id)->first();
                $total_obtained_marks_exam = $total_obtained_marks_exam['sum(marks)'];
                $total_marks_exam = OnlineExamQuestionChoice::select(DB::raw("sum(marks)"))->where('online_exam_id', $data['id'])->first();
                $total_marks_exam = $total_marks_exam['sum(marks)'];

                $exam_list_data[] = array(
                    'online_exam_id' => $data['id'],
                    'subject' => array(
                        'id' => $data['subject_id'],
                        'name' => $data['subject']['name'] . ' - ' . $data['subject']['type'],
                    ),
                    'title' => $data['title'],
                    'obtained_marks' => $total_obtained_marks_exam ?? "0",
                    'total_marks' => $total_marks_exam ?? "0",
                    'exam_submitted_date' => $exam_submitted_date ??  date('Y-m-d', strtotime($data['end_date']))
                );
            }
            $exam_list = array(
                'current_page' => $online_exam_db['current_page'],
                'data' => $exam_list_data ?? '',
                'from' => $online_exam_db['from'],
                'last_page' => $online_exam_db['last_page'],
                'per_page' => $online_exam_db['per_page'],
                'to' => $online_exam_db['to'],
                'total' => $online_exam_db['total'],
            );
            $response = array(
                'error' => false,
                'data' => $exam_list ?? '',
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

    public function getOnlineExamResult(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required_|numeric',
            'online_exam_id' => 'required|numeric',
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
            $student = Students::where('id', $request->child_id)->first();

            //get the total questions count
            $total_questions = OnlineExamQuestionChoice::where('online_exam_id', $request->online_exam_id)->count();

            //get the exam's choiced question id
            $exam_choiced_question_ids = OnlineExamStudentAnswer::where(['student_id' => $student->id, 'online_exam_id' => $request->online_exam_id])->pluck('question_id');

            //get the questions id
            $question_ids = OnlineExamQuestionChoice::whereIn('id', $exam_choiced_question_ids)->pluck('question_id');

            //get the options submitted by student
            $exam_attempted_answers = OnlineExamStudentAnswer::where(['student_id' => $student->id, 'online_exam_id' => $request->online_exam_id])->pluck('option_id');

            //removes the question id of the question if one of the answer of particular question is wrong
            foreach ($question_ids as $question_id) {
                $check_questions_answers_exists = OnlineExamQuestionAnswer::where('question_id', $question_id)->whereNotIn('answer', $exam_attempted_answers)->count();
                if ($check_questions_answers_exists) {
                    unset($question_ids[array_search($question_id, $question_ids->toArray())]);
                }
            }

            // get the correct answers counter
            $exam_correct_answers = OnlineExamQuestionAnswer::whereIn('question_id', $question_ids)->whereIn('answer', $exam_attempted_answers)->groupby('question_id')->pluck('question_id')->count();

            // question id of correct answers
            $exam_correct_answers_question_id = OnlineExamQuestionAnswer::whereIn('question_id', $question_ids)->whereIn('answer', $exam_attempted_answers)->pluck('question_id');

            //data of correct answers
            $exam_correct_answers_data = OnlineExamQuestionAnswer::whereIn('question_id', $question_ids)->whereIn('answer', $exam_attempted_answers)->groupby('question_id')->get();

            // array of correct answer with choiced exam id and marks
            $correct_answers_data = array();
            foreach ($exam_correct_answers_data as $correct_data) {
                $choice_questions = OnlineExamQuestionChoice::where(['online_exam_id' => $request->online_exam_id, 'question_id' => $correct_data->question_id])->first();
                $correct_answers_data[] = array(
                    'question_id' => $choice_questions->id,
                    'marks' => $choice_questions->marks
                );
            }

            // get questions ids
            $all_questions_ids = OnlineExamQuestionChoice::whereNotIn('question_id', $question_ids)->where('online_exam_id', $request->online_exam_id)->pluck('question_id');

            // get the incorrect answers && unattempted counter
            $exam_in_correct_answers = OnlineExamQuestionAnswer::whereIn('question_id', $all_questions_ids)->whereNotIn('answer', $exam_attempted_answers)->groupby('question_id')->pluck('question_id')->count();

            // data of in correct && unattempted answers
            $exam_in_correct_answers_data = OnlineExamQuestionAnswer::whereIn('question_id', $all_questions_ids)->whereNotIn('answer', $exam_attempted_answers)->groupby('question_id')->get();

            // array of in correct answer && unattempted with choiced exam id and marks
            $in_correct_answers_data = array();
            foreach ($exam_in_correct_answers_data as $in_correct_data) {
                $choice_questions = OnlineExamQuestionChoice::where(['online_exam_id' => $request->online_exam_id, 'question_id' => $in_correct_data->question_id])->first();
                if (isset($choice_questions) && !empty($choice_questions)) {
                    $in_correct_answers_data[] = array(
                        'question_id' => $choice_questions->id,
                        'marks' => $choice_questions->marks
                    );
                }
            }

            // total obtained and total marks
            $total_obtained_marks = OnlineExamQuestionChoice::select(DB::raw("sum(marks)"))->where('online_exam_id', $request->online_exam_id)->whereIn('question_id', $exam_correct_answers_question_id)->first();
            $total_obtained_marks = $total_obtained_marks['sum(marks)'];
            $total_marks = OnlineExamQuestionChoice::select(DB::raw("sum(marks)"))->where('online_exam_id', $request->online_exam_id)->first();
            $total_marks = $total_marks['sum(marks)'];

            // final array data
            $exam_result = array(
                'total_questions' => $total_questions,
                'correct_answers' => array(
                    'total_questions' => $exam_correct_answers,
                    'question_data' => $correct_answers_data ?? ''
                ),
                'in_correct_answers' => array(
                    'total_questions' => $exam_in_correct_answers,
                    'question_data' => $in_correct_answers_data ?? ''
                ),
                'total_obtained_marks' => $total_obtained_marks ?? '0',
                'total_marks' => $total_marks
            );
            $response = array(
                'error' => false,
                'data' => $exam_result ?? '',
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

    public function getOnlineExamReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|numeric',
            'subject_id' => 'required|numeric'
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
            $student = Students::where('id', $request->child_id)->first();
            $class_section_id = $student->class_section_id;
            $class_id = $student->class_section->class_id;


            $session_year = getSettings('session_year');
            $session_year_id = $session_year['session_year'];

            //get current
            $time_data = Carbon::now()->toArray();
            $current_date_time = $time_data['formatted'];

            $exam_query = OnlineExam::where(['model_type' => 'App\Models\ClassSection', 'model_id' => $class_section_id, 'subject_id' => $request->subject_id, 'session_year_id' => $session_year_id, ['start_date', '<=', $current_date_time]])->orWhere(function ($query) use ($class_id, $session_year_id, $request, $current_date_time) {
                $query->where(['model_type' => 'App\Models\ClassSchool', 'model_id' => $class_id, 'subject_id' => $request->subject_id, 'session_year_id' => $session_year_id, ['start_date', '<=', $current_date_time]]);
            });
            $exam_exists = $exam_query->count();
            $exam_query_without_session_year = OnlineExam::where(['model_type' => 'App\Models\ClassSection', 'model_id' => $class_section_id, 'subject_id' => $request->subject_id, ['start_date', '<=', $current_date_time]])->orWhere(function ($query) use ($class_id, $session_year_id, $request, $current_date_time) {
                $query->where(['model_type' => 'App\Models\ClassSchool', 'model_id' => $class_id, 'subject_id' => $request->subject_id, ['start_date', '<=', $current_date_time]]);
            });

            // checks the exams exists
            if (isset($exam_exists) && !empty($exam_exists)) {
                //total online exams id and counts
                $total_exam_ids = $exam_query->pluck('id');
                //online exam ids attempted
                $attempted_online_exam_ids = StudentOnlineExamStatus::where('student_id', $student->id)->whereIn('online_exam_id', $total_exam_ids)->pluck('online_exam_id');

                //get the submitted answers (i.e. option id)
                $online_exams_attempted_answers = OnlineExamStudentAnswer::where('student_id', $student->id)->whereIn('online_exam_id', $total_exam_ids)->pluck('option_id');

                //get the submitted choiced question id
                $online_exams_submitted_question_ids = OnlineExamStudentAnswer::where('student_id', $student->id)->whereIn('online_exam_id', $total_exam_ids)->pluck('question_id');

                //get the questions id
                $get_question_ids = OnlineExamQuestionChoice::whereIn('id', $online_exams_submitted_question_ids)->pluck('question_id');

                //removes the question id of the question if one of the answer of particular question is wrong
                foreach ($get_question_ids as $question_id) {
                    $check_questions_answers_exists = OnlineExamQuestionAnswer::where('question_id', $question_id)->whereNotIn('answer', $online_exams_attempted_answers)->count();
                    if ($check_questions_answers_exists) {
                        unset($get_question_ids[array_search($question_id, $get_question_ids->toArray())]);
                    }
                }
                //get the correct answers question id
                $correct_answers_question_id = OnlineExamQuestionAnswer::whereIn('question_id', $get_question_ids)->whereIn('answer', $online_exams_attempted_answers)->pluck('question_id');


                //total exams
                $total_exams = $exam_query_without_session_year->count();

                //total exam attempted
                $total_attempted_exams = StudentOnlineExamStatus::where('student_id', $student->id)->whereIn('online_exam_id', $total_exam_ids)->count();

                // total missed exams
                $total_missed_exams = $exam_query_without_session_year->whereNotIn('id', $attempted_online_exam_ids)->count();

                // get the correct choiced question id and marks
                $total_obtained_marks = OnlineExamQuestionChoice::select(DB::raw("sum(marks)"))->whereIn('online_exam_id', $total_exam_ids)->whereIn('question_id', $correct_answers_question_id)->first();
                $total_obtained_marks = $total_obtained_marks['sum(marks)'];

                //overall total marks
                $total_marks = OnlineExamQuestionChoice::select(DB::raw("sum(marks)"))->whereIn('online_exam_id', $total_exam_ids)->first();
                $total_marks = $total_marks['sum(marks)'];

                if ($total_obtained_marks) {
                    $percentage = number_format(($total_obtained_marks * 100) / $total_marks, 2);
                }


                // particular online exam data
                $online_exams_db = OnlineExam::where(['model_type' => 'App\Models\ClassSection', 'model_id' => $class_section_id, 'subject_id' => $request->subject_id, 'session_year_id' => $session_year_id, ['start_date', '<=', $current_date_time]])->orWhere(function ($query) use ($class_id, $session_year_id, $request, $current_date_time) {
                    $query->where(['model_type' => 'App\Models\ClassSchool', 'model_id' => $class_id, 'subject_id' => $request->subject_id, 'session_year_id' => $session_year_id, ['start_date', '<=', $current_date_time]]);
                })->with(['student_attempt' => function ($q) use ($student) {
                    $q->where('student_id', $student->id);
                }])->has('question_choice')->paginate(10)->toArray();


                $exam_list = array();
                $total_obtained_marks_exam = '';
                foreach ($online_exams_db['data'] as $data) {
                    $exam_submitted_question_ids = OnlineExamStudentAnswer::where(['student_id' => $student->id, 'online_exam_id' => $data['id']])->pluck('question_id');
                    $get_exam_question_ids = OnlineExamQuestionChoice::whereIn('id', $exam_submitted_question_ids)->pluck('question_id');


                    $exam_attempted_answers = OnlineExamStudentAnswer::where(['student_id' => $student->id, 'online_exam_id' => $data['id']])->pluck('option_id');


                    //removes the question id of the question if one of the answer of particular question is wrong
                    foreach ($get_exam_question_ids as $question_id) {
                        $check_questions_answers_exists = OnlineExamQuestionAnswer::where('question_id', $question_id)->whereNotIn('answer', $exam_attempted_answers)->count();
                        if ($check_questions_answers_exists) {
                            unset($get_exam_question_ids[array_search($question_id, $get_exam_question_ids->toArray())]);
                        }
                    }

                    $exam_correct_answers_question_id = OnlineExamQuestionAnswer::whereIn('question_id', $get_exam_question_ids)->whereIn('answer', $exam_attempted_answers)->pluck('question_id');

                    $total_obtained_marks_exam = OnlineExamQuestionChoice::select(DB::raw("sum(marks)"))->where('online_exam_id', $data['id'])->whereIn('question_id', $exam_correct_answers_question_id)->first();
                    $total_obtained_marks_exam = $total_obtained_marks_exam['sum(marks)'];
                    $total_marks_exam = OnlineExamQuestionChoice::select(DB::raw("sum(marks)"))->where('online_exam_id', $data['id'])->first();
                    $total_marks_exam = $total_marks_exam['sum(marks)'];

                    $exam_list[] = array(
                        'online_exam_id' => $data['id'],
                        'title' => $data['title'],
                        'obtained_marks' => $total_obtained_marks_exam ?? "0",
                        'total_marks' => $total_marks_exam ?? "0",
                    );
                }


                // array of final data
                $online_exam_report_data = array(
                    'total_exams' => $total_exams,
                    'attempted' => $total_attempted_exams,
                    'missed_exams' => $total_missed_exams,
                    'total_marks' => $total_marks ?? "0",
                    'total_obtained_marks' => $total_obtained_marks ?? "0",
                    'percentage' => $percentage ?? "0",
                    'exam_list' => array(
                        'current_page' => $online_exams_db['current_page'],
                        'data' => $exam_list,
                        'from' => $online_exams_db['from'],
                        'last_page' => $online_exams_db['last_page'],
                        'per_page' => $online_exams_db['per_page'],
                        'to' => $online_exams_db['to'],
                        'total' => $online_exams_db['total'],
                    )
                );
            }
            $response = array(
                'error' => false,
                'data' => $online_exam_report_data ?? [],
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

    public function getAssignmentReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|numeric',
            'subject_id' => 'required|numeric'
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
            $student = Students::where('id', $request->child_id)->first();

            $session_year = getSettings('session_year');
            $session_year_id = $session_year['session_year'];

            // get the assignments ids
            $assingment_ids = Assignment::where(['class_section_id' => $student->class_section_id, 'session_year_id' => $session_year_id, 'subject_id' => $request->subject_id])->pluck('id');

            //total assignments of class
            $total_assignments = Assignment::where(['class_section_id' => $student->class_section_id, 'session_year_id' => $session_year_id, 'subject_id' => $request->subject_id])->count();

            //total assignment submiited
            $total_submitted_assignments = AssignmentSubmission::where('student_id', $student->id)->whereIn('assignment_id', $assingment_ids)->count();

            // submitted assingment id
            $submitted_assignment_ids = AssignmentSubmission::where('student_id', $student->id)->whereIn('assignment_id', $assingment_ids)->pluck('assignment_id');

            //total assignment unsubmitted
            $total_assingment_unsubmitted = Assignment::where(['class_section_id' => $student->class_section_id, 'subject_id' => $request->subject_id])->whereNotIn('id', $submitted_assignment_ids)->count();

            //total points of assignment submitted
            $total_assignment_submitted_points = Assignment::select(DB::raw("sum(points)"))->where('class_section_id', $student->class_section_id)->whereIn('id', $submitted_assignment_ids)->whereNot('points', null)->first();
            $total_assignment_submitted_points = $total_assignment_submitted_points['sum(points)'];

            // total obtained assignment points
            $assingment_id_with_points = Assignment::where(['class_section_id' => $student->class_section_id, 'subject_id' => $request->subject_id])->whereIn('id', $submitted_assignment_ids)->whereNot('points', null)->pluck('id');
            $total_points_obtained = AssignmentSubmission::select(DB::raw("sum(points)"))->whereIn('assignment_id', $assingment_id_with_points)->first();
            $total_points_obtained = $total_points_obtained['sum(points)'];

            if ($total_points_obtained) {
                //percentage
                $percentage = number_format(($total_points_obtained * 100) / $total_assignment_submitted_points, 2);
            }


            $submitted_assignment_data_db = Assignment::with('submission')->where(['class_section_id' => $student->class_section_id, 'subject_id' => $request->subject_id])->whereIn('id', $submitted_assignment_ids)->whereNot('points', null);
            $submitted_assignment_data_with_points = $submitted_assignment_data_db->paginate(10)->toArray();

            $submitted_assingment_data = array();
            foreach ($submitted_assignment_data_with_points['data'] as $submitted_data) {
                $submitted_assingment_data[] = array(
                    'assignment_id' => $submitted_data['id'],
                    'assignment_name' => $submitted_data['name'],
                    'obtained_points' => $submitted_data['submission']['points'],
                    'total_points' => $submitted_data['points']
                );
            }
            $assingment_report = array(
                'assignments' => $total_assignments,
                'submitted_assignments' => $total_submitted_assignments,
                'unsubmitted_assignments' => $total_assingment_unsubmitted,
                'total_points' => $total_assignment_submitted_points ?? "0",
                'total_obtained_points' => $total_points_obtained ?? "0",
                'percentage' => $percentage ?? "0",
                'submitted_assignment_with_points_data' => array(
                    'current_page' => $submitted_assignment_data_with_points['current_page'],
                    'data' => $submitted_assingment_data,
                    'from' => $submitted_assignment_data_with_points['from'],
                    'last_page' => $submitted_assignment_data_with_points['last_page'],
                    'per_page' => $submitted_assignment_data_with_points['per_page'],
                    'to' => $submitted_assignment_data_with_points['to'],
                    'total' => $submitted_assignment_data_with_points['total'],
                )
            );
            $response = array(
                'error' => false,
                'data' => $assingment_report,
                'code' => 200,
            );
        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response, 200, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    public function getFeesPaymentTransactions(Request $request)
    {
        try {

            $parent_id = Auth::user()->parent->id;
            $child_id = Students::where('father_id',$parent_id)->orWhere('mother_id',$parent_id)->orWhere('guardian_id',$parent_id)->pluck('id');
            $fees_payment_transactions = PaymentTransaction::whereIn('student_id', $child_id)->with('session_year')->with(['student' => function ($q) {
                $q->select('id', 'user_id')->with('user:id,first_name,last_name');
            }])->orderBy('id', 'desc')->paginate(15);

            foreach ($fees_payment_transactions as $transaction) {
                $date = $transaction->date;
                if($date)
                {
                    $datetime = Carbon::parse($date);
                    $time = $datetime->format('H:i:s');
                    $addHour = $datetime->copy()->addHour();
                    if (Carbon::now()->gt($addHour)) {
                        $transaction->payment_status = 0;
                        $transaction->save();
                    }
                }
            }
            $fees_payment_transactions =  $fees_payment_transactions->toArray();
            $response = array(
                'error' => false,
                'data' => array(
                    'current_page' => $fees_payment_transactions['current_page'],
                    'transaction-data' => $fees_payment_transactions['data'],
                    'from' => $fees_payment_transactions['from'],
                    'last_page' => $fees_payment_transactions['last_page'],
                    'per_page' => $fees_payment_transactions['per_page'],
                    'to' => $fees_payment_transactions['to'],
                    'total' => $fees_payment_transactions['total'],
                ),
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

    public function getProfileDetails(){
        try{
            $user = Auth::user();
            $children = Students::where('father_id', $user->parent->id)->orWhere('mother_id', $user->parent->id)->orWhere('guardian_id', $user->parent->id)->with('class_section')->get();
            $user = flattenMyModel($user);

            foreach ($children as $child) {
                $child->first_name = $child->user->first_name;
                $child->last_name = $child->user->last_name;
                $child->image = $child->user->image;
                $child->dob = $child->user->dob;
                $child->current_address = $child->user->current_address;
                $child->permanent_address = $child->user->permanent_address;

                $dynamicFields = [];
                $dynamicField = $child->dynamic_fields;

                $data = json_decode($dynamicField, true);

                if (is_array($data)) {
                    foreach ($data as $item) {
                        foreach ($item as $key => $value) {
                            $dynamicFields[$key] = $value;
                        }
                    }
                }else{
                    $dynamicFields = $data;
                }
                $child->dynamic_fields =  $dynamicFields;


                unset($child->user);
                //Set Class Section name
                $classSectionName = $child->class_section->class->name . " " . $child->class_section->section->name;

                // Set Stream name
                $streamName = $child->class_section->class->streams->name ?? null;
                if ($streamName !== null) {
                    $child->class_section_name = $classSectionName . " " . $streamName;
                } else {
                    $child->class_section_name = $classSectionName;
                }

                //Set Medium name
                $child->medium_name = $child->class_section->class->medium->name;


                //Set Shift name
                $child->shift_id = $child->class_section->class->shifts->id ?? '';
                $child->shift = Shift::find($child->shift_id);
                if ($child->shift) {
                    $child->shift->id;
                    $child->shift->title;
                    $child->shift->start_time;

                }

                unset($child->class_section);
                //Set Category name
                $child->category_name = $child->category->name;
                unset($child->category);
            }
            $data = array_merge($user, ['children' => $children->toArray()]);
            $response = array(
                'error' => false,
                'message' => 'Data Fetched Successfully',
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

    // Make Transaction Fail API
    public function failPaymentTransactionStatus(Request $request){
        try{
            $update_status = PaymentTransaction::findOrFail($request->payment_transaction_id);
            $total_amount = $update_status->total_amount;
            $update_status->payment_status = 0;
            $update_status->save();

            $parent_id = Auth::user()->parent->id;
            $user = Parents::where('id', $parent_id)->pluck('user_id');
            $body = 'Amount :- ' . $total_amount;
            $type = 'online';
            $image = null;
            $userinfo = null;

            $notification = new Notification();
            $notification->send_to = 2;
            $notification->title = 'Payment Failed';
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

            send_notification($user, 'Payment Failed', $body, $type, $image, $userinfo);
            $response = array(
                'error' => false,
                'message' => 'Data Updated Successfully',
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
            $user = Auth::user()->id;
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

    public function getPaymentStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required',
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
            $settings = getSettings();

            if (isset($settings['razorpay_status']) && $settings['razorpay_status']) {
                $secretkey = $settings['razorpay_secret_key'] ?? "";
            }

            if (isset($settings['stripe_status']) && $settings['stripe_status']) {
                $secretkey = $settings['stripe_secret_key'] ?? "";
            }

            $url = 'https://api.stripe.com/v1/payment_intents/' . $request->payment_intent_id;

            $payment_status = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretkey,
            ])->get($url);

            $data = $payment_status['status'];

            $response = array(
                'error' => false,
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

    public function getChatUserList(Request $request){
        try{

            $offset = $request->offset;
            $limit = $request->limit;
            $search = $request->search;

            $user = $request->user();
            $childrens = $user->parent->children()->get();


            foreach($childrens as $children){

                $student_subject = $children->subjects();

                $core_subjects= array_column($student_subject["core_subject"],'subject_id');

                $elective_subjects = $student_subject["elective_subject"] ?? [];
                if ($elective_subjects) {
                    $elective_subjects = $elective_subjects->pluck('subject_id')->toArray();
                }
                $subject_id = array_merge($core_subjects,$elective_subjects);

                $class_section_ids[] = $children->class_section->id;

            }



            $class_teachers_id = ClassTeacher::whereIn('class_section_id', $class_section_ids)->pluck('class_teacher_id')->toArray();
            $subject_teacher_id = SubjectTeacher::whereIn('class_section_id',$class_section_ids)->whereIn('subject_id',$subject_id)->pluck('teacher_id')->toArray();


            $teacher_ids = array_merge($class_teachers_id, $subject_teacher_id);

            if ($search) {
                $teachers = Teacher::whereIn('id', $teacher_ids)
                    ->where(function ($query) use ($search) {
                        $query->whereHas('user', function ($subquery) use ($search) {
                            $subquery->where('first_name', 'like', "%$search%")
                                ->orWhere('last_name', 'like', "%$search%");
                        });
                    })
                    ->with('user:id,first_name,last_name,image,mobile,email', 'subjects.subject')->offset($offset)->limit($limit)
                    ->get();
            } else{
                $teachers = Teacher::whereIn('id', $teacher_ids)->with('user:id,first_name,last_name,image,mobile,email' , 'subjects.subject')->offset($offset)->limit($limit)
                ->get();
            }


            $data = [];

            foreach ($teachers as $teacher) {
                // $teacher = $subject_teacher->teacher;
                $unreadCount = 0;
                $subjectData = [];

                $class_teacher_section_ids = ClassTeacher::where('class_teacher_id', $teacher->id)->pluck('class_section_id')->toArray();
                $subject_teacher_class_section_ids = SubjectTeacher::where('teacher_id', $teacher->id)->pluck('class_section_id')->toArray();

                $class_section_ids = array_merge($class_teacher_section_ids, $subject_teacher_class_section_ids);

                $classes = ClassSection::whereIn('id',$class_teacher_section_ids)->with('class.medium', 'section','class.streams','class.shifts')->get();
                $studentsArray = [];
                foreach($childrens as $child)
                {

                    $students = Students::with('user')->where('id',$child->id)->whereIn('class_section_id',$class_section_ids)->get();

                    foreach ($students as $student) {
                        $studentsArray[] = $student->user->first_name . ' ' . $student->user->last_name;
                    }
                }

                $class_names= [];
                foreach ($classes as $class) {
                    if($class){
                        $class_names[] = ($class->class->name ?? '') .' '. ($class->section->name ?? '') .' '.  ($class->class->medium->name ?? '');
                    }
                }

                foreach ($teacher->subjects as $subject) {
                    $subjectData[] = [
                        'id' => $subject->subject->id ?? '',
                        'name' => $subject->subject->name ?? '',
                    ];
                }


                $lastMessage = ChatMessage::where(function ($query) use ($user, $teacher) {
                    $query->where('modal_id', $teacher->user->id)
                            ->where('sender_id', $user->id);
                })
                ->orWhere(function ($query) use ($user, $teacher) {
                    $query->where('sender_id', $teacher->user->id)
                            ->where('modal_id', $user->id);
                })
                ->select('id','body','date')
                ->latest()
                ->first();

                $lastReadMessage = ReadMessage::where('modal_id',$user->id)->where('user_id', $teacher->user->id)->first();


                if ($lastReadMessage) {

                    $lastReadMessageId = $lastReadMessage->last_read_message_id;
                    if(!empty($lastReadMessageId))
                    {
                        $unreadCount = ChatMessage::where('sender_id',$teacher->user->id)->where('modal_id',$user->id)->where('id', '>', $lastReadMessageId)->count();
                    }else{
                        $unreadCount = ChatMessage::where('sender_id',$teacher->user->id)->where('modal_id',$user->id)->count();
                    }

                }

                $data[] = [
                    'id' => $teacher->id,
                    'user_id' => $teacher->user->id,
                    'first_name' => $teacher->user->first_name,
                    'last_name' => $teacher->user->last_name,
                    'qualification' => $teacher->qualification,
                    'email' => $teacher->user->email,
                    'image' =>  $teacher->user->image,
                    'class_teacher' => $class_names,
                    'dob' => $teacher->user->dob,
                    'mobile_no' => $teacher->user->mobile,
                    'subjects' => $subjectData,
                    'student_name' => $studentsArray ?? [],
                    'last_message' => $lastMessage ?? null,
                    'unread_message' => $unreadCount ?? 0
                ];

            }

            $total_items = count($data);

            $unreadusers = array_filter($data, function ($teacher) {
                return $teacher['unread_message'] > 0;
            });

            $totalunreadusers = count($unreadusers);

            $data = collect($data)->sortByDesc(function ($user) {
                return optional($user['last_message'])->date ?? 0;
            })->values();

            $response = [
                'error' => false,
                'message' => 'Data Fetched Successfully',
                'data' => [
                    'items' => $data,
                    'total_items' => $total_items,
                    'total_unread_users' => $totalunreadusers,
                ],
                'code' => 100,
            ];
        }catch(\Exception $e){
            $response = array(
                'error' => true,
                'message' => trans('error_occurred'),
                'code' => 103,
            );
        }
        return response()->json($response);
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
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $uploadedFile) {

                    $originalName = $uploadedFile->getClientOriginalName();
                    $filePath = $uploadedFile->storeAs('chatfile', $originalName, 'public');

                    $file = new ChatFile();
                    $file->file_type = 1;
                    $file->file_name = $filePath;
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

            $parent =  Parents::with('user')->where('user_id',$sender_id)->first();

            $lastReadMessage = ReadMessage::where('modal_id', $receiver_id)->where('user_id',$parent->user_id)->first();

            if ($lastReadMessage) {

                $lastReadMessageId = $lastReadMessage->last_read_message_id;
                if(!empty($lastReadMessageId))
                {
                    $unreadCount = ChatMessage::where('modal_id',$receiver_id)->where('sender_id',$parent->user_id)->where('id', '>', $lastReadMessageId)->count();
                }else
                {
                    $unreadCount = ChatMessage::where('modal_id',$receiver_id)->where('sender_id',$parent->user_id)->count();
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

                $subjectArray = [];
                foreach($subjects as $subject)
                {
                    $subjectArray[] = [
                        'id' => $subject->id,
                        'name' => $subject->name,
                    ];
                }

                $childArray[] = [
                    'id' => $child->id,
                    'user_id' => $child->user_id,
                    'child_name' => $child->user->first_name .' '.$child->user->last_name,
                    'class_name' => $child->class_section->class->name .' '.$child->class_section->section->name .' '.$child->class_section->class->medium->name,
                    'admission_no' => $child->admission_no,
                    'image' => $child->user->image,
                    'subject' => $subjectArray ?? []
                ];
            }

            $userinfo = [
                'id' => $parent->id,
                'user_id' => $parent->user_id, // Assuming this is the correct property name
                'first_name' => $parent->user->first_name,
                'last_name' => $parent->user->last_name,
                'email' => $parent->user->email,
                'mobile_no' => $parent->user->mobile,
                'occupation' =>$parent->occupation,
                'image' =>$parent->user->image,
                'last_message' =>  $data ?? null,
                'children' => $childArray ?? [],
                'isParent' => 1,
                'unread_message' => $unreadCount ?? 0
            ];

            $title = $parent->user->first_name.' '.$parent->user->last_name;
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
                if (isset($message['file'])) {
                    $message['files'] = collect($message['file'])->map(function ($file) {
                        return asset('storage/' . $file['file_name']);
                    })->toArray();

                    unset($message['file']);
                } else {
                    $message['files'] = []; // or handle the case where 'file' is not set
                }
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
            $user = Auth::id();
            $teacher = $request->user_id;

            $lastMessage = ChatMessage::where('sender_id',$teacher)->where('modal_id',$user)->latest()->first();
            if($lastMessage){
                $message_id = $lastMessage->id;
            }

            // Update Read Message id
            $readMessage = ReadMessage::where('modal_id',$user)->where('user_id',$teacher)->first();

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
