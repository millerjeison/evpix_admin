@extends('layouts.master')

@section('title') {{__('role_management')}} @endsection

@section('content')

<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
             {{__('role_management')}}
        </h3>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin  stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        {{ __('manage') . ' ' . __('roles') }}
                    </h4>
                        {!! Form::open(['route' => 'roles.store', 'method' => 'POST','class' => 'pt-3']) !!}
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <label>{{ __('name') }}</label>
                                    {!! Form::text('name', null, ['placeholder' => 'Name', 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <label>{{ __('permission') }}</label>
                                <div class="row">
                                    @foreach ($permission as $value)
                                        <div class="form-group col-lg-3 col-sm-12 col-xs-12 col-md-3">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    {{ Form::checkbox('permission[]', $value->id, false, ['class' => 'name form-check-input']) }}
                                                    {{ $value->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <button type="submit" class="btn btn-theme">{{ __('submit') }}</button>
                            </div>
                        {!! Form::close() !!}
                </div>
            </div>
        </div>

        <div class="col-md-12 grid-margin  stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        {{ __('list') . ' ' . __('roles') }}
                    </h4>
                    <table aria-describedby="mydesc" class='table table-striped' id='table_list'
                    data-toggle="table" data-url="{{route('roles.show',1)}}" data-click-to-select="true" data-search="true"
                    data-side-pagination="server" data-pagination="true"
                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                    data-toolbar="#toolbar" data-show-columns="true"
                    data-show-refresh="true" data-fixed-columns="true"
                    data-fixed-number="2" data-fixed-right-number="1"
                    data-trim-on-search="false" data-mobile-responsive="true"
                    data-sort-name="id" data-sort-order="desc"
                    data-maintain-selected="true" data-export-types='["txt","excel"]'
                    data-export-options='{ "fileName": "role-list-<?= date('d-m-y') ?>" ,"ignoreColumn": ["operate"]}'>
                    <thead>
                        <tr>
                            <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                            <th scope="col" data-field="no" data-sortable="false">{{ __('no.') }}</th>
                            <th scope="col" data-field="name" data-sortable="false">{{__('name')}}</th>
                            @can('role-edit')
                                <th scope="col" data-field="operate" data-sortable="false">{{__('action')}}</th>
                            @endcan

                        </tr>
                    </thead>
                </table>
                    {!! $roles->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
