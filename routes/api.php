<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\ParentApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\TeacherApiController;
use App\Http\Controllers\Api\SystemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/link-storage', [SystemController::class, 'linkStorage']);
Route::get('/key-generate', [SystemController::class, 'keyGenerate']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('logout', [ApiController::class, 'logout']);
});

/**   hoolid
 * STUDENT APIs Hola
 **/
Route::group(['prefix' => 'student'], function () {

    //Non Authenticated APIs
    Route::post('login', [StudentApiController::class, 'login']);
    Route::post('forgot-password', [StudentApiController::class, 'forgotPassword']);

    //Authenticated APIs
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('dashboard',[StudentApiController::class,'dashboard']);
        Route::get('subjects', [StudentApiController::class, 'subjects']);
        Route::get('class-subjects', [StudentApiController::class, 'classSubjects']);
        Route::post('select-subjects', [StudentApiController::class, 'selectSubjects']);
        Route::get('parent-details', [StudentApiController::class, 'getParentDetails']);
        Route::get('timetable', [StudentApiController::class, 'getTimetable']);
        Route::get('lessons', [StudentApiController::class, 'getLessons']);
        Route::get('lesson-topics', [StudentApiController::class, 'getLessonTopics']);
        Route::get('assignments', [StudentApiController::class, 'getAssignments']);
        Route::post('submit-assignment', [StudentApiController::class, 'submitAssignment']);
        Route::post('edit-assignment', [StudentApiController::class, 'editAssignmentSubmission']);
        Route::post('delete-assignment-submission', [StudentApiController::class, 'deleteAssignmentSubmission']);
        Route::get('attendance', [StudentApiController::class, 'getAttendance']);
        Route::get('announcements', [StudentApiController::class, 'getAnnouncements']);
        Route::get('get-exam-list', [StudentApiController::class, 'getExamList']); // Exam list Route
        Route::get('get-exam-details', [StudentApiController::class, 'getExamDetails']); // Exam Details Route
        Route::get('exam-marks', [StudentApiController::class, 'getExamMarks']); // Exam Details Route

        // online exam routes
        Route::get('get-online-exam-list', [StudentApiController::class, 'getOnlineExamList']); // Get Online Exam List Route
        Route::get('get-online-exam-questions', [StudentApiController::class, 'getOnlineExamQuestions']); // Get Online Exam Questions Route
        Route::post('submit-online-exam-answers', [StudentApiController::class, 'submitOnlineExamAnswers']); // Submit Online Exam Answers Details Route
        Route::get('get-online-exam-result-list', [StudentApiController::class, 'getOnlineExamResultList']); // Online exam result list Route
        Route::get('get-online-exam-result', [StudentApiController::class, 'getOnlineExamResult']); // Online exam result  Route

        //reports
        Route::get('get-online-exam-report', [StudentApiController::class, 'getOnlineExamReport']); // Online Exam Report Route
        Route::get('get-assignments-report', [StudentApiController::class, 'getAssignmentReport']); // Assignment Report Route

        // profile data
        Route::get('get-profile-data', [StudentApiController::class, 'getProfileDetails']); // Get Profile Data
        Route::get('get-notification',[StudentApiController::class, 'getNotifications']); // Get Notification Data

        Route::get('get-user-list',[StudentApiController::class,'getChatUserList']);
        Route::post('send-message',[StudentApiController::class,'sendMessage']);
        Route::post('get-user-message',[StudentApiController::class,'getUserChatMessage']);
        Route::post('read-all-message',[StudentApiController::class, 'readAllMessages']);
    });
});

/**
 * PARENT APIs
 **/
