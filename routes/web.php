<?php

use App\Models\Grade;
use App\Models\ExamTimetable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\MediumController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ParentsController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FeesTypeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\OnlineExamController;
use App\Http\Controllers\WebSettingController;
use App\Http\Controllers\ClassSchoolController;
use App\Http\Controllers\LessonTopicController;
use App\Http\Controllers\SessionYearController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ClassTeacherController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SystemUpdateController;
use App\Http\Controllers\ExamTimetableController;
use App\Http\Controllers\StudentSessionController;
use App\Http\Controllers\SubjectTeacherController;
use App\Http\Controllers\OnlineExamQuestionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Auth::routes();
Route::get('/', [WebController::class,'index']);
Route::get('about',[WebController::class,'about'])->name('about.us');
Route::get('contact',[WebController::class, 'contact_us'])->name('contact.us');
Route::get('photo',[WebController::class, 'photo'])->name('photo');
Route::get('photo-gallery/{id}',[WebController::class, 'photo_details'])->name('photo.gallery');
Route::get('video',[WebController::class, 'video'])->name('video');
Route::get('video-gallery',[WebController::class, 'video_details'])->name('video.gallery');
Route::post('contact-us/store',[WebController::class,'contact_us_store'])->name('contact_us.store');

Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
// Route::get('/login', [WebController::class, 'auth.login'])->name('login');

