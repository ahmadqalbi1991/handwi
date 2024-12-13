@extends('admin.layouts.master')
@section('headerFiles')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-assets/plugins/table/datatable/datatables.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('admin-assets/plugins/table/datatable/custom_dt_zero_config.css') }}">
        <style type="text/css">
            .notiy_row {
                background-color : #EFB6B7;
            }
        </style>
         <style type="text/css">

        .dropdown-item{
            margin-left:0 !important;
        }
        #datatable-buttons td .user-profile-dropdown .dropdown-menu{
                right: auto !important;
            }
    </style>
     <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
@endsection

@section('content')
    <div class="container">
        <form class="order_table_data mb-4 mt-4" id="sort_form">
        <div class="page-header page-header_custom">
            <div class="page-title">
                <h3>{{ $page_title }}</h3>
            </div>
            <div class="page_header_btns">
                <a href="javascript:history.back()" class="btn btn-secondary gobackbtn"><i class='bx bx-chevron-left'></i> Back</a>
                <input type="submit" class="mt-4 btn-custom" value="Save" style="height: 44px !important; border-radius: 5px !important;">
            </div>
        </div>

        <div class="row layout-spacing ">

            <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                @if (session('message'))
                    <div class="alert alert-success alert-dismissable custom-success-box" style="margin: 15px;">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <strong> {{ session('message') }} </strong>
                    </div>
                @endif

                
                <div class="statbox widget box box-shadow">
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive" style="padding-bottom: 0 !important;">
                            
                                <div class="row align-items-center">

                                    <div class="col-12">
                                        <ul id="sortable">
                                            @foreach($chefs as $val)
                                          <li class="ui-state-default li_detail" detail_id="{{$val->id}}"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>{{$val->name}}</li>
                                          @endforeach
                                          
                                        </ul>
                                    </div>


                                </div>
                          
                            
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
          </form>
      
@endsection

@section('footerJs')
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script>
$( function() {
    $( "#sortable" ).sortable();
    $('#sort_form').on('submit',function(e){
        e.preventDefault();
        var ids = [];
        $('.li_detail').each(function(i,k){
            
            ids.push($(this).attr('detail_id'));
        });
        console.log(ids);
        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: '{{url("admin/chef/savesort")}}',
            data: {
                'details' : ids,
                '_token':'{{csrf_token()}}'
            },
           
            dataType: 'json',
            success: function(res) {
                App.alert(res['message'] || 'Data saved successfully');
            }
        });
    })
  } );
       

    </script>
@endsection