Route::group(['prefix' => 'parent'], function () {
    //Non Authenticated APIs
    Route::post('login', [ParentApiController::class, 'login']);
    //Authenticated APIs
    Route::group(['middleware' => ['auth:sanctum',]], function () {

        //APIS Without Child ID
        Route::get('announcements', [ParentApiController::class, 'getAnnouncements']); //Get Announcementes
        Route::get('fees-paid-receipt-pdf', [ParentApiController::class, 'feesPaidReceiptPDF']); //Fees Receipt
        Route::get('fees-transactions-list', [ParentApiController::class, 'getFeesPaymentTransactions']); //Fees Payment Transaction Details
        Route::get('get-profile-data', [ParentApiController::class, 'getProfileDetails']); // Get Profile Data
        Route::post('fail-payment-transaction', [ParentApiController::class, 'failPaymentTransactionStatus']); // Make Payment Transaction Fail API
        Route::get('get-notification',[ParentApiController::class, 'getNotifications']); // Get Notification Data
        Route::post('get-payment-status',[ParentApiController::class,'getPaymentStatus']); // Get Payment Status
        Route::get('get-user-list',[ParentApiController::class,'getChatUserList']);
        Route::post('send-message',[ParentApiController::class,'sendMessage']);
        Route::post('get-user-message',[ParentApiController::class,'getUserChatMessage']);
        Route::post('read-all-message',[ParentApiController::class, 'readAllMessages']);

        Route::group(['middleware' => ['auth:sanctum', 'checkChild']], function () {

            Route::get('subjects', [ParentApiController::class, 'subjects']);
            Route::get('class-subjects', [ParentApiController::class, 'classSubjects']);
            Route::get('timetable', [ParentApiController::class, 'getTimetable']);
            Route::get('lessons', [ParentApiController::class, 'getLessons']);
            Route::get('lesson-topics', [ParentApiController::class, 'getLessonTopics']);
            Route::get('assignments', [ParentApiController::class, 'getAssignments']);
            Route::get('attendance', [ParentApiController::class, 'getAttendance']);
            // Route::get('announcements', [ParentApiController::class, 'getAnnouncements']);
            Route::get('teachers', [ParentApiController::class, 'getTeachers']);
            Route::get('get-exam-list', [ParentApiController::class, 'getExamList']); // Exam list Route
            Route::get('get-exam-details', [ParentApiController::class, 'getExamDetails']); // Exam Details Route
            Route::get('exam-marks', [ParentApiController::class, 'getExamMarks']); //Exam Marks

            //fees
            Route::get('fees-details', [ParentApiController::class, 'getFeesDetails']); //Fees Details
            Route::post('add-fees-transaction', [ParentApiController::class, 'storeFeesTransaction']); //Fees Details
            Route::post('store-fees', [ParentApiController::class, 'storeFees']); //Store Fees
            Route::get('fees-paid-list', [ParentApiController::class, 'feesPaidList']); //Fees Details

            // online exam routes
            Route::get('get-online-exam-list', [ParentApiController::class, 'getOnlineExamList']); // Get Online Exam List Route
            Route::get('get-online-exam-result-list', [ParentApiController::class, 'getOnlineExamResultList']); // Online exam result list Route
            Route::get('get-online-exam-result', [ParentApiController::class, 'getOnlineExamResult']); // Online exam result  Route

            //reports
            Route::get('get-online-exam-report', [ParentApiController::class, 'getOnlineExamReport']); // Online Exam Report Route
            Route::get('get-assignments-report', [ParentApiController::class, 'getAssignmentReport']); // Assignment Report Route

        });
    });
});

/**
 * TEACHER APIs
 **/
