@extends('layouts.master')

@section('title')
    {{ __('staff').' '. __('management') }}
@endsection

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            {{ __('manage') . ' ' . __('staff') }}
        </h3>
    </div>
    <div class="row">
        @can('staff-create')
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('create') . ' ' . __('staff') }}
                        </h4>
                        <form class="pt-3 staff-form" id="staff-form" enctype="multipart/form-data" action="{{ route('staff.store') }}" method="POST" novalidate="novalidate">
                            @csrf
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-4">
                                    <label> {{ __('role') }} <span class="text-danger">*</span></label>
                                    <select name="role_id" class="form-control">
                                        <option value="">{{ __('select') . ' ' . __('role') }}</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('first_name') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('first_name', null, ['placeholder' => __('first_name'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('last_name') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('last_name', null, ['placeholder' => __('last_name'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('email') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('email', null, ['required', 'placeholder' => __('email'), 'class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('mobile') }} <span class="text-danger">*</span></label>
                                    {!! Form::number('mobile', null, ['required','placeholder' => __('mobile'), 'class' => 'form-control mobile' , 'min' => 10]) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('gender') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('gender', 'male') !!}
                                                {{ __('male') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('gender', 'female') !!}
                                                {{ __('female') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('dob') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('dob', null, ['placeholder' => __('dob'), 'class' => 'datepicker-popup-no-future form-control']) !!}
                                    <span class="input-group-addon input-group-append">
                                    </span>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('image') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="image" class="file-upload-default" accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ __('image') }}" required="required"/>
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group col-4">
                                    <label>{{ __('address') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('address', null, ['placeholder' => __('address'), 'class' => 'form-control','rows'=>2]) !!}
                                </div>
                            </div>
                            <input class="btn btn-theme" type="submit" value={{ __('submit') }}>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        @can('staff-list')
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('staff') }}
                        </h4>
                        <table aria-describedby="mydesc" class='table table-striped' id='table_list'
                            data-toggle="table" data-url="{{route('staff.show',1)}}" data-click-to-select="true"
                            data-search="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                            data-toolbar="#toolbar" data-show-columns="true"
                            data-show-refresh="true" data-fixed-columns="true"
                            data-fixed-number="2" data-fixed-right-number="1"
                            data-trim-on-search="false" data-mobile-responsive="true"
                            data-sort-name="id" data-sort-order="desc"
                            data-maintain-selected="true" data-export-types='["txt","excel"]'
                            data-export-options='{ "fileName": "staff-list-<?= date('d-m-y') ?>" ,"ignoreColumn": ["operate"]}'>
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                    <th scope="col" data-field="no" data-sortable="false">{{ __('no.') }}</th>
                                    <th scope="col" data-field="user_id" data-sortable="false" data-visible="false">{{ __('user_id') }}</th>
                                    <th scope="col" data-field="role_id" data-sortable="false" data-visible="false">{{ __('role_id') }}</th>
                                    <th scope="col" data-field="roles" data-sortable="false">{{ __('role') }}</th>
                                    <th scope="col" data-field="first_name" data-sortable="false">{{ __('first_name') }}</th>
                                    <th scope="col" data-field="last_name" data-sortable="false">{{ __('last_name') }}</th>
                                    <th scope="col" data-field="email" data-sortable="false">{{ __('email') }}</th>
                                    <th scope="col" data-field="mobile" data-sortable="false">{{ __('mobile') }}</th>
                                    <th scope="col" data-field="dob" data-sortable="false">{{ __('dob') }}</th>
                                    <th scope="col" data-field="gender" data-sortable="false">{{ __('gender') }}</th>
                                    <th scope="col" data-field="address" data-sortable="false">{{ __('address') }}</th>
                                    <th scope="col" data-field="image" data-sortable="false"data-formatter="imageFormatter">{{ __('image') }}</th>
                                    @can('staff-edit')
                                        <th scope="col" data-field="operate" data-sortable="false" data-events="staffEvents">{{__('action')}}</th>
                                    @endcan

                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        @endcan


        {{-- Edit Modal Starts--}}
        <div class="modal fade" id="editModal" data-backdrop="static" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ __('edit') . ' ' . __('staff') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="fa fa-close"></i></span>
                        </button>
                    </div>
                    <form class="staff-edit-form"  id="staff-edit-form" action="{{ route('staff.update',1)}}" novalidate="novalidate" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="user_id" id="user_id">
                            <input type="hidden" name="edit_id" id="edit_id">
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-6">
                                    <label> {{ __('role') }} <span class="text-danger">*</span></label>
                                    <select name="role_id" id="role_id" class="form-control">
                                        <option value="">{{ __('select') . ' ' . __('role') }}</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('first_name') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('first_name', null, ['required','placeholder' => __('first_name'), 'class' => 'form-control','id' => 'first_name']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('last_name') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('last_name', null, ['required','placeholder' => __('last_name'), 'class' => 'form-control','id' => 'last_name']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('email') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('email', null, ['required', 'placeholder' => __('email'), 'class' => 'form-control','id' => 'email']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('mobile') }} <span class="text-danger">*</span></label>
                                    {!! Form::number('mobile', null, ['required','placeholder' => __('mobile'), 'class' => 'form-control mobile' , 'min' => 10,'id' => 'mobile']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('gender') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('gender', 'male', null, ['required','class' => 'form-check-input edit', 'id' => 'gender']) !!}
                                                {{ __('male') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('gender', 'female', null, ['required','class' => 'form-check-input edit', 'id' => 'gender']) !!}
                                                {{ __('female') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('dob') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('dob', null, ['required','placeholder' => __('dob'), 'class' => 'datepicker-popup-no-future form-control','id' => 'dob']) !!}
                                    <span class="input-group-addon input-group-append">
                                    </span>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('address') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('address', null, ['required', 'placeholder' => __('address'), 'class' => 'form-control address', 'rows' => 3, 'id' => 'address']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('image') }}</label>
                                    <input type="file" name="image" class="file-upload-default" accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ __('image') }}" required="required"/>
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                    <div style="width: 100px; margin-top: 10px">
                                        <img src="" id="edit-staff-image" class="img-fluid w-100" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input class="btn btn-theme" type="submit" value={{ __('edit') }} />
                            <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('cancel') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    <script type="text/javascript">
        window.staffEvents = {
            'click .edit-data': function (e, value, row, index) {

                $('#user_id').val(row.user_id);
                $('#edit_id').val(row.id);
                $('#first_name').val(row.first_name);
                $('#last_name').val(row.last_name);
                $('#email').val(row.email);
                $('#mobile').val(row.mobile);
                $('#dob').val(row.dob);
                $('#role_id').val(row.role_id);
                $('#edit-staff-image').attr('src', row.image);

                $('#address').val(row.address);
                $('input[name=gender][value=' + row.gender + '].edit').prop('checked', true);

            }
        };

    </script>
@endsection

