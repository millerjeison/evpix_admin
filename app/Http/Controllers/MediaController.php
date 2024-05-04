<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\MediaFile;
use App\Rules\YouTubeUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{
    public function photo_index()
    {
        if (!Auth::user()->can('media-create')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        return view('web_settings.photos');
    }

    public function video_index()
    {
        if (!Auth::user()->can('media-create')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        return view('web_settings.videos');
    }

    public function photo_store(Request $request)
    {
        if (!Auth::user()->can('media-create')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'thumbnail' => 'required|image|mimes:png,jpg,jpeg',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $media = new Media();
            $media->name = $request->name;
            $media->type = 1;

            if ($request->hasFile('thumbnail')) {
                $image = $request->file('thumbnail');
                $file_name = $image->getClientOriginalName();
                $file_path = 'gallery/'. $file_name;
                resizeImage($image);
                $destinationPath = storage_path('app/public/gallery');
                $image->move($destinationPath, $file_name);
                $media->thumbnail = $file_path;
            }
            $media->save();

            if ($request->hasFile('image')) {
                $images = $request->file('image');

                foreach($images as $image) {

                    $file = new MediaFile();
                    $file->media_id = $media->id;
                    $file_name = $image->getClientOriginalName();
                    $file_path = 'gallery/'. $file_name;
                    resizeImage($image);
                    $destinationPath = storage_path("app/public/gallery/");
                    $image->move($destinationPath, $file_name);
                    $file->file_url = $file_path;
                    $file->save();
                }
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

    public function photo_show()
    {
        if (!Auth::user()->can('media-list')) {
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

        $sql = Media::with('files')->where('type', 1);

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
            $operate = '<a href=' . route('edit.index', $row->id) . ' class="btn btn-xs btn-gradient-primary btn-rounded btn-icon edit-data" data-id=' . $row->id .'><i class="fa fa-edit"></i></a>&nbsp;&nbsp;';
            $operate .= '<a href=' . route('photo.delete', $row->id) . ' class="btn btn-xs btn-gradient-danger btn-rounded btn-icon delete-form" data-id=' . $row->id . '><i class="fa fa-trash"></i></a>';

            $tempRow['id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['name'] = $row->name;
            $tempRow['thumbnail'] = $row->thumbnail;
            $tempRow['created_at'] = $row->created_at;
            $tempRow['updated_at'] = $row->updated_at;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function edit_index($id)
    {
        if (!Auth::user()->can('media-create')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }

        $media = Media::with('files')->where('id',$id)->first();
        return view('web_settings.edit_photos',compact('media'));
    }

    public function photo_update(Request $request)
    {
        if (!Auth::user()->can('media-edit')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'thumbnail' => 'nullable|image|mimes:png,jpg,jpeg',
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $media = Media::find($request->edit_id);
            $media->name = $request->name;

            if ($request->hasFile('thumbnail')) {

                if (Storage::disk('public')->exists($media->getRawOriginal('image'))) {
                    Storage::disk('public')->delete($media->getRawOriginal('image'));
                }

                $image = $request->file('thumbnail');
                $file_name = $image->getClientOriginalName();
                $file_path = 'gallery/'. $file_name;
                resizeImage($image);
                $destinationPath = storage_path('app/public/gallery');
                $image->move($destinationPath, $file_name);
                $media->thumbnail = $file_path;
            }
            $media->save();
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

    public function photo_delete($id)
    {
        if (!Auth::user()->can( 'media-delete')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        try {
            $media = Media::find($id);
            $files  = MediaFile::where('media_id',$id)->get();
            foreach ($files as $file) {
                if (Storage::disk('public')->exists($file->getRawOriginal('file_url'))) {
                    Storage::disk('public')->delete($file->getRawOriginal('file_url'));
                }
                $file->delete();
            }
            if (Storage::disk('public')->exists($media->getRawOriginal('thumbnail'))) {
                Storage::disk('public')->delete($media->getRawOriginal('thumbnail'));
            }
            $media->delete();
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

    public function image_update(Request $request)
    {
        if (!Auth::user()->can('media-edit')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }

        $validator = Validator::make($request->all(), [
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $media = new MediaFile();

            if ($request->hasFile('image'))
            {
                $images = $request->file('image');

                foreach($images as $image) {

                    $file = new MediaFile();
                    $file->media_id = $request->edit_id;
                    $file_name = $image->getClientOriginalName();
                    $file_path = 'gallery/'. $file_name;
                    resizeImage($image);
                    $destinationPath = storage_path("app/public/gallery/");
                    $image->move($destinationPath, $file_name);
                    $file->file_url = $file_path;
                    $file->save();
                }
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

    public function image_delete($id)
    {
        if (!Auth::user()->can('media-delete')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        try {
            $file = MediaFile::find($id);
            if (Storage::disk('public')->exists($file->getRawOriginal('file_url'))) {
                Storage::disk('public')->delete($file->getRawOriginal('file_url'));
            }
            $file->delete();
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

    public function video_store(Request $request)
    {
        if (!Auth::user()->can('media-create')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $validator = Validator::make($request->all(), [
            'youtube_url' => ['required',new YouTubeUrl],
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $media = new Media();
            $media->youtube_url = $request->youtube_url;
            $media->type = 2;
            $media->save();
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

    public function video_show()
    {
        if (!Auth::user()->can('media-list')) {
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

        $sql = Media::where('type',2);

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
            $operate = '<a href=' . route('video.update', $row->id) . ' class="btn btn-xs btn-gradient-primary btn-rounded btn-icon edit-data" data-id=' . $row->id . ' title="Edit" data-toggle="modal" data-target="#editModal"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;';
            $operate .= '<a href=' . route('video.delete', $row->id) . ' class="btn btn-xs btn-gradient-danger btn-rounded btn-icon delete-form" data-id=' . $row->id . '><i class="fa fa-trash"></i></a>';

            $tempRow['id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['url'] = "<a data-toggle='lightbox' href='". $row->embeded_url['embedUrl'] ."'>$row->youtube_url</a>";
            $tempRow['original_url'] = $row->youtube_url;
            $tempRow['created_at'] = $row->created_at;
            $tempRow['updated_at'] = $row->updated_at;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;

        return response()->json($bulkData);

    }

    public function video_update(Request $request)
    {
        if (!Auth::user()->can('media-edit')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        $validator = Validator::make($request->all(), [
            'youtube_url' => ['required',new YouTubeUrl],
        ]);

        if ($validator->fails()) {
            $response = array(
                'error' => true,
                'message' => $validator->errors()->first()
            );
            return response()->json($response);
        }
        try {
            $media = Media::find($request->edit_id);
            $media->youtube_url = $request->youtube_url;
            $media->type = 2;
            $media->save();
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

    public function video_delete($id)
    {
        if (!Auth::user()->can('media-delete')) {
            $response = array(
                'message' => trans('no_permission_message')
            );
            return redirect(route('home'))->withErrors($response);
        }
        try {
            $media = Media::find($id);
            $media->delete();
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
