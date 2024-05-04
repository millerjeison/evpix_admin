@extends('layouts.master')

@section('title')
    {{ __('notifications') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage').' '.__('notifications') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <h4 class="card-title">
                                    {{ __('create').' '.__('notifications') }}
                                </h4>
                            </div>
                        </div>
                        <form class="pt-3 create-notification" id="create_notification"  method="POST" novalidate="novalidate" action="{{route('notifications.store')}}">
                            @csrf
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('send_to') }} <span class="text-danger">*</span></label>
                                    <select name="send_to" id="send_to" class="form-control send_to">
                                        <option value="">Please Select</option>
                                        <option value="1">{{ __('all_users') }}</option>
                                        <option value="2">{{ __('specific_user') }}</option>
                                        <option value="3">{{ __('students') }}</option>
                                        <option value="4">{{ __('parents') }}</option>
                                        <option value="5">{{ __('teacher') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12 col-md-6 user_div" id="user_div" style="display: none">
                                    <label>{{ __('user') }} <span class="text-danger">*</span></label>
                                    <select multiple name="user_id[]" id="user_id" class="form-control js-example-basic-single select2-hidden-accessible">
                                        @foreach ($users as $user)
                                            <option value="{{$user->id}}">{{$user->first_name }} {{$user->last_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('title') }} <span class="text-danger">*</span></label>
                                    <input name="title" type="text" placeholder="{{ __('title') }}" class="form-control" />
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('message') }} <span class="text-danger">*</span></label>
                                    <textarea name="message" cols="10" rows="3" placeholder="{{ __('message') }}" class="form-control"></textarea>
                                </div>
                                <div class="form-group col-sm-12 col-md-12">
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="checkbox" name="image" value="image" class="form-check-input image-check" id="show-image-uploader">{{ __('include').' '.__('image') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-6 image-uploader" id="image-uploader" style="display: none">
                                    <label>{{ __('image') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="image" class="file-upload-default" accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ __('image') }}" required="required"/>
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <input class="btn btn-theme" type="submit" value={{ __('submit') }}>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('notifications') }}
                        </h4>
                        <table aria-describedby="mydesc" class='table table-striped' id='table_list' data-toggle="table" data-url="{{route('notifications.show',1)}}" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-mobile-responsive="true" data-use-row-attr-func="true" data-reorderable-rows="false" data-maintain-selected="true" data-export-types='["txt","excel"]' data-export-options='{ "fileName": "{{__('notifications')}}-<?= date(' d-m-y') ?>" ,"ignoreColumn":["operate"]}' data-show-export="true" data-query-params="notificationQueryParams">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                    <th scope="col" data-field="no">{{ __('no.') }}</th>
                                    <th scope="col" data-field="title" data-sortable="true">{{ __('title') }}</th>
                                    <th scope="col" data-field="message" data-sortable="true">{{ __('message') }}</th>
                                    <th scope="col" data-field="image" data-sortable="true" data-formatter="notificationImageFormatter">{{ __('image') }}</th>
                                    <th scope="col" data-field="type" data-sortable="true">{{ __('type') }}</th>
                                    <th scope="col" data-field="date" data-sortable="true">{{ __('date') }}</th>
                                    <th scope="col" data-field="created_at" data-sortable="true" data-visible="false">{{__('created_at')}}</th>
                                    <th scope="col" data-field="updated_at" data-sortable="true" data-visible="false">{{__('updated_at')}}</th>
                                    <th scope="col" data-field="operate" data-sortable="false">{{ __('action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
