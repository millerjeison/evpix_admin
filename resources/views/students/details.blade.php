@extends('layouts.master')

@section('title')
    {{ __('students') }}
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
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('students') }}
                        </h4>
                        <div id="toolbar">
                            <div class="row">
                                <div class="col">
                                    <select name="filter_class_section_id" id="filter_class_section_id"
                                        class="form-control">
                                        <option value="">{{ __('select_class_section') }}</option>
                                        @foreach ($class_section as $class)
                                            <option value={{ $class->id }}>
                                                {{ $class->class->name . ' ' . $class->section->name . ' ' . $class->class->medium->name . ' '. ($class->class->streams->name ?? ' ') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-12">
                                <table aria-describedby="mydesc" class='table table-responsive' id='table_list'
                                    data-toggle="table" data-url="{{ url('students-list') }}" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200 , 500]" data-search="true"
                                    data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                                    data-fixed-columns="true" data-fixed-number="2" data-fixed-right-number="1"
                                    data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                                    data-sort-order="desc" data-maintain-selected="true" data-export-types='["txt","excel"]'
                                    data-export-options='{ "fileName": "students-list-<?= date('d-m-y') ?>" ,"ignoreColumn":
                                    ["operate"]}' data-query-params="studentDetailsqueryParams"
                                    data-check-on-init="true">

                                    <thead>
                                        <tr>
                                            <th scope="col" data-field="id" data-sortable="true" data-visible="false">
                                                {{ __('id') }}</th>
                                            <th scope="col" data-field="no" data-sortable="false">{{ __('no.') }}</th>
                                            <th scope="col" data-field="user_id" data-sortable="false"
                                                data-visible="false">{{ __('user_id') }}</th>
                                            <th scope="col" data-field="admission_no" data-sortable="false">
                                                {{ __('gr_number') }}</th>
                                            <th scope="col" data-field="roll_number" data-sortable="false">
                                                {{ __('roll_no') }}</th>

                                            <th scope="col" data-field="first_name" data-sortable="false">
                                                {{ __('first_name') }}</th>
                                            <th scope="col" data-field="last_name" data-sortable="false">
                                                {{ __('last_name') }}</th>
                                            <th scope="col" data-field="dob" data-sortable="false">{{ __('dob') }}
                                            </th>
                                            <th scope="col" data-field="gender" data-sortable="false">{{ __('gender') }}
                                            </th>
                                            <th scope="col" data-field="blood_group" data-sortable="false" data-visible="false">
                                                {{ __('blood_group') }}</th>
                                            <th scope="col" data-field="image" data-sortable="false"
                                                data-formatter="imageFormatter">{{ __('image') }}
                                            </th>
                                            <th scope="col" data-field="class_section_id" data-sortable="false"
                                                data-visible="false">
                                                {{ __('class') . ' ' . __('section') . ' ' . __('id') }}</th>
                                            <th scope="col" data-field="class_section_name" data-sortable="false">
                                                {{ __('class') . ' ' . __('section') }}</th>
                                            <th scope="col" data-field="stream_name" data-sortable="false" data-visible="false">
                                                {{ __('stream') }}</th>
                                            <th scope="col" data-field="category_id" data-sortable="false"
                                                data-visible="false">{{ __('category') . ' ' . __('id') }}</th>
                                            <th scope="col" data-field="category_name" data-sortable="false" data-visible="false">
                                                {{ __('category') }}</th>
                                            <th scope="col" data-field="caste" data-sortable="false" data-visible="false">{{ __('caste') }}
                                            </th>
                                            <th scope="col" data-field="religion" data-sortable="false" data-visible="false">
                                                {{ __('religion') }}</th>
                                            <th scope="col" data-field="admission_date" data-sortable="false">
                                                {{ __('admission_date') }}</th>
                                            <th scope="col" data-field="height" data-sortable="false" data-visible="false">
                                                {{ __('height') }}</th>
                                            <th scope="col" data-field="weight" data-sortable="false" data-visible="false">
                                                {{ __('weight') }}</th>

                                            <th scope="col" data-field="father_first_name" data-sortable="false">
                                                {{ __('father') . ' ' . __('name') }}</th>
                                            <th scope="col" data-field="father_mobile" data-sortable="false">
                                                {{ __('father') . ' ' . __('mobile') }}</th>
                                            <th scope="col" data-field="father_occupation" data-sortable="false" data-visible="false">
                                                {{ __('father') . ' ' . __('occupation') }}</th>
                                            <th scope="col" data-field="father_image" data-sortable="false"
                                                data-formatter="fatherImageFormatter">
                                                {{ __('father') . ' ' . __('image') }}</th>

                                            <th scope="col" data-field="mother_first_name" data-sortable="false">
                                                {{ __('mother') . ' ' . __('name') }}</th>
                                            <th scope="col" data-field="mother_occupation" data-sortable="false" data-visible="false">
                                                {{ __('parents') . ' ' . __('occupation') }}</th>
                                            <th scope="col" data-field="mother_image" data-sortable="false"
                                                data-formatter="motherImageFormatter">
                                                {{ __('mother') . ' ' . __('image') }}</th>

                                            <th scope="col" data-field="guardian_first_name" data-sortable="false">
                                                {{ __('guardian') . ' ' . __('name') }}</th>
                                            <th scope="col" data-field="guardian_mobile" data-sortable="false">
                                                {{ __('guardian') . ' ' . __('mobile') }}</th>
                                            <th scope="col" data-field="guardian_occupation" data-sortable="false" data-visible="false">
                                                {{ __('guardian') . ' ' . __('occupation') }}</th>
                                            <th scope="col" data-field="guardian_image" data-sortable="false"
                                                data-formatter="guardianImageFormatter">
                                                {{ __('guardian') . ' ' . __('image') }}</th>

                                            <th scope="col" data-field="is_new_admission" data-sortable="false"
                                                data-visible="false">{{ __('is_new_admission') }}</th>
                                            @canany(['student-edit', 'student-delete'])
                                                <th data-events="studentEvents" data-width="150" scope="col" data-field="operate"
                                                    data-sortable="false">{{ __('action') }}</th>
                                            @endcanany
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('student-edit')
        <div class="modal fade" id="editModal" data-backdrop="static" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel">{{ __('edit') . ' ' . __('students') }}</h4><br>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="fa fa-close"></i></span>
                        </button>
                    </div>
                    <form id="edit-form" class="edit-student-registration-form" novalidate="novalidate"
                        action="{{ url('students') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="edit_id" id="edit_id">

                            <div class="row">
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('first_name') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('first_name', null, ['placeholder' => __('first_name'), 'class' => 'form-control', 'id' => 'edit_first_name']) !!}

                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('last_name') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('last_name', null, ['placeholder' => __('last_name'), 'class' => 'form-control', 'id' => 'edit_last_name']) !!}

                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('mobile') }}</label>
                                    {!! Form::tel('mobile', null, ['placeholder' => __('mobile'), 'class' => 'form-control', 'id' => 'edit_mobile' , 'min' => 1]) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-12">
                                    <label>{{ __('gender') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" name="gender" value="male" id="edit_male">
                                                {{ __('male') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" name="gender" value="female" id="edit_female">
                                                {{ __('female') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('image') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="image" class="file-upload-default" accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled=""
                                            placeholder="{{ __('image') }}" required="required" id="edit_image" />
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme"
                                                type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                    <div style="width: 100px;">
                                        <img src="" id="edit-student-image-tag" class="img-fluid w-100" />
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('dob') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('dob', null, ['placeholder' => __('dob'), 'class' => 'datepicker-popup-no-future form-control', 'id' => 'edit_dob']) !!}
                                    <span class="input-group-addon input-group-append">
                                    </span>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('class') . ' ' . __('section') }} <span
                                            class="text-danger">*</span></label>
                                    <select required name="class_section_id" class="form-control" id="edit_class_section_id">
                                        <option value="">{{ __('select') . ' ' . __('class') . ' ' . __('section') }}
                                        </option>
                                        @foreach ($class_section as $section)
                                            <option value="{{ $section->id }}">{{ $section->class->name }} -
                                                {{ $section->section->name }} {{ $section->class->medium->name }} {{$section->class->streams->name ?? ' '}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-4">
                                    <label> {{ __('category') }} <span class="text-danger">*</span></label>
                                    <select name="category_id" class="form-control" id="edit_category_id">
                                        <option value="">{{ __('select') . ' ' . __('category') }}</option>
                                        @foreach ($category as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('gr_number') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('admission_no', null, ['placeholder' => __('admission_no'), 'class' => 'form-control', 'id' => 'edit_admission_no' ,'readonly'=>true]) !!}

                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('roll_no') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('roll_number', null, ['placeholder' => __('roll_no'), 'class' => 'form-control', 'id' => 'edit_roll_number']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('caste') }}</label>
                                    {!! Form::text('caste', null, ['placeholder' => __('caste'), 'class' => 'form-control', 'id' => 'edit_caste']) !!}

                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('religion') }}</label>
                                    {!! Form::text('religion', null, ['placeholder' => __('religion'), 'class' => 'form-control', 'id' => 'edit_religion']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('admission_date') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('admission_date', null, ['placeholder' => __('admission_date'), 'class' => 'datepicker-popup-no-future form-control', 'id' => 'edit_admission_date']) !!}
                                    <span class="input-group-addon input-group-append">
                                    </span>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('blood_group') }} <span class="text-danger">*</span></label>
                                    <select name="blood_group" class="form-control" id="edit_blood_group">
                                        <option value="">{{ __('select') . ' ' . __('blood_group') }}</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('height') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('height', null, ['placeholder' => __('height'), 'class' => 'form-control', 'id' => 'edit_height']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('weight') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('weight', null, ['placeholder' => __('weight'), 'class' => 'form-control', 'id' => 'edit_weight']) !!}
                                </div>
                                <div class="form-group col-12">
                                    <label>{{ __('address') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('current_address', null, ['placeholder' => __('current_address'), 'class' => 'form-control', 'id' => 'current_address', 'id' => 'edit_current_address', 'rows' => 2]) !!}
                                </div>
                                <div class="form-group col-12">
                                    <label>{{ __('permanent_address') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('permanent_address', null, ['placeholder' => __('permanent_address'), 'class' => 'form-control', 'id' => 'permanent_address', 'id' => 'edit_permanent_address', 'rows' => 2]) !!}
                                </div>
                            </div>
                            <div class="row">
                                @foreach ($formFields as $row)

                                        @if($row->type==="text" || $row->type==="number")
                                            <div class="form-group col-sm-12 col-md-4">
                                                <label>{{ ucwords(str_replace('_', ' ', $row->name)) }} {!! ($row->is_required) ? ' <span class="text-danger">*</span></label>': '' !!}</label></label>
                                                <input type="{{$row->type}}" name="{{$row->name}}" id="{{ $row->name }}" placeholder="{{ ucwords(str_replace('_', ' ', $row->name)) }}" class="form-control edit_text_number" {{($row->is_required===1)?"required":''}}>
                                            </div>
                                        @endif

                                        @if($row->type==="dropdown")
                                            <div class="form-group col-sm-12 col-md-4">
                                                <label>{{ ucwords(str_replace('_', ' ', $row->name)) }} {!! ($row->is_required) ? ' <span class="text-danger">*</span></label>': '' !!}</label></label>
                                                <select name="{{ $row->name }}" class="form-control edit_dropdown" id="{{ $row->name }}" {{($row->is_required===1)?"required":''}}>
                                                    <option value="">Please Select</option>
                                                    @foreach(json_decode($row->default_values) as $options)
                                                        @if($options != null)
                                                            <option value="{{$options}}">{{ucfirst($options)}}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        @if($row->type==="radio")
                                            <div class="form-group col-sm-12 col-md-4">
                                                <label>{{ ucwords(str_replace('_', ' ', $row->name)) }} {!! ($row->is_required) ? ' <span class="text-danger">*</span></label>': '' !!}</label></label>
                                                <br>
                                                <div class="d-flex">
                                                    @foreach(json_decode($row->default_values) as $options)
                                                        @if($options != null)
                                                            <div class="form-check form-check-inline">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="edit_radio" id="{{$options}}" name="{{$row->name}}" value="{{$options}}" {{($row->is_required===1)?"required":''}}>
                                                                    {{ ucfirst($options) }}
                                                                </label>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        @if($row->type==="checkbox")
                                        <div class="form-group col-sm-12 col-md-4">
                                            <label>{{ ucwords(str_replace('_', ' ', $row->name)) }} {!! ($row->is_required) ? ' <span class="text-danger">*</span></label>': '' !!}</label>
                                            <br>
                                            <div class="col-md-10" id="{{$row->name}}">
                                                @foreach(json_decode($row->default_values) as $options)
                                                    @if($options != null)
                                                        <div class="checkbox form-check form-check-inline">
                                                            <label class="form-check-label">
                                                                <input type="checkbox"  class="edit_checkbox" id="checkbox_{{ $options }}" name="{{ 'checkbox[' . $row->name . '][' . $options . ']' }}" value="{{$options}}"> {{ ucfirst(str_replace('_', ' ', $options)) }}
                                                            </label>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>

                                        </div>
                                    @endif
                                        @if($row->type==="textarea")
                                            <div class="form-group col-sm-12 col-md-4">
                                                <label>{{ ucwords(str_replace('_', ' ', $row->name)) }} {!! ($row->is_required) ? ' <span class="text-danger">*</span></label>': '' !!}</label></label>
                                                <textarea name="{{$row->name}}" id="{{$row->name}}" cols="10" rows="3" placeholder="{{ ucwords(str_replace('_', ' ', $row->name)) }}" class="form-control edit_textarea" {{($row->is_required===1)?"required":''}}></textarea>
                                            </div>
                                        @endif

                                        @if($row->type==="file")
                                            <div class="form-group col-sm-12 col-md-4">
                                                <label>{{ ucwords(str_replace('_', ' ', $row->name)) }} {!! ($row->is_required) ? ' <span class="text-danger">*</span></label>': '' !!}</label>
                                                    <input type="file" name="{{$row->name}}" class="file-upload-default">
                                                    <div class="input-group col-xs-12">
                                                        <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ ucwords(str_replace('_', ' ', $row->name)) }}"/>
                                                        <span class="input-group-append">
                                                            <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                                        </span>
                                                    </div>
                                                    <div id="{{ $row->name }}-div" style="display: none">
                                                        <a href="" id="{{ $row->name }}" target="_blank" rel="noopener noreferrer"></a>
                                                        <input type="hidden" id="{{ $row->name }}-name" name="{{ $row->name }}"/>
                                                    </div>
                                            </div>
                                        @endif

                                @endforeach
                            </div>
                            <div class="form-group col-sm-12 col-md-12">
                                <div class="d-flex">
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="checkbox" name="parent" value="Parent" class="form-check-input" id="show-edit-parents-details">{{ __('parents_details') }}
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="checkbox" name="guardian" value="Guardian" class="form-check-input" id="show-edit-guardian-details">{{ __('guardian_details') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="edit_parents_div" style="display:none;">
                                <div class="form-group col-sm-12 col-md-12">
                                    <label>{{ __('father_email') }} <span class="text-danger">*</span></label>
                                    <select class="edit-father-search w-100" id="edit_father_email"
                                        name="father_email"></select>
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('father') . ' ' . __('first_name') }} <span
                                            class="text-danger">*</span></label>
                                    {!! Form::text('father_first_name', null, ['placeholder' => __('father') . ' ' . __('first_name'), 'class' => 'form-control', 'id' => 'edit_father_first_name', 'readonly' => true]) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('father') . ' ' . __('last_name') }} <span
                                            class="text-danger">*</span></label>
                                    {!! Form::text('father_last_name', null, ['placeholder' => __('father') . ' ' . __('last_name'), 'class' => 'form-control', 'id' => 'edit_father_last_name', 'readonly' => true]) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('father') . ' ' . __('mobile') }} <span
                                            class="text-danger">*</span></label>
                                    {!! Form::text('father_mobile', null, ['placeholder' => __('father') . ' ' . __('mobile'), 'class' => 'form-control', 'id' => 'edit_father_mobile', 'readonly' => true , 'min' => 1]) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('father') . ' ' . __('dob') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('father_dob', null, ['placeholder' => __('father') . ' ' . __('dob'), 'class' => 'form-control datepicker-popup-no-future form-control', 'id' => 'edit_father_dob', 'readonly' => true]) !!}
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('father') . ' ' . __('occupation') }} <span
                                            class="text-danger">*</span></label>
                                    {!! Form::text('father_occupation', null, ['placeholder' => __('father') . ' ' . __('occupation'), 'class' => 'form-control', 'id' => 'edit_father_occupation', 'readonly' => true]) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('father') . ' ' . __('image') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="father_image" class="father_image file-upload-default" accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled=""
                                            placeholder="{{ __('father') . ' ' . __('image') }}" />
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme"
                                                type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                    <div style="width: 100px;">
                                        <img src="" id="edit-father-image-tag" class="img-fluid w-100" />
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-12">
                                    <label>{{ __('mother_email') }} <span class="text-danger">*</span></label>
                                    <select class="edit-mother-search w-100" id="edit_mother_email"
                                        name="mother_email"></select>
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('mother') . ' ' . __('first_name') }} <span
                                            class="text-danger">*</span></label>
                                    {!! Form::text('mother_first_name', null, ['placeholder' => __('mother') . ' ' . __('first_name'), 'class' => 'form-control', 'id' => 'edit_mother_first_name', 'readonly' => true]) !!}
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('mother') . ' ' . __('last_name') }} <span
                                            class="text-danger">*</span></label>
                                    {!! Form::text('mother_last_name', null, ['placeholder' => __('mother') . ' ' . __('last_name'), 'class' => 'form-control', 'id' => 'edit_mother_last_name', 'readonly' => true]) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('mother') . ' ' . __('mobile') }} <span
                                            class="text-danger">*</span></label>
                                    {!! Form::text('mother_mobile', null, ['placeholder' => __('mother') . ' ' . __('mobile'), 'class' => 'form-control', 'id' => 'edit_mother_mobile', 'readonly' => true , 'min' => 1]) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('mother') . ' ' . __('dob') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('mother_dob', null, ['placeholder' => __('mother') . ' ' . __('dob'), 'class' => 'form-control datepicker-popup-no-future form-control', 'id' => 'edit_mother_dob', 'readonly' => true]) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('mother') . ' ' . __('occupation') }} <span
                                            class="text-danger">*</span></label>
                                    {!! Form::text('mother_occupation', null, ['placeholder' => __('mother') . ' ' . __('occupation'), 'class' => 'form-control', 'id' => 'edit_mother_occupation', 'readonly' => true]) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('mother') . ' ' . __('image') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="mother_image" class="file-upload-default"  accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled=""
                                            placeholder="{{ __('mother') . ' ' . __('image') }}" />
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme"
                                                type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                    <div style="width: 100px;">
                                        <img src="" id="edit-mother-image-tag" class="img-fluid w-100" />
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="form-group">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input"
                                            id="show-edit-guardian-details">{{ __('guardian_details') }}
                                    </label>
                                </div>
                            </div> --}}
                            <div class="row" id="edit_guardian_div" style="display:none;">
                                <div class="form-group col-sm-12 col-md-12">
                                    <label>{{ __('guardian') . ' ' . __('email') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="edit-guardian-search form-control" id="edit_guardian_email"
                                        name="guardian_email"></select>
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('guardian') . ' ' . __('first_name') }} <span
                                            class="text-danger">*</span></label>
                                    {!! Form::text('guardian_first_name', null, ['placeholder' => __('guardian') . ' ' . __('first_name'), 'class' => 'form-control', 'id' => 'edit_guardian_first_name', 'readonly' => true]) !!}
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('guardian') . ' ' . __('last_name') }} <span
                                            class="text-danger">*</span></label>
                                    {!! Form::text('guardian_last_name', null, ['placeholder' => __('guardian') . ' ' . __('last_name'), 'class' => 'form-control', 'id' => 'edit_guardian_last_name', 'readonly' => true]) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('guardian') . ' ' . __('mobile') }} <span
                                            class="text-danger">*</span></label>
                                    {!! Form::text('guardian_mobile', null, ['placeholder' => __('guardian') . ' ' . __('mobile'), 'class' => 'form-control', 'id' => 'edit_guardian_mobile', 'readonly' => true , 'min' => 1]) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-12">
                                    <label>{{ __('gender') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" name="guardian_gender" value="male" id="edit_guardian_male">
                                                {{ __('male') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" name="guardian_gender" value="female" id="edit_guardian_female">
                                                {{ __('female') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('guardian') . ' ' . __('dob') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('guardian_dob', null, ['placeholder' => __('guardian') . ' ' . __('dob'), 'class' => 'form-control datepicker-popup-no-future form-control', 'id' => 'edit_guardian_dob', 'readonly' => true]) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('guardian') . ' ' . __('occupation') }} <span
                                            class="text-danger">*</span></label>
                                    {!! Form::text('guardian_occupation', null, ['placeholder' => __('guardian') . ' ' . __('occupation'), 'class' => 'form-control', 'id' => 'edit_guardian_occupation', 'readonly' => true]) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('guardian') . ' ' . __('image') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="file" name="guardian_image" class="file-upload-default" accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled=""
                                            placeholder="{{ __('guardian') . ' ' . __('image') }}" />
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme"
                                                type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                    <div style="width: 100px;">
                                        <img src="" id="edit-guardian-image-tag" class="img-fluid w-100" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input class="btn btn-theme" type="submit" value={{ __('submit') }}>
                            <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('cancel') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection
