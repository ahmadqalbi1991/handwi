@extends('admin.layouts.master')
@section('headerFiles')
    
    <style type="text/css">
        .nav.profile-navbar .nav-item{
width:100%
        }
        .nav.profile-navbar  .nav-link {
            display: block;
            padding: 0.5rem 1rem;
            font-weight: 700;
            background: #ca2124;
            color: white;
        }
       
       table  a img{
            width: 100px !important;
            padding: 5px;
            border: 1px solid #ccc;
            height: 100px !important;
            border-radius: 5px;
            object-fit: contain;
        }
        .fancybox__content{
            max-width: 1000px !important;
            max-height: 600px !important;
        }
        .tag-doc.btn.btn-primary.btn-success {
            margin-top: 0px !important;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                
                <h3>{{ $page_heading }}</h3>
            </div>
            <a href="javascript:history.back()"><button class="btn btn-secondary gobackbtn"><i class='bx bx-chevron-left'></i> Back</button></a>
        </div>

        <div class="row layout-spacing ">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12  layout-spacing">
                <div class="statbox widget box box-shadow">
                    <div class="widget-content widget-content-area" style="height: inherit">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-6 mb-4 align-self-center">
                                                @if($restaurent->cover_image !=null)
                                                <img src="@if(isset($restaurent->cover_image)){{$restaurent->cover_image}} @endif" style="width:100%;max-height:460px;height:100%;">
                                                @endif
                                            </div>
                                            <div class="col-lg-6 mb-4">
                                                <div class="border-bottom text-center pb-4">
                                                    @if($customer->image!=null)
                                                    <img id="logo-preview" class="img-lg rounded-circle mb-3"
                                                        src="{{$customer->image}}" width="100" height="100">
                                                        @endif
                                                    <div class="mb-3">
                                                        <strong>{{$customer->name}}</strong><br><br>
                                                        <span class="mb-0 mr-2 text-muted">{{$customer->email}}</span><br>
                                                        <span class="mb-0 mr-2 text-muted">{{$customer->dial_code.$customer->phone_number}}</span><br>
                                                        <span class="mb-0 mr-2 text-muted">{{$customer->location}}</span><br>
                                                        <span class="mb-0 mr-2 text-muted">
                                                            <a class="btn btn-secondary mt-2" href="#!" data-toggle="modal" data-target="#viewlocation">View Location</a>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="py-4">
                                                    
                                                    
                                                    <p class="clearfix">
                                                        <span class="float-left">
                                                        Brand name
                                                        </span>
                                                        <span class="float-right text-muted">
                                                            {{$restaurent->brand_name}} 
                                                        </span>
                                                    </p>
                                                    <p class="clearfix">
                                                        <span class="float-left">
                                                        Food Preperation Time
                                                        </span>
                                                        <span class="float-right text-muted">
                                                            {{$restaurent->preparation_unit.' '.$restaurent->preparation_time}} 
                                                        </span>
                                                    </p>
                                                     @php $config = \App\Models\Config::get(['tax_rate','delivery_fee','admin_commission'])->first(); @endphp
                                                    <p class="clearfix">
                                                        <span class="float-left">
                                                        Commission (%)
                                                        </span>
                                                        <span class="float-right text-muted">
                                                            @if($customer->commission>0){{$customer->commission}}@else{{$config->admin_commission}}@endif
                                                        </span>
                                                    </p>
                                                     <p class="clearfix">
                                                        <span class="float-left">
                                                        Delivery Fee
                                                        </span>
                                                        <span class="float-right text-muted">
                                                            @if($customer->delivery_fee > 0 ){{$customer->delivery_fee}}@else{{$config->delivery_fee}}  @endif
                                                        </span>
                                                    </p>
                                                    <p class="clearfix">
                                                        <span class="float-left">
                                                        Start Time
                                                        </span>
                                                        <span class="float-right text-muted">
                                                            {{date('h:i A', strtotime($customer->start_time))}} 
                                                        </span>
                                                    </p>
                                                    <p class="clearfix">
                                                        <span class="float-left">
                                                        End Time
                                                        </span>
                                                        <span class="float-right text-muted">
                                                            {{date('h:i A', strtotime($customer->end_time))}} 
                                                        </span>
                                                    </p>
                                                    <p class="clearfix">
                                                        <span class="float-left">
                                                            City
                                                        </span>
                                                        <span class="float-right text-muted">
                                                            @if($customer->city)
                                                                {{$customer->city->city_name_en}}
                                                            @endif 
                                                            @if($customer->country)
                                                                , {{$customer->country->country_name}}
                                                            @endif 
                                                            </span>
                                                    </p>
                                                </div>
                                                <a href="{{route('admin.chef.edit',$customer->id)}}"
                                                    class="btn btn-primary btn-block mb-2">Edit</a>
                                            </div>
                                        </div>
                                        <div class="row border-bottom">
                                            <!-- Left Bar -->
                                            


                                              
                                                <!-- <div class="row"> -->
                                                    <div class="col-lg-6 ">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <div class="mb-4 py-2 ">
                                                                    <ul class="nav profile-navbar d-flex justify-content-between">
                                                                        <li class="nav-item">
                                                                            <a class="nav-link"
                                                                                href="{{route('admin.address_list',$customer->id)}}">
                                                                                <i class="mdi mdi-animation"></i>
                                                                                Bank Details
                                                                            </a>
                                                                        </li>
                                                                        
                                                                        
                                                                        
                                                                    </ul>
                                                                    <table class="table" >
                                                                        <tr>
                                                                            <td>Bank</td>
                                                                            <td>@if($customer->bank){{ $customer->bank->name_en }}@endif</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Account number</td>
                                                                            <td>{{ $customer->account_no }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>IBAN</td>
                                                                            <td>{{ $customer->ifsc }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Swift Code</td>
                                                                            <td>{{ $customer->swift }}</td>
                                                                        </tr>
                                                                                                                            
                                                                        <tr>
                                                                            <td>Branch</td>
                                                                            <td>{{ $customer->bank_branch }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Beneficiary Name</td>
                                                                            <td>{{ $customer->benificiary }}</td>
                                                                        </tr>
                                                                    </table>
                                                                </div>

                                                            </div>    
                                                        </div>
                                                    </div>
                                            
                                                <!-- <div class="row"> -->
                                                    <!-- <div class="col-lg-6 mb-4 py-2 border-bottom">
                                                        <ul class="nav profile-navbar d-flex justify-content-between">
                                                            <li class="nav-item">
                                                                <a class="nav-link"
                                                                    href="javascript:void(0">
                                                                    <i class="mdi mdi-animation"></i>
                                                                    Chef Timing
                                                                </a>
                                                            </li>
                                                            <table class="table" >
                                                            @php 
                                                            $days = config('global.days');  @endphp
                                                            @foreach($days as $key => $val)
                                                                @php $st = $key.'_from'; $ed = $key.'_to';  @endphp
                                                                <tr>
                                                                    <td>{{ucfirst($val)}}
                                                                    </td>
                                                                    <td>@if($restaurent->$st!='00:00'){{date('h:i A',strtotime($restaurent->$st))}}@endif</td>
                                                                    <td>@if($restaurent->$ed!='00:00'){{date('h:i A',strtotime($restaurent->$ed))}}@endif</td>
                                                                </tr>
                                                            @endforeach
                                                        </table>
                                                            
                                                        </ul>
                                                    </div>
                                                     -->
                                                    <div class="col-lg-6 mb-4 py-2 ">
                                                        <ul class="nav profile-navbar d-flex justify-content-between">
                                                            <li class="nav-item">
                                                                <a class="nav-link"
                                                                    href="{{route('admin.address_list',$customer->id)}}">
                                                                    <i class="mdi mdi-animation"></i>
                                                                Documents
                                                                </a>
                                                            </li>
                                                        <table class="table" >
                                                                <tr>
                                                                    <td>Emirates Id</td>
                                                                    <td class="d-flex align-items-center">
                                                                        <!-- {!! previewDocuments($customer->emirates_id) !!}  -->
                                                                        @if($customer->emirates_id)
                                                                            {!! viewDocument($customer->emirates_id) !!}
                                                                            {!! downloadDocument($customer->emirates_id) !!}
                                                                        @endif
                                                                        <!-- <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px; background-color: #DE6E4B !important; border-color: #DE6E4B !important;" data-fancybox data-src="#!" href="#!">
                                                                        <i class="fa fa-eye"></i> View</a> 
                                                                        <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px;" href="#!" target="_blank" download>
                                                                        <i class="fa fa-download"></i> Download</a></td> -->
                                                                </tr> 
                                                                <tr>
                                                                    <td>Passport</td>
                                                                    <td class="d-flex align-items-center">
                                                                        <!-- {!! previewDocuments($customer->passport_id) !!}  -->
                                                                        @if($customer->passport_id)
                                                                            {!! viewDocument($customer->passport_id) !!}
                                                                            {!! downloadDocument($customer->passport_id) !!}
                                                                        @endif
                                                                        <!-- <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px; background-color: #DE6E4B !important; border-color: #DE6E4B !important;" data-fancybox data-src="#!" href="#!"><i class="fa fa-eye"></i> View</a> 
                                                                        <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px;" href="#!" target="_blank" download><i class="fa fa-download"></i> Download</a></td> -->
                                                                </tr>
                                                                <tr>
                                                                    <td>Trade License</td>
                                                                    <td class="d-flex align-items-center">
                                                                        <!-- {!! previewDocuments($customer->trade_license) !!}  -->
                                                                        @if($customer->trade_license)
                                                                            {!! viewDocument($customer->trade_license) !!}
                                                                            {!! downloadDocument($customer->trade_license) !!}
                                                                        @endif
                                                                        <!-- <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px; background-color: #DE6E4B !important; border-color: #DE6E4B !important;" data-fancybox data-src="#!" href="#!"><i class="fa fa-eye"></i> View</a> <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px;" href="#!" target="_blank" download><i class="fa fa-download"></i> Download</a></td> -->
                                                                </tr> 
                                                                <tr>
                                                                    <td>Bank Account Proof</td>
                                                                    <td class="d-flex align-items-center">
                                                                        <!-- {!! previewDocuments($customer->bank_account_proof) !!}  -->
                                                                        @if($customer->bank_account_proof)
                                                                            {!! viewDocument($customer->bank_account_proof) !!}
                                                                            {!! downloadDocument($customer->bank_account_proof) !!}
                                                                        @endif
                                                                        <!-- <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px; background-color: #DE6E4B !important; border-color: #DE6E4B !important;" data-fancybox data-src="#!" href="#!"><i class="fa fa-eye"></i> View</a> <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px;" href="#!" target="_blank" download><i class="fa fa-download"></i> Download</a></td> -->
                                                                </tr>
                                                                <tr>
                                                                    <td>Residency Visa</td>
                                                                    <td class="d-flex align-items-center">
                                                                        <!-- {!! previewDocuments($customer->visa_copy) !!}  -->
                                                                        @if($customer->visa_copy)
                                                                            {!! viewDocument($customer->visa_copy) !!}
                                                                            {!! downloadDocument($customer->visa_copy) !!}
                                                                        @endif
                                                                        <!-- <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px; background-color: #DE6E4B !important; border-color: #DE6E4B !important;" data-fancybox data-src="#!" href="#!"><i class="fa fa-eye"></i> View</a> <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px;" href="#!" target="_blank" download><i class="fa fa-download"></i> Download</a></td> -->
                                                                </tr>                                                                
                                                        </table>

                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="row d-none">
                                                <div class="col-12 mb-4 py-2 border-bottom">
                                                    <ul class="nav profile-navbar d-flex justify-content-between">
                                                        <li class="nav-item">
                                                            <a class="nav-link"
                                                                href="{{route('admin.address_list',$customer->id)}}">
                                                                <i class="mdi mdi-animation"></i>
                                                               Documents
                                                            </a>
                                                        </li>
                                                       <table class="table" >
                                                            <tr>
                                                                <td>Emirates Id</td>
                                                                <td class="d-flex align-items-center">{!! previewDocuments($customer->emirates_id) !!} <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px; background-color: #DE6E4B !important; border-color: #DE6E4B !important;" data-fancybox data-src="#!" href="#!"><i class="fa fa-eye"></i> View</a> <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px;" href="#!" target="_blank" download><i class="fa fa-download"></i> Download</a></td>
                                                            </tr> 
                                                            <tr>
                                                                <td>Passport</td>
                                                                <td class="d-flex align-items-center">{!! previewDocuments($customer->passport_id) !!} <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px; background-color: #DE6E4B !important; border-color: #DE6E4B !important;" data-fancybox data-src="#!" href="#!"><i class="fa fa-eye"></i> View</a> <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px;" href="#!" target="_blank" download><i class="fa fa-download"></i> Download</a></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Trade License</td>
                                                                <td class="d-flex align-items-center">{!! previewDocuments($customer->trade_license) !!} <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px; background-color: #DE6E4B !important; border-color: #DE6E4B !important;" data-fancybox data-src="#!" href="#!"><i class="fa fa-eye"></i> View</a> <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px;" href="#!" target="_blank" download><i class="fa fa-download"></i> Download</a></td>
                                                            </tr> 
                                                            <tr>
                                                                <td>Bank Account Proof</td>
                                                                <td class="d-flex align-items-center">{!! previewDocuments($customer->bank_account_proof) !!} <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px; background-color: #DE6E4B !important; border-color: #DE6E4B !important;" data-fancybox data-src="#!" href="#!"><i class="fa fa-eye"></i> View</a> <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px;" href="#!" target="_blank" download><i class="fa fa-download"></i> Download</a></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Visa</td>
                                                                <td class="d-flex align-items-center">{!! previewDocuments($customer->visa_copy) !!} <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px; background-color: #DE6E4B !important; border-color: #DE6E4B !important;" data-fancybox data-src="#!" href="#!"><i class="fa fa-eye"></i> View</a> <a class="tag-doc btn btn-primary btn-success" style="margin: 0 5px; height: 35px;" href="#!" target="_blank" download><i class="fa fa-download"></i> Download</a></td>
                                                            </tr>                                                                
                                                       </table>

                                                    </ul>
                                                </div>
                                            </div>

                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>


    <!-- Modal -->
<div class="modal fade" id="viewlocation" tabindex="-1" role="dialog" aria-labelledby="viewlocationLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content overflow-hidden">
      <div class="modal-header">
        <h5 class="modal-title" id="viewlocationLabel">View Location</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0 overflow-hidden">
     <div id="my_map_add" style="border:0; width:100%; height: 450px;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('footerJs')
<script type="text/javascript">
function my_map_add() {
var myMapCenter = new google.maps.LatLng({{$customer->latitude??25.193437221217184}}, {{$customer->longitude??55.29526081705425}});
var myMapProp = {center:myMapCenter, zoom:12, scrollwheel:false, draggable:false, mapTypeId:google.maps.MapTypeId.ROADMAP};
var map = new google.maps.Map(document.getElementById("my_map_add"),myMapProp);
var marker = new google.maps.Marker({position:myMapCenter});
marker.setMap(map);
}
</script>

<script src="https://maps.googleapis.com/maps/api/js?key={{Config('global.google_key')}}&callback=my_map_add"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            });
            data = $('#datatable-buttons').DataTable({
                "processing": true,
                "serverSide": true,
                "order": [[0, 'desc']],
                dom: 'Brtip',
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                buttons: [
                ],
                "ajax": {
                    "url": "{{ route('admin.customers.getcutomertop_orders') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function(d) {
                        if ($('#customer_id').val() != '') {
                            d['customer_id'] = "{{$customer->id}}";
                        }
                    }
                },
                "columns": [{
                        "data": "id"
                    },
                    {
                        "data": "order_number"
                    },
                    {
                        "data": "order_status"
                    },
                    {
                        "data": "grand_total"
                    },
                    {
                        "data": "created_at"
                    },
                    {
                        "data": "action"
                    }

                ]

            });
        });

        function applyParams() {
            data.ajax.reload();
        }

        function formReset() {
            $('#state_id').val('').trigger("change");
            $('#ownership_id').val('').trigger("change");
            $('#name').val('');
            data.ajax.reload();
        }
    </script>
@endsection