Route::group(['prefix' => 'teacher'], function () {
    //Non Authenticated APIs
    Route::post('login', [TeacherApiController::class, 'login']);
    //Authenticated APIs
    Route::group(['middleware' => ['auth:sanctum',]], function () {
        Route::get('classes', [TeacherApiController::class, 'classes']);

        Route::get('subjects', [TeacherApiController::class, 'subjects']);

        //Assignment
        Route::get('get-assignment', [TeacherApiController::class, 'getAssignment']);
        Route::post('create-assignment', [TeacherApiController::class, 'createAssignment']);
        Route::post('update-assignment', [TeacherApiController::class, 'updateAssignment']);
        Route::post('delete-assignment', [TeacherApiController::class, 'deleteAssignment']);

        //Assignment Submission
        Route::get('get-assignment-submission', [TeacherApiController::class, 'getAssignmentSubmission']);
        Route::post('update-assignment-submission', [TeacherApiController::class, 'updateAssignmentSubmission']);

        //File
        Route::post('delete-file', [TeacherApiController::class, 'deleteFile']);
        Route::post('update-file', [TeacherApiController::class, 'updateFile']);

        //Lesson
        Route::get('get-lesson', [TeacherApiController::class, 'getLesson']);
        Route::post('create-lesson', [TeacherApiController::class, 'createLesson']);
        Route::post('update-lesson', [TeacherApiController::class, 'updateLesson']);
        Route::post('delete-lesson', [TeacherApiController::class, 'deleteLesson']);

        //Topic
        Route::get('get-topic', [TeacherApiController::class, 'getTopic']);
        Route::post('create-topic', [TeacherApiController::class, 'createTopic']);
        Route::post('update-topic', [TeacherApiController::class, 'updateTopic']);
        Route::post('delete-topic', [TeacherApiController::class, 'deleteTopic']);

        //Announcement
        Route::get('get-announcement', [TeacherApiController::class, 'getAnnouncement']);
        Route::post('send-announcement', [TeacherApiController::class, 'sendAnnouncement']);
        Route::post('update-announcement', [TeacherApiController::class, 'updateAnnouncement']);
        Route::post('delete-announcement', [TeacherApiController::class, 'deleteAnnouncement']);

        Route::get('get-attendance', [TeacherApiController::class, 'getAttendance']);
        Route::post('submit-attendance', [TeacherApiController::class, 'submitAttendance']);


        //Exam
        Route::get('get-exam-list', [TeacherApiController::class, 'getExamList']); // Exam list Route
        Route::get('get-exam-details', [TeacherApiController::class, 'getExamDetails']); // Exam Details Route
        Route::post('submit-exam-marks/subject', [TeacherApiController::class, 'submitExamMarksBySubjects']); // Submit Exam Marks By Subjects Route
        Route::post('submit-exam-marks/student', [TeacherApiController::class, 'submitExamMarksByStudent']); // Submit Exam Marks By Students Route

        Route::group(['middleware' => ['auth:sanctum', 'checkStudent']], function () {
            Route::get('get-student-result', [TeacherApiController::class, 'GetStudentExamResult']); // Student Exam Result
            Route::get('get-student-marks', [TeacherApiController::class, 'GetStudentExamMarks']); // Student Exam Marks
        });

        //Student List
        Route::get('student-list', [TeacherApiController::class, 'getStudentList']);
        Route::get('student-details', [TeacherApiController::class, 'getStudentDetails']);

        //Schedule List
        Route::get('teacher_timetable', [TeacherApiController::class, 'getTeacherTimetable']);

        //Profile Detials
        Route::get('get-profile-details', [TeacherApiController::class, 'getProfileDetails']);
        Route::get('get-notification',[TeacherApiController::class, 'getNotifications']); // Get Notification Data

        Route::get('get-user-list',[TeacherApiController::class,'getChatUserList']);
        Route::post('send-message',[TeacherApiController::class,'sendMessage']);
        Route::post('get-user-message',[TeacherApiController::class,'getUserChatMessage']);
        Route::post('read-all-message',[TeacherApiController::class, 'readAllMessages']);
    });
});

/**
 * GENERAL APIs
 **/
Route::get('holidays', [ApiController::class, 'getHolidays']);
Route::get('sliders', [ApiController::class, 'getSliders']);
Route::get('current-session-year', [ApiController::class, 'getSessionYear']);
Route::get('settings', [ApiController::class, 'getSettings']);
Route::post('forgot-password', [ApiController::class, 'forgotPassword']);
Route::get('get-events-list',[ApiController::class,'getEvents']);
Route::get('get-events-details',[ApiController::class,'getEventsDetails']);
Route::group(['middleware' => ['auth:sanctum',]], function () {
    Route::post('change-password', [ApiController::class, 'changePassword']);
});
