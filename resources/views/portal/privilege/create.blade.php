@extends('vendor.template.layout')

@section('content')
@php
    $privileges = \App\Models\UserPrivileges::join('users', 'users.id', 'user_privileges.user_id')
    ->join('designations', 'designations.id', '=', 'users.designation_id')->where(['users.id' => $record->id, 'user_privileges.designation_id' => \App\Models\User::where('id', $record->id)->pluck('designation_id')->first()])->pluck('privileges')->first();
    $privileges = json_decode($privileges, true);
@endphp
    <style>
        .col-8{
            margin-top: 9px;
        }
        .btn{
            border-radius: 8px;
        }
    </style>
    <div class="card mb-5">
        <div class="card-body">
            <div class="col-xs-12 col-sm-12">

                @if(session()->has('message'))
                    <div class="alert alert-success">
                        {{ session()->get('message') }}
                    </div>
                @endif

                <form method="post" id="admin-form" action="{{ url('vendor/save_privilege') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">

                    <input type="hidden" name="id" value="{{ $record->id ?? 0 }}">

                    @csrf()

                    <div class="form-group">

                      <fieldset>
                          <legend>Access Rights</legend>
                         <div class="form-group row mt-0 mb-0">
                                    <label class="col-sm-2 col-form-label">Store</label>
                                    <div class="col-sm-10">
                                        <div class="row">
                                            <div class="col-8" role="access-group-row">

                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[Store][View]" value="1" data-parsley-multiple="access_groups1076"
                                                            @if( isset($privileges['Store']['View']) && $privileges['Store']['View'] == 1 )
                                                                checked
                                                            @endif
                                                            >
                                                            View
                                                            <i class="input-helper"></i>
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[Store][Create]" value="1" data-parsley-multiple="access_groups1076"
                                                               @if( isset($privileges['Store']['Create']) && $privileges['Store']['Create'] == 1 )
                                                                checked
                                                               @endif
                                                            >
                                                            Create
                                                            <i class="input-helper"></i>
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[Store][Edit]" value="1" data-parsley-multiple="access_groups1076"
                                                            @if( isset($privileges['Store']['Edit']) && $privileges['Store']['Edit'] == 1 )
                                                                checked
                                                            @endif
                                                            >
                                                            Edit
                                                            <i class="input-helper"></i>
                                                        </label>
                                                    </div>

                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[Store][Delete]" value="1" data-parsley-multiple="access_groups1076"
                                                            @if( isset($privileges['Store']['Delete']) && $privileges['Store']['Delete'] == 1 )
                                                                checked
                                                            @endif
                                                            >
                                                            Delete
                                                            <i class="input-helper"></i>
                                                        </label>
                                                    </div>
                                            </div>
                                            <div class="col-4 pt-1" style="display: none;">
                                                <button type="button" class="btn btn-sm btn-outline-success" role="access-set-all">Set All</button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" role="access-reset-all">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                          <div class="form-group row mt-0 mb-0">
                              <label class="col-sm-2 col-form-label">Orders</label>
                              <div class="col-sm-10">
                                  <div class="row">
                                      <div class="col-8" role="access-group-row">

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Orders][View]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Orders']['View']) && $privileges['Orders']['View'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  View
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Orders][Create]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Orders']['Create']) && $privileges['Orders']['Create'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  Create
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Orders][Edit]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Orders']['Edit']) && $privileges['Orders']['Edit'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  Edit
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Orders][Delete]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Orders']['Delete']) && $privileges['Orders']['Delete'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  Delete
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>
                                      </div>
                                      <div class="col-4 pt-1" style="display: none;">
                                          <button type="button" class="btn btn-sm btn-outline-success" role="access-set-all">Set All</button>
                                          <button type="button" class="btn btn-sm btn-outline-warning" role="access-reset-all">Reset</button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                           <div class="form-group row mt-0 mb-0">
                              <label class="col-sm-2 col-form-label">Roles</label>
                              <div class="col-sm-10">
                                  <div class="row">
                                      <div class="col-8" role="access-group-row">

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Roles][View]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Roles']['View']) && $privileges['Roles']['View'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  View
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Roles][Create]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Roles']['Create']) && $privileges['Roles']['Create'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  Create
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Roles][Edit]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Roles']['Edit']) && $privileges['Roles']['Edit'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  Edit
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Roles][Delete]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Roles']['Delete']) && $privileges['Roles']['Delete'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  Delete
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>
                                      </div>
                                      <div class="col-4 pt-1" style="display: none;">
                                          <button type="button" class="btn btn-sm btn-outline-success" role="access-set-all">Set All</button>
                                          <button type="button" class="btn btn-sm btn-outline-warning" role="access-reset-all">Reset</button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                           <div class="form-group row mt-0 mb-0">
                              <label class="col-sm-2 col-form-label">Designation</label>
                              <div class="col-sm-10">
                                  <div class="row">
                                      <div class="col-8" role="access-group-row">

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Designation][View]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Designation']['View']) && $privileges['Designation']['View'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  View
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Designation][Create]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Designation']['Create']) && $privileges['Designation']['Create'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  Create
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Designation][Edit]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Designation']['Edit']) && $privileges['Designation']['Edit'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  Edit
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Designation][Delete]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Designation']['Delete']) && $privileges['Designation']['Delete'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  Delete
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>
                                      </div>
                                      <div class="col-4 pt-1" style="display: none;">
                                          <button type="button" class="btn btn-sm btn-outline-success" role="access-set-all">Set All</button>
                                          <button type="button" class="btn btn-sm btn-outline-warning" role="access-reset-all">Reset</button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                           <div class="form-group row mt-0 mb-0">
                              <label class="col-sm-2 col-form-label">Product list</label>
                              <div class="col-sm-10">
                                  <div class="row">
                                      <div class="col-8" role="access-group-row">

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Product_list][View]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Product_list']['View']) && $privileges['Product_list']['View'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  View
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Product_list][Create]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Product_list']['Create']) && $privileges['Product_list']['Create'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  Create
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Product_list][Edit]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Product_list']['Edit']) && $privileges['Product_list']['Edit'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  Edit
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>

                                          <div class="form-check form-check-inline mr-5 ">
                                              <label class="form-check-label">
                                                  <input type="checkbox" class="form-check-input" name="access_groups[Product_list][Delete]" value="1" data-parsley-multiple="access_groups1076"
                                                         @if( isset($privileges['Product_list']['Delete']) && $privileges['Product_list']['Delete'] == 1 )
                                                         checked
                                                          @endif
                                                  >
                                                  Delete
                                                  <i class="input-helper"></i>
                                              </label>
                                          </div>
                                      </div>
                                      <div class="col-4 pt-1" style="display: none;">
                                          <button type="button" class="btn btn-sm btn-outline-success" role="access-set-all">Set All</button>
                                          <button type="button" class="btn btn-sm btn-outline-warning" role="access-reset-all">Reset</button>
                                      </div>
                                  </div>
                              </div>
                          </div> 
                      </fieldset>

                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>

                </form>
            </div>
            <div class="col-xs-12 col-sm-6">

            </div>
        </div>
    </div>
@stop
