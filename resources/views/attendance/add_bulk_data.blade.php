@extends('layouts.master')

@section('title')
{{ __('add_bulk_data') }}
@endsection

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            {{ __('manage') . ' ' . __('students') }}
        </h3>
    </div>
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <form class="pt-3 student-export" enctype="multipart/form-data" action="{{route('student-export')}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-4">
                                <label>{{ __('class') . ' ' . __('section') }} <span class="text-danger">*</span></label>
                                <select name="class_section_id" id="class_section" class="form-control select2">
                                    <option value="">{{ __('select') . ' ' . __('class') . ' ' . __('section') }}
                                    </option>
                                    @foreach ($class_sections as $section)
                                        <option value="{{ $section->id }}" data-class="{{ $section->class->id }}">
                                            {{ $section->class->name }} - {{ $section->section->name }} {{ $section->class->medium->name }} {{$section->class->streams->name ?? ''}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-sm-12 col-md-4">
                                <label>{{ __('date') }} <span class="text-danger">*</span></label>
                                {!! Form::text('date', null, ['required', 'placeholder' => __('date'), 'class' => 'form-control daterange', 'id' => 'date','data-date-end-date'=>"0d"]) !!}
                                <span class="input-group-addon input-group-append"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-3 mt-5">
                                <input class="btn btn-theme" type="submit" value="Download Dummy File" name="submit" id="student_export">
                            </div>
                            <div class="col-sm-12 col-xs-12">
                                <span style="font-size: 14px"> <b>{{__('Note')}} :- </b>
                                    <br>{{__('first_download_dummy_file_and_add_attendance_then_upload_it')}}
                                    <br>{{__('add_attendance_on_type_column_0_for_(absent)_and_1_for_(present)')}}</span>
                            </div>
                        </div>
                    </form>
                    <br>
                    <hr>
                    <br>
                    <form class="pt-3 student-import" id="create-form-bulk-data" enctype="multipart/form-data" action="{{ route('attendance.store-bulk-data') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-4">
                                <label>{{ __('class') . ' ' . __('section') }} <span class="text-danger">*</span></label>
                                <select name="class_section_id" id="class_section" class="form-control select2">
                                    <option value="">{{ __('select') . ' ' . __('class') . ' ' . __('section') }}
                                    </option>
                                    @foreach ($class_sections as $section)
                                        <option value="{{ $section->id }}" data-class="{{ $section->class->id }}">
                                            {{ $section->class->name }} - {{ $section->section->name }} {{ $section->class->medium->name }} {{$section->class->streams->name ?? ''}}</option>
                                    @endforeach
                                </select>

                            </div>
                            <div class="form-group col-sm-12 col-md-4">
                                <label>{{ __('file_upload') }} <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="file-upload-default" />
                                <div class="input-group col-xs-12">
                                    <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ __('file_upload') }}" required="required" />
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 col-xs-12">
                                <input class="btn btn-theme submit_bulk_file" type="submit" value="Submit" name="submit" id="submit_bulk_file">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
