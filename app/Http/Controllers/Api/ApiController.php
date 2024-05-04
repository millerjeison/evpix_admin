<?php

namespace App\Http\Controllers\Api;


use App\Models\Event;
use App\Models\Slider;
use App\Models\Holiday;
use App\Models\SessionYear;
use Illuminate\Http\Request;
use App\Models\MultipleEvent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $user->fcm_id = '';
            $user->save();
            $user->currentAccessToken()->delete();
            $response = array(
                'error' => false,
                'message' => 'Logout Successfully done.',
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

    public function getHolidays(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'assignment_id' => 'nullable|numeric',
        //     'subject_id' => 'nullable|numeric',
        // ]);

        // if ($validator->fails()) {
        //     $response = array(
        //         'error' => true,
        //         'message' => $validator->errors()->first(),
        //     );
        //     return response()->json($response);
        // }
        // $student = $request->user()->student;

        try {
            $data = Holiday::get();
            $response = array(
                'error' => false,
                'message' => "Holidays Fetched Successfully",
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

    public function getSliders(Request $request)
    {
        try {
            $data = Slider::where('type',1)->orWhere('type',3)->get();
            $response = array(
                'error' => false,
                'message' => "Sliders Fetched Successfully",
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

    public function getSessionYear(Request $request)
    {
        try {
            $session_year = getSettings('session_year');
            $session_year_id = $session_year['session_year'];

            $data = SessionYear::find($session_year_id);
            $response = array(
                'error' => false,
                'message' => "Session Year Fetched Successfully",
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

    public function getSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:privacy_policy,contact_us,about_us,terms_condition,app_settings,fees_settings',
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
            if ($request->type == "app_settings") {
                $session_year = $settings['session_year'] ?? "";
                $calender = !empty($session_year) ? SessionYear::find($session_year) : null;

                $data['app_link'] = $settings['app_link'] ?? "";
                $data['ios_app_link'] = $settings['ios_app_link'] ?? "";
                $data['app_version'] = $settings['app_version'] ?? "";
                $data['ios_app_version'] = $settings['ios_app_version'] ?? "";
                $data['force_app_update'] = $settings['force_app_update'] ?? "";
                $data['app_maintenance'] = $settings['app_maintenance'] ?? "";
                $data['session_year'] = $calender;
                $data['school_name'] = $settings['school_name'] ?? "";
                $data['school_tagline'] = $settings['school_tagline'] ?? "";
                $data['teacher_app_link'] = $settings['teacher_app_link'] ?? "";
                $data['teacher_ios_app_link'] = $settings['teacher_ios_app_link'] ?? "";
                $data['teacher_app_version'] = $settings['teacher_app_version'] ?? "";
                $data['teacher_ios_app_version'] = $settings['teacher_ios_app_version'] ?? "";
                $data['teacher_force_app_update'] = $settings['teacher_force_app_update'] ?? "";
                $data['teacher_app_maintenance'] = $settings['teacher_app_maintenance'] ?? "";
                $data['online_payment'] = $settings['online_payment'] ?? "1";
                $data['is_demo'] = env('DEMO_MODE');

                if (isset($settings['razorpay_status']) && $settings['razorpay_status']) {
                    if (isset($settings['fees_due_date'])) {
                        $date = date('Y-m-d', strtotime($settings['fees_due_date']));
                    }
                    $data['fees_settings'] = array(
                        'razorpay_status' => $settings['razorpay_status'] ?? "",
                        // 'razorpay_secret_key' => $settings['razorpay_secret_key'] ?? "",
                        'razorpay_api_key' => $settings['razorpay_api_key'] ?? "",
                        'razorpay_webhook_secret' => $settings['razorpay_webhook_secret'] ?? "",
                        'razorpay_api_key' => $settings['razorpay_api_key'] ?? "",
                        'currency_code' => $settings['currency_code'] ?? "",
                        'currency_symbol' =>  $settings['currency_symbol'] ?? "",
                    );
                }

                if (isset($settings['stripe_status']) && $settings['stripe_status']) {
                    if (isset($settings['fees_due_date'])) {
                        $date = date('Y-m-d', strtotime($settings['fees_due_date']));
                    }
                    $data['fees_settings'] = array(
                        'stripe_status' => $settings['stripe_status'] ?? "",
                        'stripe_publishable_key' => $settings['stripe_publishable_key'] ?? "",
                        // 'stripe_secret_key' => $settings['stripe_secret_key'] ?? "",
                        // 'stripe_webhook_secret' => $settings['stripe_webhook_secret'] ?? "",
                        'currency_code' => $settings['currency_code'] ?? "",
                        'currency_symbol' =>  $settings['currency_symbol'] ?? "",
                        'fees_due_date' => $date ?? "",
                        'fees_due_charges' => $settings['fees_due_charges'] ?? "",
                    );
                }

                if (isset($settings['paystack_status']) && $settings['paystack_status']) {
                    if (isset($settings['fees_due_date'])) {
                        $date = date('Y-m-d', strtotime($settings['fees_due_date']));
                    }
                    $data['fees_settings'] = array(
                        'paystack_status' => $settings['paystack_status'] ?? "",
                        'paystack_public_key' => $settings['paystack_public_key'] ?? "",
                        'currency_code' => $settings['currency_code'] ?? "",
                        'currency_symbol' =>  $settings['currency_symbol'] ?? "",
                        'fees_due_date' => $date ?? "",
                        'fees_due_charges' => $settings['fees_due_charges'] ?? "",
                    );
                }

                if(isset($settings['online_exam_terms_condition']) && !empty($settings['online_exam_terms_condition'])){
                    $data['online_exam_terms_condition'] = htmlspecialchars_decode($settings['online_exam_terms_condition']);
                }else{
                    $data['online_exam_terms_condition'] = "";
                }
            } else {
                $data = $settings[$request->type] ?? "";
            }
            $response = array(
                'error' => false,
                'message' => "Data Fetched Successfully",
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

    protected function forgotPassword(Request $request)
    {
        $input = $request->only('email');
        $validator = Validator::make($input, [
            'email' => "required|email"
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
            $response = Password::sendResetLink($input);
            if ($response == Password::RESET_LINK_SENT) {
                $response = array(
                    'error' => false,
                    'message' => "Forgot Password email send successfully",
                    'code' => 200,
                );
            } else {
                $response = array(
                    'error' => true,
                    'message' => "Cannot send Reset Password Link.Try again later.",
                    'code' => 108,
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

    protected function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|between:8,12',
            'new_confirm_password' => 'same:new_password',
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
            if (Hash::check($request->current_password, $user->password)) {
                $user->update(['password' => Hash::make($request->new_password)]);
                $response = array(
                    'error' => false,
                    'message' => "Password Changed successfully.",
                    'code' => 200,
                );
            } else {
                $response = array(
                    'error' => true,
                    'message' => "Invalid Password",
                    'code' => 109,
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
    public function getEvents()
    {
        try {
            $events = Event::all();

            foreach ($events as $event) {
                if($event->type == 'multiple')
                {
                    $hasdaySchedule = MultipleEvent::where('event_id',$event->id)->first();
                    if( $hasdaySchedule)
                    {
                        $data[] = [
                            'id' => $event->id,
                            'has_day_schedule' => 1,
                            'title' => $event->title,
                            'type' => $event->type,
                            'start_date' => $event->start_date,
                            'end_date' => $event->end_date,
                            'start_time' => $event->start_time,
                            'end_time' => $event->end_time,
                            'image' => $event->image,
                            'description' => $event->description,
                        ];
                    }else{
                        $data[] = [
                            'id' => $event->id,
                            'has_day_schedule' => 0,
                            'title' => $event->title,
                            'type' => $event->type,
                            'start_date' => $event->start_date,
                            'end_date' => $event->end_date,
                            'start_time' => $event->start_time,
                            'end_time' => $event->end_time,
                            'image' => $event->image,
                            'description' => $event->description,
                        ];
                    }

                }else{
                    $data[] = [
                        'id' => $event->id,
                        'has_day_schedule' => 0,
                        'title' => $event->title,
                        'type' => $event->type,
                        'start_date' => $event->start_date,
                        'end_date' => $event->end_date,
                        'start_time' => $event->start_time,
                        'end_time' => $event->end_time,
                        'image' => $event->image,
                        'description' => $event->description,
                    ];
                }

            }
            $response = array(
                'error' => false,
                'message' => "Events Fetched Successfully",
                'data' => isset($data) ? $data : [],
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

    public function getEventsDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|nullable',
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

            $data = MultipleEvent::where('event_id',$request->event_id)->get();

            $response = array(
                'error' => false,
                'message' => "Events Details Fetched Successfully",
                'data' => $data,
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
}
