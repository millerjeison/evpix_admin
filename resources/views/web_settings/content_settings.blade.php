@extends('layouts.master')

@section('title')
    {{ __('section') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('section') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h3>
                            {{$about->tag}}
                        </h3>
                        <hr>
                        <br>
                        <form class="pt-3 edit-content" method="POST" id="edit-content-about" action="{{route('content.edit',1)}}" novalidate="novalidate">
                            <input type="hidden" name="id" id="id" value="{{$about->id}}"/>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('section_title') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('tag',  isset($about->tag) ? $about->tag : '' , ['required', 'placeholder' => __('tag'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('heading') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('heading', isset($about->heading) ? $about->heading : '', ['required', 'placeholder' => __('heading'), 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-4 ">
                                    <label>{{ __('content') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('content', isset($about->content)? $about->content : '', ['rows' => '10','placeholder' => __('content'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-4">
                                    <label>{{ __('image') }} <span class="text-danger">*</span></label>
                                    <input type="file" id="image" name="image" class="file-upload-default edit_image" accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control edit_image" value="" placeholder="{{__('image')}}" />
                                        <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                    <br>
                                    <br>
                                    <div class="w-100 text-center">
                                        <img src="{{isset($about->image) ? $about->image : url('assets/images/dummyImg.png')}}" id="content_image" class="w-25">
                                    </div>
                                </div>
                                <div class="form-group col-sm-6 col-md-4">
                                    <label>{{ __('status') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '1', (isset($about->status) ? $about->status == '1' :''),[]) !!}{{ __('enable') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '0', (isset($about->status) ? $about->status == '0' :''),[]) !!}{{ __('disable') }}
                                            </label>
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
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h3>
                            {{$whoweare->tag}}
                        </h3>
                        <hr>
                        <br>
                        <form class="pt-3 edit-content"  method="POST" id="edit-content-whoweare" action="{{route('content.edit',1)}}" novalidate="novalidate">
                            <input type="hidden" name="id" id="id" value="{{$whoweare->id}}"/>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('section_title') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('tag',  isset($whoweare->tag) ? $whoweare->tag : '' , ['required', 'placeholder' => __('tag'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('heading') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('heading', isset($whoweare->heading) ? $whoweare->heading : '', ['required', 'placeholder' => __('heading'), 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-4 ">
                                    <label>{{ __('content') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('content', isset($whoweare->content)? $whoweare->content : '', ['rows' => '10','placeholder' => __('content'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-4">
                                    <label>{{ __('image') }} <span class="text-danger">*</span></label>
                                    <input type="file" id="image" name="image" class="file-upload-default edit_image" accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control edit_image" value="" placeholder="{{__('image')}}" />
                                        <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                    <br>
                                    <br>
                                    <div class="w-100 text-center">
                                        <img src="{{isset($whoweare->image) ? $whoweare->image : url('assets/images/dummyImg.png')}}" id="content_image" class="w-25">
                                    </div>
                                </div>
                                <div class="form-group col-sm-6 col-md-4">
                                    <label>{{ __('status') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '1', (isset($whoweare->status) ? $whoweare->status == '1' :''),[]) !!}{{ __('enable') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '0', (isset($whoweare->status) ? $whoweare->status == '0' :''),[]) !!}{{ __('disable') }}
                                            </label>
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
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h3>
                            {{$teacher->tag}}
                        </h3>
                        <hr>
                        <br>
                        <form class="pt-3 edit-content" id="edit-content-teacher" action="{{route('content.edit',1)}}" novalidate="novalidate">
                            <input type="hidden" name="id" id="id" value="{{$teacher->id}}"/>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('section_title') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('tag',  isset($teacher->tag) ? $teacher->tag : '' , ['required', 'placeholder' => __('tag'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('heading') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('heading', isset($teacher->heading) ? $teacher->heading : '', ['required', 'placeholder' => __('heading'), 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6 ">
                                    <label>{{ __('content') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('content', isset($teacher->content)? $teacher->content : '', ['rows' => '5','placeholder' => __('content'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('status') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '1', (isset($teacher->status) ? $teacher->status == '1' :''),[]) !!}{{ __('enable') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '0', (isset($teacher->status) ? $teacher->status == '0' :''),[]) !!}{{ __('disable') }}
                                            </label>
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
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h3>
                            {{$program->tag}}
                        </h3>
                        <hr>
                        <br>
                        <form class="pt-3 edit-content" id="edit-content-program" action="{{route('content.edit',1)}}" novalidate="novalidate">
                            <input type="hidden" name="id" id="id" value="{{$program->id}}"/>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('section_title') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('tag',  isset($program->tag) ? $program->tag : '' , ['required', 'placeholder' => __('tag'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('heading') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('heading', isset($program->heading) ? $program->heading : '', ['required', 'placeholder' => __('heading'), 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6 ">
                                    <label>{{ __('content') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('content', isset($program->content)? $program->content : '', ['rows' => '5','placeholder' => __('content'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('status') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '1', (isset($program->status) ? $program->status == '1' :''),[]) !!}{{ __('enable') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '0', (isset($program->status) ? $program->status == '0' :''),[]) !!}{{ __('disable') }}
                                            </label>
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
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h3>
                            {{$event->tag}}
                        </h3>
                        <hr>
                        <br>
                        <form class="pt-3 edit-content" id="edit-content-event" action="{{route('content.edit',1)}}" novalidate="novalidate">
                            <input type="hidden" name="id" id="id" value="{{$event->id}}"/>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('section_title') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('tag',  isset($event->tag) ? $event->tag : '' , ['required', 'placeholder' => __('tag'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('heading') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('heading', isset($event->heading) ? $event->heading : '', ['required', 'placeholder' => __('heading'), 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6 ">
                                    <label>{{ __('content') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('content', isset($event->content)? $event->content : '', ['rows' => '5','placeholder' => __('content'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('status') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '1', (isset($event->status) ? $event->status == '1' :''),[]) !!}{{ __('enable') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '0', (isset($event->status) ? $event->status == '0' :''),[]) !!}{{ __('disable') }}
                                            </label>
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
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h3>
                            {{$photo->tag}}
                        </h3>
                        <hr>
                        <br>
                        <form class="pt-3 edit-content" id="edit-content-photo" action="{{route('content.edit',1)}}" novalidate="novalidate">
                            <input type="hidden" name="id" id="id" value="{{$photo->id}}"/>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('section_title') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('tag',  isset($photo->tag) ? $photo->tag : '' , ['required', 'placeholder' => __('tag'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('heading') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('heading', isset($photo->heading) ? $photo->heading : '', ['required', 'placeholder' => __('heading'), 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6 ">
                                    <label>{{ __('content') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('content', isset($photo->content)? $photo->content : '', ['rows' => '5','placeholder' => __('content'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('status') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '1', (isset($photo->status) ? $photo->status == '1' :''),[]) !!}{{ __('enable') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '0', (isset($photo->status) ? $photo->status == '0' :''),[]) !!}{{ __('disable') }}
                                            </label>
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
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h3>
                            {{$video->tag}}
                        </h3>
                        <hr>
                        <br>
                        <form class="pt-3 edit-content" id="edit-content-video" action="{{route('content.edit',1)}}" novalidate="novalidate">
                            <input type="hidden" name="id" id="id" value="{{$video->id}}"/>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('section_title') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('tag',  isset($video->tag) ? $video->tag : '' , ['required', 'placeholder' => __('tag'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('heading') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('heading', isset($video->heading) ? $video->heading : '', ['required', 'placeholder' => __('heading'), 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6 ">
                                    <label>{{ __('content') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('content', isset($video->content)? $video->content : '', ['rows' => '5','placeholder' => __('content'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('status') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '1', (isset($video->status) ? $video->status == '1' :''),[]) !!}{{ __('enable') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '0', (isset($video->status) ? $video->status == '0' :''),[]) !!}{{ __('disable') }}
                                            </label>
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
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h3>
                            {{$faq->tag}}
                        </h3>
                        <hr>
                        <br>
                        <form class="pt-3 edit-content" id="edit-content-faq" action="{{route('content.edit',1)}}" novalidate="novalidate">
                            <input type="hidden" name="id" id="id" value="{{$faq->id}}"/>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('section_title') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('tag',  isset($faq->tag) ? $faq->tag : '' , ['required', 'placeholder' => __('tag'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('heading') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('heading', isset($faq->heading) ? $faq->heading : '', ['required', 'placeholder' => __('heading'), 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6 ">
                                    <label>{{ __('content') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('content', isset($faq->content)? $faq->content : '', ['rows' => '5','placeholder' => __('content'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('status') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '1', (isset($faq->status) ? $faq->status == '1' :''),[]) !!}{{ __('enable') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '0', (isset($faq->status) ? $faq->status == '0' :''),[]) !!}{{ __('disable') }}
                                            </label>
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
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h3>
                            {{$app->tag}}
                        </h3>
                        <hr>
                        <br>
                        <form class="pt-3 edit-content" id="edit-content-app" action="{{route('content.edit',1)}}" novalidate="novalidate">
                            <input type="hidden" name="id" id="id" value="{{$app->id}}"/>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('section_title') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('tag',  isset($app->tag) ? $app->tag : '' , ['required', 'placeholder' => __('tag'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('heading') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('heading', isset($app->heading) ? $app->heading : '', ['required', 'placeholder' => __('heading'), 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6 ">
                                    <label>{{ __('content') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('content', isset($app->content) ? $app->content : '', ['rows' => '5','placeholder' => __('content'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('status') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '1', (isset($app->status) ? $app->status == '1' :''),[]) !!}{{ __('enable') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '0', (isset($app->status) ? $app->status == '0' :''),[]) !!}{{ __('disable') }}
                                            </label>
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
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h3>
                            {{isset($question->tag) ? $question->tag : '' }}
                        </h3>
                        <hr>
                        <br>
                        <form class="pt-3 edit-content" id="edit-content-question" action="{{route('content.edit',1)}}" novalidate="novalidate">
                            <input type="hidden" name="id" id="id" value="{{isset($question->id) ? $question->id : '' }}"/>
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-5">
                                    <label>{{ __('section_title') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('tag',  isset($question->tag) ? $question->tag : '' , ['required', 'placeholder' => __('tag'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-5">
                                    <label>{{ __('heading') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('heading', isset($question->heading) ? $question->heading : '', ['required', 'placeholder' => __('heading'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-6 col-md-2">
                                    <label>{{ __('status') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '1', (isset($question->status) ? $question->status == '1' :''),[]) !!}{{ __('enable') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('status', '0', (isset($question->status) ? $question->status == '0' :''),[]) !!}{{ __('disable') }}
                                            </label>
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
    </div>
@endsection
