@extends('layouts.master')

@section('title')
    {{ __('photos') }}
@endsection
@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('photos') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('edit') . ' ' . __('photos') }}
                        </h4>
                        <div class="col-12">
                            <form class="pt-3 edit-photo" id="edit-photo" action="{{route('photo.update')}}" method="POST" novalidate="novalidate" enctype="multipart/form-data">
                                <div class="row">
                                    <input type="hidden" name="edit_id" id="edit_id" value="{{$media->id}}"/>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('name') }} <span class="text-danger">*</span></label>
                                            {!! Form::text('name', $media->name ?? null, ['required', 'placeholder' => __('name'), 'class' => 'form-control', 'id' => 'name']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('thumbnail') }} <span class="text-danger">*</span></label>
                                            <input type="file" name="thumbnail" class="file-upload-default edit_image" accept="image/*"/>
                                            <div class="input-group col-xs-12">
                                                <input type="text" class="form-control file-upload-info" placeholder="{{ __('thumbnail') }}"/>
                                                <span class="input-group-append">
                                                    <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                                </span>
                                            </div>
                                            <div class="mt-3 w-150">
                                                <img src="{{$media->thumbnail}}" id="edit_image" height="50px" width="50px">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input class="btn btn-theme" id="create-btn" type="submit" value={{ __('submit') }}>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('photos') }}
                        </h4>
                        <form class="pt-3 edit-image" id="edit-image" action="{{route('image.update')}}" method="POST" novalidate="novalidate" enctype="multipart/form-data">
                            <input type="hidden" name="edit_id" id="edit_id" value="{{$media->id}}"/>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group text-right mr-5">
                                        <input type="file" name="image[]" class="file-upload-default" multiple accept="image/*"/>
                                        <button type="button" class="file-upload-browse btn btn-inverse-success">
                                            <i class="fa fa-plus"></i>&nbsp;&nbsp;{{ __('upload') }}</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    @foreach ($media->files as $image)
                                    <div class="image-container">
                                        <img src="{{$image->file_url}}" class="edit-image" height="150px" width="150px" />
                                        <div class="overlay-img">
                                            <a href="{{route('image.delete', $image->id)}}" class="image-delete" style="text-decoration: none"><div class="cross-icon"><i class="fa fa-times icon"></i></div></a>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <input class="btn btn-theme mt-4" id="create-btn" type="submit" value={{ __('submit') }}>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script type="text/javascript">
    $(".hover").mouseleave(
    function () {
      $(this).removeClass("hover");
    }
  );
</script>
@endsection