Route::group(['middleware' => ['Role', 'auth']], function () {
    Route::group(['middleware' => 'language'], function () {
        // Route::get('/home', [HomeController::class, 'index']);
        Route::get('home', [HomeController::class, 'index'])->name('home');
        Route::get('/logout', [HomeController::class, 'logout'])->name('logout');
        Route::get('subject-by-class-section', [HomeController::class, 'getSubjectByClassSection'])->name('class-section.by.subject');
        Route::get('teacher-by-class-subject', [HomeController::class, 'getTeacherByClassSubject'])->name('teacher.by.class.subject');
        ///new reset password controller
        Route::get('home/reset_password', [HomeController::class, 'resetPasswordView']);

        Route::get('roles-list/{id}', [RoleController::class, 'showList'])->name('roles-list');
        Route::resource('roles', RoleController::class);
        Route::resource('users', UserController::class);

        Route::get('settings', [SettingController::class, 'index']);
        Route::post('settings', [SettingController::class, 'update']);

        Route::get('fcm-settings', [SettingController::class, 'fcm_index']);
        Route::post('fcm-settings', [SettingController::class, 'fcm_update']);

        Route::resource('medium', MediumController::class);
        Route::get('medium_list', [MediumController::class, 'show']);

        Route::resource('teachers', TeacherController::class);
        Route::get('teacher_details',[TeacherController::class,'teacherListIndex'])->name('teacher.details');
        Route::get('teacher_list', [TeacherController::class, 'show']);


        Route::resource('section', SectionController::class);
        Route::get('section_list', [SectionController::class, 'show']);

        Route::get('class/subject', [ClassSchoolController::class, 'subject'])->name('class.subject');
        Route::put('class/subject/{class_id}', [ClassSchoolController::class, 'update_subjects'])->name('class.subject.update');
        Route::delete('class/subject/{class_subject_id}', [ClassSchoolController::class, 'subject_destroy'])->name('class.subject.delete');
        Route::delete('class/subject-group/{group_id}', [ClassSchoolController::class, 'subject_group_destroy'])->name('class.subject-group.delete');
        Route::get('class/subject/list', [ClassSchoolController::class, 'subject_list'])->name('class.subject.list');
        Route::get('class-list', [ClassSchoolController::class, 'show']);
        Route::resource('class', ClassSchoolController::class);

        Route::get('class-subject-list/{medium_id}', [ClassSchoolController::class, 'getSubjectsByMediumId']);

        Route::get('assign/class/teacher', [ClassTeacherController::class, 'teacher'])->name('class.teacher');
        Route::post('class/teacher/store', [ClassTeacherController::class, 'assign_teacher'])->name('class.teacher.store');
        Route::get('class-teacher-list', [ClassTeacherController::class, 'show']);
        Route::post('remove-class-teacher/{id}/{class_teacher_id}', [ClassTeacherController::class, 'removeClassTeacher']);

        Route::resource('subject', SubjectController::class);
        Route::get('subject-list', [SubjectController::class, 'show']);

        Route::get('/parent/search', [ParentsController::class, 'search']);
        Route::resource('parents', ParentsController::class);
        Route::get('parents_list', [ParentsController::class, 'show']);

        Route::resource('session-years', SessionYearController::class);
        Route::get('session_years_list', [SessionYearController::class, 'show']);
        Route::delete('remove-installment-data/{id}',[SessionYearController::class, 'deleteInstallmentData']);

        Route::get('students-list', [StudentController::class, 'show'])->name('students.list');
        Route::get('students/assign-class', [StudentController::class, 'assignClass'])->name('students.assign-class');
        Route::post('students/assign-class', [StudentController::class, 'assignClass_store'])->name('students.assign-class.store');
        Route::get('students/new-student-list', [StudentController::class, 'newStudentList'])->name('students.new-student-list');
        Route::get('students/create_bulk', [StudentController::class, 'createBulkData'])->name('students.create-bulk-data');
        Route::post('students/store_bulk', [StudentController::class, 'storeBulkData'])->name('students.store-bulk-data');
        Route::resource('students', StudentController::class);

        //student generate roll number
        Route::get('student/assign-roll-number',[StudentController::class, 'indexStudentRollNumber'])->name('students.index-students-roll-number');
        Route::get('student/list-assign-roll-number',[StudentController::class, 'listStudentRollNumber'])->name('students.list-students-roll-number');
        Route::post('student/store-roll-number',[StudentController::class, 'storeStudentRollNumber'])->name('students.store-roll-number');

        Route::resource('category', CategoryController::class);
        Route::get('category_list', [CategoryController::class, 'show']);

        Route::resource('subject-teachers', SubjectTeacherController::class);
        Route::get('subject-teachers-list', [SubjectTeacherController::class, 'show']);

        Route::resource('timetable', TimetableController::class);
        Route::get('timetable-list', [TimetableController::class, 'show']);
        Route::get('checkTimetable', [TimetableController::class, 'checkTimetable']);

        Route::get('get-subject-by-class-section', [TimetableController::class, 'getSubjectByClassSection']);
        Route::get('getteacherbysubject', [TimetableController::class, 'getteacherbysubject']);

        Route::get('gettimetablebyclass', [TimetableController::class, 'gettimetablebyclass'])->name('get.timetable.class');
        Route::get('gettimetablebyteacher', [TimetableController::class, 'gettimetablebyteacher']);
        Route::get('get-timetable-by-subject-teacher-class', [TimetableController::class, 'getTimetableBySubjectTeacherClass']);

        Route::get('class-timetable', [TimetableController::class, 'class_timetable']);
        Route::get('teacher-timetable', [TimetableController::class, 'teacher_timetable']);

        Route::resource('attendance', AttendanceController::class);
        Route::get('view-attendance', [AttendanceController::class, 'view'])->name("attendance.view");
        Route::get('student-attendance-list', [AttendanceController::class, 'attendance_show']);
        Route::get('getAttendanceData', [AttendanceController::class, 'getAttendanceData']);
        Route::get('student-list', [AttendanceController::class, 'show']);
        Route::get('add-bulk-attendance', [AttendanceController::class, 'createBulkData'])->name('attendance.add-bulk-data');
        Route::post('attendance/store_bulk',[AttendanceController::class,'storeBulkData'])->name('attendance.store-bulk-data');
        Route::post('student/export',[AttendanceController::class,'studentExport'])->name('student-export');

        Route::resource('lesson', LessonController::class);
        Route::get('search-lesson', [LessonController::class, 'search']);
        Route::delete('file/delete/{id}', [LessonController::class, 'deleteFile'])->name('file.delete');
        Route::resource('lesson-topic', LessonTopicController::class);

        Route::resource('announcement', AnnouncementController::class);
        Route::get('announcement-list', [AnnouncementController::class, 'show']);
        Route::get('getAssignData', [AnnouncementController::class, 'getAssignData']);

        Route::resource('holiday', HolidayController::class);
        Route::get('holiday-list', [HolidayController::class, 'show']);
        Route::get('holiday-view', [HolidayController::class, 'holiday_view']);

        Route::resource('assignment', AssignmentController::class);
        Route::get('assignment-submission', [AssignmentController::class, 'viewAssignmentSubmission'])->name('assignment.submission');
        Route::put('assignment-submission/{id}', [AssignmentController::class, 'updateAssignmentSubmission'])->name('assignment.submission.update');
        Route::get('assignment-submission-list', [AssignmentController::class, 'assignmentSubmissionList'])->name('assignment.submission.list');

        Route::resource('sliders', SliderController::class);

        Route::get('exams/exam-result', [ExamController::class, 'getExamResultIndex'])->name('exams.get-result');
        Route::get('exams/show-result', [ExamController::class, 'showExamResult'])->name('exams.show-result');
        Route::post('exams/update-result-marks', [ExamController::class, 'updateExamResultMarks'])->name('exams.update-result-marks');

        Route::post('exams/submit-marks', [ExamController::class, 'submitMarks'])->name('exams.submit-marks');

        Route::get('exams/upload-marks', [ExamController::class, 'uploadMarks'])->name('exams.upload-marks');
        Route::get('exams/marks-list', [ExamController::class, 'marksList'])->name('exams.marks-list');

        Route::get('exams/get-exams/{class_id}', [ExamController::class, 'getExamByClass'])->name('exams.classes');
        Route::delete('/delete-exam-class/{exam_id}/{class_id}', [ExamController::class, 'deleteExamClass']);
        Route::get('exams/get-subjects/{class_id}/{exam_id}', [ExamController::class, 'getSubjectByExam'])->name('exams.subject');
        Route::post('exams/publish/{id}', [ExamController::class, 'publishExamResult'])->name('exams.publish');
        Route::get('exams/get-publish-exam/{class_id}',[ExamController::class,'getPublishExam'])->name('exam.publish.list');
        Route::resource('exams', ExamController::class);

        Route::post('exams/update-timetable', [ExamTimetableController::class, 'updateTimetable'])->name('exams.update-timetable');
        Route::delete('exams/delete-timetable/{id}', [ExamTimetableController::class, 'deleteTimetable'])->name('exams.delete-timetable');
        Route::get('grades', [ExamController::class, 'indexGrades'])->name('grades');

        Route::get('exams/get-exam-subjects/{exam_id}', [ExamController::class, 'getExamSubjects'])->name('exams.subjects');

        Route::post('create-grades', [ExamController::class, 'createGrades'])->name('create-grades');
        Route::get('show-grades', [ExamController::class, 'showGrades'])->name('show-grades');
        Route::put('update-grades/{grade_id}', [ExamController::class, 'updateGrades'])->name('update-grades');
        Route::delete('destroy-grades/{grade_id}', [ExamController::class, 'destroyGrades'])->name('destroy-grades');

        Route::resource('exam-timetable', ExamTimetableController::class);
        Route::get('exam/get-classes/{exam_id}', [ExamTimetableController::class, 'getClassesByExam'])->name('exams.classes');
        Route::get('exam/get-subjects/{class_id}', [ExamTimetableController::class, 'getSubjectsByClass'])->name('exams.class-subjects');

        Route::get('email-settings', [SettingController::class, 'email_index'])->name('setting.email-config-index');
        Route::post('email-settings', [SettingController::class, 'email_update']);
        Route::post('verify-email-settings', [SettingController::class, 'verifyEmailConfigration'])->name('setting.varify-email-config');

        Route::get('privacy-policy/index', [SettingController::class, 'privacy_policy_index'])->name('privacy.index');
        Route::get('terms-condition', [SettingController::class, 'terms_condition_index']);
        Route::get('contact-us', [SettingController::class, 'contact_us_index']);
        Route::get('about-us', [SettingController::class, 'about_us_index']);

        Route::post('setting-update', [SettingController::class, 'setting_page_update']);
        Route::post('notification-setting',[SettingController::class, 'notification_setting']);

        Route::get('reset-password', function () {
            return view('students.reset_password');
        })->name('students.reset_password');
        Route::get('reset-password-list', [StudentController::class, 'reset_password']);
        Route::post('student-change-password', [StudentController::class, 'change_password']);

        Route::resource('promote-student', StudentSessionController::class);
        Route::get('getPromoteData', [StudentSessionController::class, 'getPromoteData']);
        Route::get('promote-student-list', [StudentSessionController::class, 'show']);

        Route::get('resetpassword', [HomeController::class, 'resetpassword'])->name('resetpassword');
        Route::get('checkPassword', [HomeController::class, 'checkPassword']);
        Route::post('changePassword', [HomeController::class, 'changePassword']);

        Route::get('edit-profile', [HomeController::class, 'editProfile'])->name('edit-profile');
        Route::post('update-profile', [HomeController::class, 'updateProfile'])->name('update-profile');

        Route::resource('language', LanguageController::class);
        Route::get('language-sample', [LanguageController::class, 'language_sample']);
        Route::get('language-list', [LanguageController::class, 'show']);

        Route::get('set-language/{lang}', [LanguageController::class, 'set_language']);
        Route::get('sendtest', [SettingController::class, 'test_mail']);

        // fees
        Route::resource('fees-type', FeesTypeController::class);

        Route::get('fees/classes', [FeesTypeController::class, 'feesClassListIndex'])->name('fees.class.index');
        // Route::post('fees/classes/update', [FeesTypeController::class, 'updateFeesClass'])->name('fees.class.update');
        Route::get('fees/classes/list', [FeesTypeController::class, 'feesClassList'])->name('fees.class.list');


        Route::post('class/fees-type', [FeesTypeController::class, 'updateFeesClass'])->name('class.fees.type.update');
        Route::delete('class/fees-type/{fees_class_id}', [FeesTypeController::class, 'removeFeesClass'])->name('class.fees.type.delete');

        Route::get('fees/paid', [FeesTypeController::class, 'feesPaidListIndex'])->name('fees.paid.index');
        Route::get('fees/paid/list', [FeesTypeController::class, 'feesPaidList'])->name('fees.paid.list');
        Route::post('fees/paid/store', [FeesTypeController::class, 'feesPaidStore'])->name('fees.paid.store');

        Route::get('fees-config', [FeesTypeController::class, 'feesConfigIndex'])->name('fees.config.index');
        Route::post('fees-config/update', [FeesTypeController::class, 'feesConfigUpdate'])->name('fees.config.udpate');
        Route::put('fees/paid/update/{id}', [FeesTypeController::class, 'feesPaidUpdate'])->name('fees.paid.udpate');
        Route::delete('fees/paid/remove-choiceable-fees/{id}', [FeesTypeController::class, 'feesPaidRemoveChoiceableFees'])->name('fees.paid.remove.choiceable.fees');
        Route::delete('fees/paid/remove-installment-fees/{id}', [FeesTypeController::class, 'feesPaidRemoveInstallmentFees'])->name('fees.paid.remove.installment.fees');
        Route::delete('fees/paid/clear-data/{id}', [FeesTypeController::class, 'clearFeesPaidData'])->name('fees.paid.clear.data');

        Route::post('fees/optional-paid/store', [FeesTypeController::class, 'optionalFeesPaidStore'])->name('fees.optional-paid.store');
        Route::post('fees/compulsory-paid/store', [FeesTypeController::class, 'compulsoryFeesPaidStore'])->name('fees.compulsory-paid.store');

        Route::get('fees/transaction-logs', [FeesTypeController::class, 'feesTransactionsLogsIndex'])->name('fees.transactions.log.index');
        Route::get('fees/transaction-logs/list', [FeesTypeController::class, 'feesTransactionsLogsList'])->name('fees.transactions.log.list');

        Route::get('fees/paid/receipt-pdf/{id}', [FeesTypeController::class, 'feesPaidReceiptPDF'])->name('fees.paid.receipt.pdf');
        Route::get('fees/fees-receipt', function () {
            return view('fees.fees_receipt');
        })->name('fees.receipt');

        // //Pending Fees
        // Route::get('fees/fees-pending',[FeesTypeController::class,'feesPendingIndex'])->name('fees.pending.index');
        // Route::get('fees/fees-pending/list', [FeesTypeController::class, 'feesPendingList'])->name('fees.pending.list');

        // Online Exam
        Route::get('online-exam/terms-conditions',[OnlineExamController::class ,'onlineExamTermsConditionIndex'])->name('online-exam.terms-conditions');
        Route::post('online-exam/store-terms-conditions',[OnlineExamController::class ,'storeOnlineExamTermsCondition'])->name('online-exam.store-terms-conditions');

        Route::resource('online-exam', OnlineExamController::class);
        Route::post('online-exam/add-new-question',[OnlineExamController::class ,'storeExamQuestionChoices'])->name('online-exam.add-new-question');
        Route::get('online-exam/get-class-subject-questions/{online_exam_id}',[OnlineExamController::class ,'getClassSubjectQuestions'])->name('online-exam-question.get-class-subject-questions');
        Route::get('get-subject-online-exam',[OnlineExamController::class ,'getSubjects']);
        Route::get('get-exam-question-index',[OnlineExamController::class ,'examQuestionsIndex'])->name('exam.questions.index');
        Route::post('online-exam/store-questions-choices',[OnlineExamController::class ,'storeQuestionsChoices'])->name('online-exam.store-choice-question');
        Route::delete('online-exam/remove-choiced-question/{id}',[OnlineExamController::class ,'removeQuestionsChoices'])->name('online-exam.remove-choice-question');
        Route::get('online-exam/result/{id}', [OnlineExamController::class, 'onlineExamResultIndex'])->name('online-exam.result.index');
        Route::get('online-exam/result-show/{id}', [OnlineExamController::class, 'showOnlineExamResult'])->name('online-exam.result.show');

        Route::resource('online-exam-question', OnlineExamQuestionController::class);
        Route::delete('online-exam-question/remove-option/{id}', [OnlineExamQuestionController::class , 'removeOptions']);
        Route::delete('online-exam-question/remove-answer/{id}', [OnlineExamQuestionController::class , 'removeAnswers']);
        // End Online Exam Routes

        Route::get('app-settings', [SettingController::class, 'app_index']);
        Route::post('app-settings', [SettingController::class, 'app_update']);
        Route::get('system-update', [SystemUpdateController::class, 'index'])->name('system-update.index');
        Route::post('system-update', [SystemUpdateController::class, 'update'])->name('system-update.update');


        Route::resource('stream',StreamController::class);

        Route::resource('shifts',ShiftController::class);
        Route::get('get-class-teacher/{class_section_id}',[ClassTeacherController::class,'getClassTeacherlist']);
        Route::get('get-all-class-teacher/{class_section_id}',[ClassTeacherController::class,'getNotClassTeacherList']);
        Route::get('update-warning-modal',[HomeController::class, 'updateWarningModal'])->name('update-warning-modal');
        Route::get('attendance/create_bulk',[AttendanceController::class,'createBulkData'])->name('attendance.create-bulk-data');

        Route::post('form-fields/change-rank', [FormFieldController::class,'changeRank']);
        Route::resource('form-fields', FormFieldController::class);

        Route::resource('notifications', NotificationController::class);

        Route::delete('multiple-event/{id}', [EventController::class, 'deleteMultipleEvent'])->name('multiple.event.delete');
        Route::resource('events', EventController::class);

        Route::get('content',[WebSettingController::class,'content_index'])->name('content.index');
        Route::get('content-list',[WebSettingController::class,'content_show'])->name('content.show');
        Route::post('content/{id}',[WebSettingController::class,'content_update'])->name('content.edit');
        Route::get('educational-program',[WebSettingController::class,'educational_index'])->name('educational.index');
        Route::post('educational-program/store',[WebSettingController::class,'educational_store'])->name('educational.store');
        Route::get('educational-program-list',[WebSettingController::class,'educational_show'])->name('educational.show');
        Route::post('educational-program/update',[WebSettingController::class,'educational_update'])->name('educational.update');
        Route::delete('educational-program/delete{id}',[WebSettingController::class,'educational_delete'])->name('educational.delete');

        Route::get('faq',[WebSettingController::class,'faq_index'])->name('faq.index');
        Route::post('faq/store',[WebSettingController::class,'faq_store'])->name('faq.store');
        Route::get('faq-list',[WebSettingController::class,'faq_show'])->name('faq.show');
        Route::post('faq/update/{id}',[WebSettingController::class,'faq_update'])->name('faq.update');
        Route::delete('faq/delete/{id}',[WebSettingController::class,'faq_delete'])->name('faq.delete');

        Route::get('contact-us/web',[WebSettingController::class,'contact_us_index'])->name('contact_us.index');
        Route::get('contact-us-list',[WebSettingController::class,'contact_us_show'])->name('contact_us.show');
        Route::post('contact-us/reply/{id}',[WebSettingController::class,'reply'])->name('contact_us.reply');
        Route::delete('contact-us/delete/{id}',[WebSettingController::class,'contact_us_delete'])->name('contact_us.delete');


        Route::get('photos',[MediaController::class,'photo_index'])->name('photo.index');
        Route::post('photos/store',[MediaController::class,'photo_store'])->name('photos.store');
        Route::get('photos-list',[MediaController::class,'photo_show'])->name('photos.show');
        Route::get('photos/edit/{id}',[MediaController::class,'edit_index'])->name('edit.index');
        Route::post('photos/update',[MediaController::class,'photo_update'])->name('photo.update');
        Route::delete('photos/delete/{id}',[MediaController::class,'photo_delete'])->name('photo.delete');
        Route::post('image/update',[MediaController::class,'image_update'])->name('image.update');
        Route::delete('image/delete/{id}',[MediaController::class,'image_delete'])->name('image.delete');

        Route::get('/videos-index',[MediaController::class,'video_index'])->name('video.index');
        Route::post('videos/store',[MediaController::class,'video_store'])->name('video.store');
        Route::get('videos-list',[MediaController::class,'video_show'])->name('video.show');
        Route::post('videos/update/{id}',[MediaController::class,'video_update'])->name('video.update');
        Route::delete('videos/delete/{id}',[MediaController::class,'video_delete'])->name('video.delete');

        Route::resource('staff', StaffController::class);
    });
});

