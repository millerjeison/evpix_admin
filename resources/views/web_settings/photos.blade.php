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
                            {{ __('create') . ' ' . __('photos') }}
                        </h4>
                        <div class="col-12">
                            <form class="pt-3 sliders-create-form" id="create-form" action="{{route('photos.store')}}" method="POST" novalidate="novalidate" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('name') }} <span class="text-danger">*</span></label>
                                            {!! Form::text('name', null, ['required', 'placeholder' => __('name'), 'class' => 'form-control', 'id' => 'name']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('thumbnail') }} <span class="text-danger">*</span></label>
                                            <input type="file" name="thumbnail" class="file-upload-default"  accept="image/*"/>
                                            <div class="input-group col-xs-12">
                                                <input type="text" class="form-control file-upload-info" placeholder="{{ __('thumbnail') }}"/>
                                                <span class="input-group-append">
                                                    <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('image') }} <span class="text-danger">*</span></label>
                                            <input type="file" name="image[]" class="file-upload-default" multiple  accept="image/*"/>
                                            <div class="input-group col-xs-12">
                                                <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ __('image') }}"/>
                                                <span class="input-group-append">
                                                    <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                                </span>
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
                            {{ __('list') . ' ' . __('educational_program') }}
                        </h4>
                        <table aria-describedby="mydesc" class='table table-striped' id='table_list'
                               data-toggle="table" data-url="{{route('photos.show',1)}}" data-click-to-select="true"
                               data-side-pagination="server" data-pagination="true"
                               data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                               data-toolbar="#toolbar" data-show-columns="true"
                               data-show-refresh="true" data-fixed-columns="true"
                               data-fixed-number="2" data-fixed-right-number="1"
                               data-trim-on-search="false" data-mobile-responsive="true"
                               data-sort-name="id" data-sort-order="desc"
                               data-maintain-selected="true" data-export-types='["txt","excel"]'
                               data-export-options='{ "fileName": "photo-list-<?= date('d-m-y') ?>" ,"ignoreColumn": ["operate"]}'>
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                <th scope="col" data-field="no" data-sortable="false">{{ __('no.') }}</th>
                                <th scope="col" data-field="name" data-sortable="true">{{ __('title') }}</th>
                                <th scope="col" data-field="thumbnail" data-sortable="true" data-formatter="thumbnailFormatter">{{ __('thumbnail') }}</th>
                                <th scope="col" data-field="created_at" data-sortable="true" data-visible="false">{{ __('created_at') }}</th>
                                <th scope="col" data-field="updated_at" data-sortable="true" data-visible="false">{{ __('updated_at') }}</th>
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
