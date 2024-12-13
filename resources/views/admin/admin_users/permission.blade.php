@extends('admin.template.layout')

@section('content')
<style>
    .btn-mini{
        padding: 6px 10px !important;
        border-radius: 5px !important;
    }
</style>
    <div class="card">
    <div class="card-body">
        <div class="col-xs-12 col-sm-12">
            <form method="post" id="admin-form" action="{{ url('admin/update_user_permission') }}" enctype="multipart/form-data" data-parsley-validate="true">
                @csrf()
                <input type="hidden" id="id" name="id" value="{{ $id }}" />
                <div class="form-group">
                    <fieldset>
                        <legend>Access Rights</legend>

                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Dashboard</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input dashboard" name="dashboard_view" value="1" @if( isset ( $user_permissions['dashboard_view'] )) {{'checked'}} @endif> View
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="dashboard">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="dashboard">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
<!-- 
                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Admin Users</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input adminusers" name="admin_users_view" value="1" @if( isset ( $user_permissions['admin_users_view'] )) {{'checked'}} @endif> View
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input adminusers" name="admin_users_create" value="1" @if( isset ( $user_permissions['admin_users_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input adminusers" name="admin_users_edit" value="1" @if( isset ( $user_permissions['admin_users_edit'] )) {{'checked'}} @endif> Edit
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input adminusers" name="admin_users_delete" value="1" @if( isset ( $user_permissions['admin_users_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="adminusers">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="adminusers">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <!-- <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Admin User Designation</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input admin_user_desig" name="admin_user_desig_view" value="1" @if( isset ( $user_permissions['admin_user_desig_view'] )) {{'checked'}} @endif> View
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input admin_user_desig" name="admin_user_desig_create" value="1" @if( isset ( $user_permissions['admin_user_desig_create'] )) {{'checked'}} @endif>
                                                Create <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input admin_user_desig" name="admin_user_desig_edit" value="1" @if( isset ( $user_permissions['admin_user_desig_edit'] )) {{'checked'}} @endif> Edit
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input admin_user_desig" name="admin_user_desig_delete" value="1" @if( isset ( $user_permissions['admin_user_desig_delete'] )) {{'checked'}} @endif>
                                                Delete <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="admin_user_desig">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="admin_user_desig">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <div class="form-group row mt-0 mb-4">
                            <label class="col-sm-2 col-form-label">Customers</label>

                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input customers" name="customers_view" value="1" @if( isset ( $user_permissions['customers_view'] )) {{'checked'}} @endif> View
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input customers" name="customers_create" value="1" @if( isset ( $user_permissions['customers_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input customers" name="customers_edit" value="1" @if( isset ( $user_permissions['customers_edit'] )) {{'checked'}} @endif> Edit
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input customers" name="customers_delete" value="1" @if( isset ( $user_permissions['customers_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="customers">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="customers">Reset</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="form-group row mt-0 mb-4">
                            <label class="col-sm-2 col-form-label">Vendors</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input vendor" name="vendor_view" value="1" @if( isset ( $user_permissions['vendor_view'] )) {{'checked'}} @endif> View <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input vendor" name="vendor_create" value="1" @if( isset ( $user_permissions['vendor_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input vendor" name="vendor_edit" value="1" @if( isset ( $user_permissions['vendor_edit'] )) {{'checked'}} @endif> Edit <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input vendor" name="vendor_delete" value="1" @if( isset ( $user_permissions['vendor_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="vendor">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="vendor">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <div class="form-group row mt-0 mb-4">
                            <label class="col-sm-2 col-form-label">Vendor Earnings</label>
                            <div class="col-sm-10">
                                <div class="row">
                                    <div class="col-8" role="access-group-row">
                                        
                                        <div class="form-check form-check-inline mr-5">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input vendor_earning" name="vendor_earning" value="1" @if( isset ( $user_permissions['vendor_earning'] )) {{'checked'}} @endif> Earnings
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input vendor_earning" name="vendor_service_earning" value="1" @if( isset ( $user_permissions['vendor_service_earning'] )) {{'checked'}} @endif> Service Earnings
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>

                                    </div>
                                    <div class="col-4 pt-1">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="vendor_earning">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="vendor_earning">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="form-group row mt-0 mb-0">
                            <label class="col-sm-2 col-form-label">Stores</label>

                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input stores" name="stores_view" value="1" @if( isset ( $user_permissions['stores_view'] )) {{'checked'}} @endif> View <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input stores" name="stores_create" value="1" @if( isset ( $user_permissions['stores_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input stores" name="stores_edit" value="1" @if( isset ( $user_permissions['stores_edit'] )) {{'checked'}} @endif> Edit <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input stores" name="stores_delete" value="1" @if( isset ( $user_permissions['stores_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="stores">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="stores">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Orders</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input orders" name="orders_view" value="1" @if( isset ( $user_permissions['orders_view'] )) {{'checked'}} @endif> View <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <!-- <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input orders" name="orders_create" value="1" @if( isset ( $user_permissions['orders_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div> -->
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input orders" name="orders_edit" value="1" @if( isset ( $user_permissions['orders_edit'] )) {{'checked'}} @endif> Edit <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <!-- <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input orders" name="orders_delete" value="1" @if( isset ( $user_permissions['orders_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div> -->
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="orders">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="orders">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Services</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input services" name="services_view" value="1" @if( isset ( $user_permissions['services_view'] )) {{'checked'}} @endif> View
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input services" name="services_create" value="1" @if( isset ( $user_permissions['services_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input services" name="services_edit" value="1" @if( isset ( $user_permissions['services_edit'] )) {{'checked'}} @endif> Edit
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input services" name="services_delete" value="1" @if( isset ( $user_permissions['services_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="services">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="services">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Products & Dishes</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input products" name="products_view" value="1" @if( isset ( $user_permissions['products_view'] )) {{'checked'}} @endif> View
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input products" name="products_create" value="1" @if( isset ( $user_permissions['products_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input products" name="products_edit" value="1" @if( isset ( $user_permissions['products_edit'] )) {{'checked'}} @endif> Edit
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input products" name="products_delete" value="1" @if( isset ( $user_permissions['products_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="products">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="products">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Service Request</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input service_request" name="service_request_view" value="1" @if( isset ( $user_permissions['service_request_view'] )) {{'checked'}} @endif> View
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                       <!--  <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input service_request" name="products_create" value="1" @if( isset ( $user_permissions['service_request_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input service_request" name="products_edit" value="1" @if( isset ( $user_permissions['service_request_edit'] )) {{'checked'}} @endif> Edit
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input service_request" name="products_delete" value="1" @if( isset ( $user_permissions['service_request_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div> -->
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="service_request">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="service_request">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Masters</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input masters" name="masters_view" value="1" @if( isset ( $user_permissions['masters_view'] )) {{'checked'}} @endif> View <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input masters" name="masters_create" value="1" @if( isset ( $user_permissions['masters_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input masters" name="masters_edit" value="1" @if( isset ( $user_permissions['masters_edit'] )) {{'checked'}} @endif> Edit <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input masters" name="masters_delete" value="1" @if( isset ( $user_permissions['masters_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="masters">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="masters">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Banners</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input banners" name="banners_view" value="1" @if( isset ( $user_permissions['banners_view'] )) {{'checked'}} @endif> View <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input banners" name="banners_create" value="1" @if( isset ( $user_permissions['banners_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input banners" name="banners_edit" value="1" @if( isset ( $user_permissions['banners_edit'] )) {{'checked'}} @endif> Edit <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input banners" name="banners_delete" value="1" @if( isset ( $user_permissions['banners_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="banners">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="banners">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Coupon Category</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input coupon_category" name="coupon_category_view" value="1" @if( isset ( $user_permissions['coupon_category_view'] )) {{'checked'}} @endif> View <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input coupon_category" name="coupon_category_create" value="1" @if( isset ( $user_permissions['coupon_category_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input coupon_category" name="coupon_category_edit" value="1" @if( isset ( $user_permissions['coupon_category_edit'] )) {{'checked'}} @endif> Edit <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input coupon_category" name="coupon_category_delete" value="1" @if( isset ( $user_permissions['coupon_category_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="coupon_category">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="coupon_category">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Coupon Brand</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input coupon_brand" name="coupon_brand_view" value="1" @if( isset ( $user_permissions['coupon_brand_view'] )) {{'checked'}} @endif> View <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input coupon_brand" name="coupon_brand_create" value="1" @if( isset ( $user_permissions['coupon_brand_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input coupon_brand" name="coupon_brand_edit" value="1" @if( isset ( $user_permissions['coupon_brand_edit'] )) {{'checked'}} @endif> Edit <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input coupon_brand" name="coupon_brand_delete" value="1" @if( isset ( $user_permissions['coupon_brand_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="coupon_brand">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="coupon_brand">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Transport</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input transport" name="transport_view" value="1" @if( isset ( $user_permissions['transport_view'] )) {{'checked'}} @endif> View <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input transport" name="transport_create" value="1" @if( isset ( $user_permissions['transport_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input transport" name="transport_edit" value="1" @if( isset ( $user_permissions['transport_edit'] )) {{'checked'}} @endif> Edit <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input transport" name="transport_delete" value="1" @if( isset ( $user_permissions['transport_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="transport">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="transport">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Coupon Codes</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input coupon" name="coupon_view" value="1" @if( isset ( $user_permissions['coupon_view'] )) {{'checked'}} @endif> View <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input coupon" name="coupon_create" value="1" @if( isset ( $user_permissions['coupon_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input coupon" name="coupon_edit" value="1" @if( isset ( $user_permissions['coupon_edit'] )) {{'checked'}} @endif> Edit <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input coupon" name="coupon_delete" value="1" @if( isset ( $user_permissions['coupon_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="coupon">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="coupon">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">CMS Pages</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input cms_pages" name="cms_pages_view" value="1" @if( isset ( $user_permissions['cms_pages_view'] )) {{'checked'}} @endif> View
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input cms_pages" name="cms_pages_create" value="1" @if( isset ( $user_permissions['cms_pages_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input cms_pages" name="cms_pages_edit" value="1" @if( isset ( $user_permissions['cms_pages_edit'] )) {{'checked'}} @endif> Edit
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input cms_pages" name="cms_pages_delete" value="1" @if( isset ( $user_permissions['cms_pages_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="cms_pages">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="cms_pages">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">FAQ's</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input faqs" name="faqs_view" value="1" @if( isset ( $user_permissions['faqs_view'] )) {{'checked'}} @endif> View <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input faqs" name="faqs_create" value="1" @if( isset ( $user_permissions['faqs_create'] )) {{'checked'}} @endif> Create <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input faqs" name="faqs_edit" value="1" @if( isset ( $user_permissions['faqs_edit'] )) {{'checked'}} @endif> Edit <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input faqs" name="faqs_delete" value="1" @if( isset ( $user_permissions['faqs_delete'] )) {{'checked'}} @endif> Delete <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="faqs">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="faqs">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Contact Detail Settings</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input contact_detail_settings" name="contact_detail_settings_view" value="1" @if( isset ( $user_permissions['contact_detail_settings_view'] ))
                                                {{'checked'}} @endif> View <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <!-- <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input contact_detail_settings" name="contact_detail_settings_create" value="1" @if( isset ( $user_permissions['contact_detail_settings_create'] ))
                                                {{'checked'}} @endif> Create <i class="input-helper"></i>
                                            </label>
                                        </div> -->
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input contact_detail_settings" name="contact_detail_settings_edit" value="1" @if( isset ( $user_permissions['contact_detail_settings_edit'] ))
                                                {{'checked'}} @endif> Edit <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <!-- <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input contact_detail_settings" name="contact_detail_settings_delete" value="1" @if( isset ( $user_permissions['contact_detail_settings_delete'] ))
                                                {{'checked'}} @endif> Delete <i class="input-helper"></i>
                                            </label>
                                        </div> -->
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="contact_detail_settings">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="contact_detail_settings">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Settings</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input settings" name="settings_view" value="1" @if( isset ( $user_permissions['settings_view'] )) {{'checked'}} @endif> View
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <!-- <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input settings" name="settings_create" value="1" @if( isset ( $user_permissions['settings_create'] )) {{'checked'}} @endif> Create
                                                <i class="input-helper"></i>
                                            </label>
                                        </div> -->
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input settings" name="settings_edit" value="1" @if( isset ( $user_permissions['settings_edit'] )) {{'checked'}} @endif> Edit
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <!-- <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input settings" name="settings_delete" value="1" @if( isset ( $user_permissions['settings_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div> -->
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="settings">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="settings">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Rating</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input rating" name="rating_view" value="1" @if( isset ( $user_permissions['rating_view'] )) {{'checked'}} @endif> View <i class="input-helper"></i>
                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input rating" name="rating_delete" value="1" @if( isset ( $user_permissions['rating_delete'] )) {{'checked'}} @endif> Delete
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="rating">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="rating">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Reports</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input reports" name="reports_view" value="1" @if( isset ( $user_permissions['reports_view'] )) {{'checked'}} @endif> View <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="reports">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="reports">Reset</button>
                                    </div>

                                    <div class="col-4 pt-0"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mt-0 mb-4 align-items-center">
                            <label class="col-sm-2 col-form-label pt-0 pb-0">Notification</label>
                            <div class="col-sm-10">
                                <div class="row align-items-center">
                                    <div class="col-8" role="access-group-row">
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input notification" name="notification_view" value="1" @if( isset ( $user_permissions['notification_view'] )) {{'checked'}} @endif> View
                                                <i class="input-helper"></i>
                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input notification" name="notification_create" value="1" @if( isset ( $user_permissions['notification_create'] )) {{'checked'}} @endif> Create <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <!-- <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input notification" name="notification_edit" value="1" @if( isset ( $user_permissions['notification_edit'] )) {{'checked'}} @endif> Edit <i class="input-helper"></i>
                                            </label>
                                        </div> -->
                                        <div class="form-check form-check-inline mr-5 mt-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input notification" name="notification_delete" value="1" @if( isset ( $user_permissions['notification_delete'] )) {{'checked'}} @endif> Delete <i class="input-helper"></i>
                                            </label>
                                        </div>

                                        
                                    </div>

                                    <div class="col-4 pt-0">
                                        <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all" target="notification">Set All</button>
                                        <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all" target="notification">Reset</button>
                                    </div>

                                    <div class="col-4 pt-0"></div>
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
        <div class="col-xs-12 col-sm-6"></div>
    </div>
</div>

@stop

@section('script')
    <script>
        App.initFormView();
        $('body').off('submit', '#admin-form');
        $('body').on('submit', '#admin-form', function(e) {
            e.preventDefault();
            var $form = $(this);
            var formData = new FormData(this);
            var id = $("#id").val();

            App.loading(true);
            $form.find('button[type="submit"]')
                .text('Saving')
                .attr('disabled', true);

            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: $form.attr('action'),
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                dataType: 'json',
                timeout: 600000,
                success: function(res) {
                    App.loading(false);

                    if (res['status'] == 0) {
                        if (typeof res['errors'] !== 'undefined') {
                            var error_def = $.Deferred();
                            var error_index = 0;
                            jQuery.each(res['errors'], function(e_field, e_message) {
                                if (e_message != '') {
                                    $('[name="' + e_field + '"]').eq(0).addClass('is-invalid');
                                    $('<div class="invalid-feedback">' + e_message + '</div>')
                                        .insertAfter($('[name="' + e_field + '"]').eq(0));
                                    if (error_index == 0) {
                                        error_def.resolve();
                                    }
                                    error_index++;
                                }
                            });
                            error_def.done(function() {
                                var error = $form.find('.is-invalid').eq(0);
                                $('html, body').animate({
                                    scrollTop: (error.offset().top - 100),
                                }, 500);
                            });
                        } else {
                            var m = res['message'];
                            App.alert(m, 'Oops!');
                        }
                    } else {
                        App.alert(res['message']);
                        setTimeout(function() {
                            window.location.href = App.siteUrl('/admin/admin_users/update_permission/'+id);
                        }, 1500);
                    }

                    $form.find('button[type="submit"]')
                        .text('Save')
                        .attr('disabled', false);
                },
                error: function(e) {
                    App.loading(false);
                    $form.find('button[type="submit"]')
                        .text('Save')
                        .attr('disabled', false);
                    App.alert(e.responseText, 'Oops!');
                }
            });
        });
        $('body').off('click', '[role="access-set-all"]');
        $('body').on('click', '[role="access-set-all"]', function(e) {
            var traget = $(this).attr('target');
            $('.'+traget).attr('checked', 'checked');
        });
        $('body').off('click', '[role="access-reset-all"]');
        $('body').on('click', '[role="access-reset-all"]', function(e) {
            var traget = $(this).attr('target');
            $('.'+traget).attr('checked', false);
        });
    </script>
@stop