// webhooks
Route::post('webhook/razorpay', [WebhookController::class, 'razorpay']);
Route::post('webhook/stripe', [WebhookController::class, 'stripe']);
Route::post('webhook/paystack',[WebhookController::class,'paystack']);

Route::get('/privacy-policy', function () {
    $settings = getSettings('privacy_policy');
    echo $settings['privacy_policy'];
});

Route::get('/terms-conditions', function(){
    $settings = getSettings('terms_condition');
    echo $settings['terms_condition'];
});

Route::get('clear', function () {
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
});

Route::get('storage-link', function () {
    try {
        Artisan::call('storage:link');
        echo "storage link created";
    } catch (Exception $e) {
        echo "Storage Link already exists";
    }
});


Route::get('migrate', function () {
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('migrate');
});
// Route::get('rollback', function () {
//     Artisan::call('view:clear');
//     Artisan::call('route:clear');
//     Artisan::call('config:clear');
//     Artisan::call('cache:clear');
//     Artisan::call('migrate:rollback');
// });
Route::get('seeder_install', function () {
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('db:seed --class=InstallationSeeder');
});
// Route::get('test', function () {
//     //            return "working";
//     \Artisan::call('db:seed --class=ProductTableSeeder');
//     \Artisan::call('config:clear');
//     \Artisan::call('view:clear');
//     //            echo "Done";
//     //    if (Storage::disk('public')->exists("http://127.0.0.1:8000/storage/lessons/lQNZSKVwsZS5XkC3Jjag4DS4s7ykymH07GzFFa3K.txt")) {
//     //        echo "file exists";
//     //        Storage::disk('public')->delete("http://127.0.0.1:8000/storage/lessons/lQNZSKVwsZS5XkC3Jjag4DS4s7ykymH07GzFFa3K.txt");
//     //    }else{
//     //        echo "file does not exists";
//     //    }
// });


// Route::get('/storage-link', function(){
//     $target = storage_path('app/public');
//     $link = public_path('/storage');
//     symlink($target, $link);
//     echo "symbolic link created successfully";
// });

// Route::get('/command', function()
// {
//     $exitCode = Artisan::call('db:seed', ['--class' => 'DummyDataSeeder']);
//     echo "Command executed successfully.";
// });

// Route::get('/', function () {
//     return view('auth.login');
// });
