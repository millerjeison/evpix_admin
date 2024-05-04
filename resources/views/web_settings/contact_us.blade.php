@extends('layouts.master')

@section('title')
    {{ __('contact_us') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('contact_us') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('contact_us') }}
                        </h4>
                        <table aria-describedby="mydesc" class='table table-striped' id='table_list'
                               data-toggle="table" data-url="{{url('contact-us-list')}}" data-click-to-select="true"
                               data-side-pagination="server" data-pagination="true"
                               data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                               data-toolbar="#toolbar" data-show-columns="true"
                               data-show-refresh="true" data-fixed-columns="true"
                               data-fixed-number="2" data-fixed-right-number="1"
                               data-trim-on-search="false" data-mobile-responsive="true"
                               data-sort-name="id" data-sort-order="desc"
                               data-maintain-selected="true" data-export-types='["txt","excel"]'
                               data-export-options='{ "fileName": "slider-list-<?= date('d-m-y') ?>" ,"ignoreColumn": ["operate"]}'>
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                <th scope="col" data-field="no" data-sortable="false">{{ __('no.') }}</th>
                                <th scope="col" data-field="first_name" data-sortable="true">{{ __('first_name') }}</th>
                                <th scope="col" data-field="last_name" data-sortable="true">{{ __('last_name') }}</th>
                                <th scope="col" data-field="email" data-sortable="true">{{ __('email') }}</th>
                                <th scope="col" data-field="phone" data-sortable="true">{{ __('phone') }}</th>
                                <th scope="col" data-field="message" data-sortable="true">{{ __('message') }}</th>
                                <th scope="col" data-field="date" data-sortable="true">{{ __('date') }}</th>
                                <th scope="col" data-field="created_at" data-sortable="true" data-visible="false">{{ __('created_at') }}</th>
                                <th scope="col" data-field="updated_at" data-sortable="true" data-visible="false">{{ __('updated_at') }}</th>
                                <th scope="col" data-field="operate" data-sortable="false" data-events="actionEvents">{{ __('action') }}</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade " id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content" style="width: 800px; padding: 25px;">
                        <div class="modal-header">
                            <h4 class="modal-title" id="exampleModalLabel">{{ __('compose') }}</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form class="edit-program" id="edit-program" enctype="multipart/form-data" action="{{route('contact_us.reply',1)}}" novalidate="novalidate">
                            <div class="modal-body">
                                <input type="hidden" name="edit_id" id="edit_id" value=""/>
                                <input type="hidden" name="first_name" id="first_name" value=""/>
                                <input type="hidden" name="last_name" id="last_name" value=""/>
                                <input type="hidden" name="message" id="message" value=""/>
                                <div class="row mb-3">
                                    <label class="col-md-2 text-center mt-3" >{{ __('to') }}</label>
                                    {!! Form::text('email', null, ['required', 'class' => 'form-control col-md-10','id' => 'email']) !!}
                                </div>
                                <div class="row mb-3">
                                    <label class="col-md-2 text-center mt-3">{{ __('subject') }}</label>
                                    {!! Form::text('subject', null, ['required','class' => 'form-control col-md-10', 'id' => 'subject']) !!}
                                </div>
                                <div class="row mb-3">
                                    <label class="col-md-2 text-center mt-3">{{ __('message') }} </label>
                                    {!! Form::textarea('reply_message', null, ['required','class' => 'form-control col-md-10', 'id' => 'reply_message' ,'rows' => 10]) !!}
                                </div>
                                <div class="row mt-5">
                                    <div class="col-md-2"></div>
                                    <div class="d-flex">
                                        <div class="form-group mr-4">
                                            <input type="file" name="file[]" class="file-upload-default" multiple/>
                                            <button type="button" class="file-upload-browse btn btn-secondary">
                                                <i class="fa fa-plus"></i>&nbsp;{{ __('attachment') }}</button>
                                        </div>
                                        <div class="form-group">
                                            <input class="btn btn-theme" type="submit" value={{ __('send') }} />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    </div>
@endsection
@section('script')
<script type="text/javascript">

    window.actionEvents = {
        'click .edit-data': function (e, value, row, index) {
            $('#edit_id').val(row.id);
            $('#email').val(row.email);
            $('#first_name').val(row.first_name);
            $('#last_name').val(row.last_name);
            $('#message').val(row.message);
        }
    };
    </script>

    <script type="text/javascript">
        function queryParams(p) {
            return {
                limit: p.limit,
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                search: p.search
            };
        }
    </script>
@endsection
