<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Faq;
use App\Models\Media;
use App\Models\ContactUs;
use App\Models\MediaFile;
use App\Models\WebSetting;
use Illuminate\Http\Request;
use App\Models\EducationalProgram;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class WebSettingController extends Controller
{
    public function content_index()
    {
        if (!Auth::user()->can('content-create')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $about = WebSetting::where('name','about_us')->first();
        $whoweare = WebSetting::where('name','who_we_are')->first();
        $teacher = WebSetting::where('name','teacher')->first();
        $event = WebSetting::where('name', 'events')->first();
        $program = WebSetting::where('name', 'programs')->first();
        $photo = WebSetting::where('name','photos')->first();
        $video = WebSetting::where('name','videos')->first();
        $faq = WebSetting::where('name','faqs')->first();
        $app = WebSetting::where('name','app')->first();
        $question = WebSetting::where('name','question')->first();

        return view('web_settings.content_settings',compact('about','whoweare','teacher','event','program','photo','video','faq','app','question'));
    }

    // public function content_show()
    // {
    //     if (!Auth::user()->can('content-list')) {
    //         $response = array(
    //             'message' => trans('no_permission_message')
    //         );
    //         return response()->json($response);
    //     }
    //     $offset = 0;
    //     $limit = 10;
    //     $sort = 'id';
    //     $order = 'ASC';

    //     if (isset($_GET['offset']))
    //         $offset = $_GET['offset'];
    //     if (isset($_GET['limit']))
    //         $limit = $_GET['limit'];

    //     if (isset($_GET['sort']))
    //         $sort = $_GET['sort'];
    //     if (isset($_GET['order']))
    //         $order = $_GET['order'];

    //     $sql = WebSetting::where('id','!=',0);

    //     if (isset($_GET['search']) && !empty($_GET['search'])) {
    //         $search = $_GET['search'];
    //         $sql->where('id', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%");
    //     }
    //     $total = $sql->count();

    //     $sql->orderBy($sort, $order)->skip($offset)->take($limit);

    //     $res = $sql->get();

    //     $bulkData = array();
    //     $bulkData['total'] = $total;
    //     $rows = array();
    //     $tempRow = array();
    //     $no = 1;
    //     foreach ($res as $row) {
    //         $operate = '<a href=' . route('content.edit', $row->id) . ' class="btn btn-xs btn-gradient-primary btn-rounded btn-icon edit-data" data-id=' . $row->id . ' title="Edit" data-toggle="modal" data-target="#editModal"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;';

    //         $tempRow['id'] = $row->id;
    //         $tempRow['no'] = $no++;
    //         $tempRow['name'] = $row->name;
    //         $tempRow['tag'] = $row->tag;
    //         $tempRow['heading'] = $row->heading;
    //         $tempRow['content'] = $row->content;
    //         $tempRow['image'] = $row->image;
    //         $tempRow['status'] = $row->status;
    //         $tempRow['created_at'] = $row->created_at;
    //         $tempRow['updated_at'] = $row->updated_at;
    //         $tempRow['operate'] = $operate;
    //         $rows[] = $tempRow;
    //     }
    //     $bulkData['rows'] = $rows;
    //     return response()->json($bulkData);
    // }

    public function content_update(Request $request,$id)
    {
        if (!Auth::user()->can('content-edit')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $validator = Validator::make($request->all(), [
            'tag' => 'required',
            'heading' => 'required',
            //'content' => 'required'
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $websetting = WebSetting::find($request->id);
            $websetting->tag = $request->tag;
            $websetting->heading = $request->heading;
            $websetting->content = $request->content;
            $websetting->status =   $request->status;
            if ($request->hasFile('image')) {
                if($websetting->getRawOriginal('image')){
                    if (Storage::disk('public')->exists($websetting->getRawOriginal('image'))) {
                        Storage::disk('public')->delete($websetting->getRawOriginal('image'));
                    }
                }

                $image = $request->file('image');

                $file_name = time() . '-' . $image->getClientOriginalName();

                $file_path = 'websettings/content/' . $file_name;

                resizeImage($image);

                $destinationPath = storage_path('app/public/websettings/content');
                $image->move($destinationPath, $file_name);

                $websetting->image = $file_path;
            }
            $websetting->save();

            $response = array(
                'error' => false,
                'message' => trans('data_update_successfully'),
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

    public function educational_index()
    {
        if (!Auth::user()->can('program-list')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        return view('web_settings.educational_program');
    }

    public function educational_store(Request $request)
    {
        if (!Auth::user()->can('program-create')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'image' => 'required|image|mimes:png,jpg,svg,jpeg'
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $program = new EducationalProgram();
            $program->title = $request->title;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $file_name = time() . '-' . $image->getClientOriginalName();
                $file_path = 'websettings/educational/' . $file_name;
                resizeImage($image);
                $destinationPath = storage_path('app/public/websettings/educational');
                $image->move($destinationPath, $file_name);
                $program->image = $file_path;
            }

            $program->save();

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

    public function educational_show()
    {
        if (!Auth::user()->can('program-list')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return response()->json($response);
        }
        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'ASC';

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            $sort = $_GET['sort'];
        if (isset($_GET['order']))
            $order = $_GET['order'];

        $sql = EducationalProgram::where('id','!=',0);

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('title', 'LIKE', "%$search%");
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
            $operate = '<a href=' . route('educational.update', $row->id) . ' class="btn btn-xs btn-gradient-primary btn-rounded btn-icon edit-data" data-id=' . $row->id . ' title="Edit" data-toggle="modal" data-target="#editModal"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;';
            $operate .= '<a href=' . route('educational.delete', $row->id) . ' class="btn btn-xs btn-gradient-danger btn-rounded btn-icon delete-form" data-id=' . $row->id . '><i class="fa fa-trash"></i></a>';

            $tempRow['id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['title'] = $row->title;
            $tempRow['image'] = $row->image ?? '-';
            $tempRow['created_at'] = $row->created_at;
            $tempRow['updated_at'] = $row->updated_at;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function educational_update(Request $request)
    {
        if (!Auth::user()->can('program-edit')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'image' => 'required|image|mimes:png,jpg,svg,jpeg'
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $program = EducationalProgram::find($request->edit_id);
            $program->title = $request->title;
            if ($request->hasFile('image')) {
                if (Storage::disk('public')->exists($program->getRawOriginal('image'))) {
                    Storage::disk('public')->delete($program->getRawOriginal('image'));
                }
                $image = $request->file('image');
                $file_name = time() . '-' . $image->getClientOriginalName();
                $file_path = 'websettings/educational/' . $file_name;
                resizeImage($image);
                $destinationPath = storage_path('app/public/websettings/educational');
                $image->move($destinationPath, $file_name);
                $program->image = $file_path;
            }

            $program->save();

            $response = array(
                'error' => false,
                'message' => trans('data_update_successfully'),
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

    public function educational_delete($id)
    {
        if (!Auth::user()->can('program-delete')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        try {
            $program= EducationalProgram::find($id);
            if (Storage::disk('public')->exists($program->getRawOriginal('image'))) {
                Storage::disk('public')->delete($program->getRawOriginal('image'));
            }
            $program->delete();
            $response = array(
                'error' => false,
                'message' => trans('data_delete_successfully')
            );
        } catch (\Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred')
            );
        }
        return response()->json($response);
    }

    public function faq_index()
    {
        if (!Auth::user()->can('faq-list')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        return view('web_settings.faq');
    }

    public function faq_store(Request $request)
    {
        if (!Auth::user()->can('faq-create')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }

        $validator = Validator::make($request->all(), [
           'question' => 'required|string',
           'answer' => 'required|string',
           'status' => 'integer|required'
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $faq = new Faq();
            $faq->question = $request->question;
            $faq->answer = $request->answer;
            $faq->status = $request->status;
            $faq->save();

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

    public function faq_show()
    {
        if (!Auth::user()->can('faq-list')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return response()->json($response);
        }
        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'ASC';

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            $sort = $_GET['sort'];
        if (isset($_GET['order']))
            $order = $_GET['order'];

        $sql = Faq::where('id','!=',0);

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%");
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
            $operate = '<a href=' . route('faq.update', $row->id) . ' class="btn btn-xs btn-gradient-primary btn-rounded btn-icon edit-data" data-id=' . $row->id . ' title="Edit" data-toggle="modal" data-target="#editModal"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;';
            $operate .= '<a href=' . route('faq.delete', $row->id) . ' class="btn btn-xs btn-gradient-danger btn-rounded btn-icon delete-form" data-id=' . $row->id . '><i class="fa fa-trash"></i></a>';

            $tempRow['id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['question'] = $row->question;
            $tempRow['answer'] = $row->answer;
            $tempRow['status'] = $row->status;
            $tempRow['created_at'] = $row->created_at;
            $tempRow['updated_at'] = $row->updated_at;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function faq_update(Request $request)
    {
        if (!Auth::user()->can('faq-edit')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }

        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'answer' => 'required|string',
            'status' => 'integer|required'
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $faq = Faq::find($request->edit_id);
            $faq->question = $request->question;
            $faq->answer = $request->answer;
            $faq->status = $request->status;
            $faq->save();

            $response = array(
                'error' => false,
                'message' => trans('data_update_successfully'),
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

    public function faq_delete($id)
    {
        if (!Auth::user()->can('faq-delete')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        try {
            $faq = Faq::find($id);
            $faq->delete();
            $response = array(
                'error' => false,
                'message' => trans('data_delete_successfully')
            );
        } catch (\Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred')
            );
        }
        return response()->json($response);
    }

    public function contact_us_index()
    {
        if (!Auth::user()->can('contact-us')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $school_email = getSettings('school_email');
        $email = $school_email['school_email'];
        return view('web_settings.contact_us');
    }

    public function contact_us_show()
    {
        if (!Auth::user()->can('contact-us')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'ASC';

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            $sort = $_GET['sort'];
        if (isset($_GET['order']))
            $order = $_GET['order'];

        $data = getSettings('date_formate');
        $sql = ContactUs::where('id','!=',0);

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%");
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
            $operate = '<a href=' . route('contact_us.reply', $row->id) . ' class="btn btn-xs btn-gradient-primary btn-rounded btn-icon edit-data" data-id=' . $row->id . ' title="Reply" data-toggle="modal" data-target="#editModal"><i class="fa fa-reply"></i></a>&nbsp;&nbsp;';
            $operate .= '<a href=' . route('contact_us.delete', $row->id) . ' class="btn btn-xs btn-gradient-danger btn-rounded btn-icon delete-form" data-id=' . $row->id . '  title="Delete"><i class="fa fa-trash"></i></a>';

            $tempRow['id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['first_name'] = $row->first_name;
            $tempRow['last_name'] = $row->last_name;
            $tempRow['email'] = $row->email;
            $tempRow['phone'] = $row->phone;
            $tempRow['message'] = $row->message;
            $tempRow['date'] = date($data['date_formate'], strtotime($row->date));
            $tempRow['created_at'] = $row->created_at;
            $tempRow['updated_at'] = $row->updated_at;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function reply(Request $request)
    {
        if (!Auth::user()->can('contact-us')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        try {
            $admin_mail = env('MAIL_FROM_ADDRESS');
            $school_name = env('APP_NAME');

            $attachments = [];

            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $file) {
                    $attachments[] = [
                        'path' => $file->getRealPath(),
                        'name' => $file->getClientOriginalName(),
                        'mime' => $file->getMimeType()
                    ];
                }
            }

            $data = [
                'subject' => 'Reply to Your Query',
                'email' => $request->email,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'query' => $request->message,
                'reply_message' => $request->reply_message,
                'school_name' => $school_name,
                'attachments' => $attachments
            ];


            Mail::send('web_settings.testmail', $data, function ($message) use ($data, $admin_mail, $school_name) {
                $message->to($data['email'])->subject($data['subject']);
                $message->from($admin_mail, $school_name);

                if ($data['attachments']) {
                    foreach ($data['attachments'] as $attachment) {
                        $message->attach($attachment['path'], [
                            'as' => $attachment['name'],
                            'mime' => $attachment['mime'],
                        ]);
                    }
                }
            });

            $response = array(
                'error' => false,
                'message' => trans('email_sent_successfully'),
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

    public function contact_us_delete($id)
    {
        if (!Auth::user()->can('contact-us')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        try {
            $data = ContactUs::find($id);
            $data->delete();
            $response = array(
                'error' => false,
                'message' => trans('data_delete_successfully')
            );
        } catch (\Throwable $e) {
            $response = array(
                'error' => true,
                'message' => trans('error_occurred')
            );
        }
        return response()->json($response);
    }
}
