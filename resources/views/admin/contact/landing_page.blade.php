@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/custom_dt_customer.css">
    <script src="https://cdn.jsdelivr.net/npm/tinymce@5.9.1/tinymce.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   
@stop
@section('content')

<div class="card mb-5">
    
    <div class="row card-body">

        <div class="col-xl-12 col-lg-12 col-sm-12">
            @if ( session('success'))
                <div class="alert alert-success alert-dismissable custom-success-box" style="margin: 15px;">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong> {{ session('success') }} </strong>
                </div>
            @endif
            @if ( session('error'))
                <div class="alert alert-danger alert-dismissable custom-danger-box" style="margin: 15px;">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong> {{ session('error') }} </strong>
                </div>
            @endif
            <div class="statbox widget box">
                <div class=" mt-3">
                    <?php

                    $bannerTitleSetting = \App\Models\LandingPageSetting::where('meta_key', 'banner_title')->first();
                    $bannerDescSetting = \App\Models\LandingPageSetting::where('meta_key', 'banner_description')->first();
                    $bannerImageSetting = \App\Models\LandingPageSetting::where('meta_key', 'banner_image')->first();
                    
                    $aboutappTitleSetting = \App\Models\LandingPageSetting::where('meta_key', 'aboutapp_title')->first();
                    $aboutappDescSetting = \App\Models\LandingPageSetting::where('meta_key', 'aboutapp_description')->first();
                    $about_appImageSetting = \App\Models\LandingPageSetting::where('meta_key', 'about_app')->first();
                    $about_app_image_2ImageSetting = \App\Models\LandingPageSetting::where('meta_key', 'about_app_image_2')->first();

                    $groceriesTitleSetting = \App\Models\LandingPageSetting::where('meta_key', 'groceries_title')->first();
                    $groceriesDescSetting = \App\Models\LandingPageSetting::where('meta_key', 'groceries_description')->first();
                    $groceries_imageImageSetting = \App\Models\LandingPageSetting::where('meta_key', 'groceries_image')->first();

                    $speedyfood_titleSetting = \App\Models\LandingPageSetting::where('meta_key', 'speedyfood_title')->first();
                    $speedyfood_descriptionSetting = \App\Models\LandingPageSetting::where('meta_key', 'speedyfood_description')->first();
                    $speedyfoodimage_imageSetting = \App\Models\LandingPageSetting::where('meta_key', 'speedyfoodimage_image')->first();


                    $stop_there_descriptionSetting = \App\Models\LandingPageSetting::where('meta_key', 'stop_there_description')->first();
                    $stop_there_titleSetting = \App\Models\LandingPageSetting::where('meta_key', 'stop_there_title')->first();
                    $stop_there_image_imageSetting = \App\Models\LandingPageSetting::where('meta_key', 'stop_there_image')->first();

                    $customer_support_descriptionSetting = \App\Models\LandingPageSetting::where('meta_key', 'customer_support_description')->first();
                    $customer_support_titleSetting = \App\Models\LandingPageSetting::where('meta_key', 'customer_support_title')->first();
                    $customer_support_image_imageSetting = \App\Models\LandingPageSetting::where('meta_key', 'customer_support_image')->first();

                    $features_descriptionSetting = \App\Models\LandingPageSetting::where('meta_key', 'features_description')->first();
                    $features_titleSetting = \App\Models\LandingPageSetting::where('meta_key', 'features_title')->first();
                    $features_image_imageSetting = \App\Models\LandingPageSetting::where('meta_key', 'features_image')->first();

                    $how_it_work_1_descriptionSetting = \App\Models\LandingPageSetting::where('meta_key', 'how_it_work_1_description')->first();
                    $how_it_work_1_titleSetting = \App\Models\LandingPageSetting::where('meta_key', 'how_it_work_1_title')->first();
                    $how_it_work_1_imageSetting = \App\Models\LandingPageSetting::where('meta_key', 'how_it_work_1_image')->first();

                    $how_it_work_2_descriptionSetting = \App\Models\LandingPageSetting::where('meta_key', 'how_it_work_2_description')->first();
                    $how_it_work_2_titleSetting = \App\Models\LandingPageSetting::where('meta_key', 'how_it_work_2_title')->first();
                    $how_it_work_2_imageSetting = \App\Models\LandingPageSetting::where('meta_key', 'how_it_work_2_image')->first();

                    $how_it_work_3_descriptionSetting = \App\Models\LandingPageSetting::where('meta_key', 'how_it_work_3_description')->first();
                    $how_it_work_3_titleSetting = \App\Models\LandingPageSetting::where('meta_key', 'how_it_work_3_title')->first();
                    $how_it_work_3_imageSetting = \App\Models\LandingPageSetting::where('meta_key', 'how_it_work_3_image')->first();

                     $lets_download_app_descriptionSetting = \App\Models\LandingPageSetting::where('meta_key', 'lets_download_app_description')->first();
                    $lets_download_app_titleSetting = \App\Models\LandingPageSetting::where('meta_key', 'lets_download_app_title')->first();
                    $lets_download_app_imageSetting = \App\Models\LandingPageSetting::where('meta_key', 'lets_download_app_image')->first();
                    $lets_download_app_imageSetting1 = \App\Models\LandingPageSetting::where('meta_key', 'lets_download_app_image1')->first();
                    ?>
                 <form method="post" id="admin-form" action="{{ route('admin.landing_page_setting_store') }}" enctype="multipart/form-data">
                    @csrf()

                    <div class="row">
                    
                        <div class="col-md-6 form-group">
                            <label>Banner Image</label>
                          
                            <input type="file" name="banner_image" class="form-control" accept="image/*">
                              @if ($bannerImageSetting)
                            <img src="{{$bannerImageSetting->meta_value}}" alt="Banner Image" style="height: 100px; width:100px;">
                            @endif
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Banner Title </label>
                            <input type="text" name="banner_title" class="form-control jqv-input" value="{{ $bannerTitleSetting ? $bannerTitleSetting->meta_value : '' }}" placeholder="Banner Title">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Banner Description </label>
                            <textarea class="form-control description" name="banner_description" placeholder="Banner Description">{{ $bannerDescSetting ? $bannerDescSetting->meta_value : '' }}</textarea>
                        </div>
                    </div>

                      <div class="row">
                    
                        <div class="col-md-6 form-group">
                            <label>About App Image</label>
                          
                            <input type="file" name="about_app" class="form-control" accept="image/*">
                              @if ($about_appImageSetting)
                            <img src="{{$about_appImageSetting->meta_value}}" alt="Banner Image" style="height: 100px; width:100px;">
                            @endif
                        </div>
                         <div class="col-md-6 form-group">
                            <label>About App Image 2</label>
                           
                            <input type="file" name="about_app_image_2" class="form-control" accept="image/*">
                             @if ($about_app_image_2ImageSetting)
                            <img src="{{$about_app_image_2ImageSetting->meta_value}}" alt="Banner Image" style="height: 100px; width:100px;">
                            @endif
                        </div>
                        <div class="col-md-6 form-group">
                            <label>About App Title </label>
                            <input type="text" name="aboutapp_title" class="form-control jqv-input" value="{{ $aboutappTitleSetting ? $aboutappTitleSetting->meta_value : '' }}" placeholder="About App Title">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>About App Description </label>
                            <textarea class="form-control description" name="aboutapp_description" placeholder="About App Description">{{ $aboutappDescSetting ? $aboutappDescSetting->meta_value : '' }}</textarea>
                        </div>
                    </div>

                     <div class="row">
                    
                        <div class="col-md-6 form-group">
                            <label>Groceries Image</label>
                           
                            <input type="file" name="groceries_image" class="form-control" accept="image/*">
                             @if ($groceries_imageImageSetting)
                            <img src="{{$groceries_imageImageSetting->meta_value}}" alt="Banner Image" style="height: 100px; width:100px;">
                            @endif
                        </div>
                         
                        <div class="col-md-6 form-group">
                            <label>Groceries Title </label>
                            <input type="text" name="groceries_title" class="form-control jqv-input" value="{{ $groceriesTitleSetting ? $groceriesTitleSetting->meta_value : '' }}" placeholder="Groceries Title">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Groceries Description </label>
                            <textarea class="form-control description" name="groceries_description" placeholder="Groceries Description">{{ $groceriesDescSetting ? $groceriesDescSetting->meta_value : '' }}</textarea>
                        </div>
                    </div>
                     <div class="row">
                    
                        <div class="col-md-6 form-group">
                            <label>Speedy Food Image</label>
                           
                            <input type="file" name="speedyfoodimage_image" class="form-control" accept="image/*">
                             @if ($speedyfoodimage_imageSetting)
                            <img src="{{$speedyfoodimage_imageSetting->meta_value}}" alt="Banner Image" style="height: 100px; width:100px;">
                            @endif
                        </div>
                         
                        <div class="col-md-6 form-group">
                            <label>Speedy Food Title </label>
                            <input type="text" name="speedyfood_title" class="form-control jqv-input" value="{{ $speedyfood_titleSetting ? $speedyfood_titleSetting->meta_value : '' }}" placeholder="Speedy Food Title">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Speedy Food Description </label>
                            <textarea class="form-control description" name="speedyfood_description" placeholder="Speedy Food Description">{{ $speedyfood_descriptionSetting ? $speedyfood_descriptionSetting->meta_value : '' }}</textarea>
                        </div>
                    </div>
                       <div class="row">
                    
                        <div class="col-md-6 form-group">
                            <label>Stop There Image</label>
                           
                            <input type="file" name="stop_there_image" class="form-control" accept="image/*">
                             @if ($stop_there_image_imageSetting)
                            <img src="{{$stop_there_image_imageSetting->meta_value}}" alt="Banner Image" style="height: 100px; width:100px;">
                            @endif
                        </div>
                         
                        <div class="col-md-6 form-group">
                            <label>Stop There Title </label>
                            <input type="text" name="stop_there_title" class="form-control jqv-input" value="{{ $stop_there_titleSetting ? $stop_there_titleSetting->meta_value : '' }}" placeholder="Stop There Title">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Stop There Description </label>
                            <textarea class="form-control description" name="stop_there_description" placeholder="Stop There Description">{{ $stop_there_descriptionSetting ? $stop_there_descriptionSetting->meta_value : '' }}</textarea>
                        </div>
                    </div>


                     <div class="row">
                    
                        <div class="col-md-6 form-group">
                            <label>Customer Support Image</label>
                          
                            <input type="file" name="customer_support_image" class="form-control" accept="image/*">
                              @if ($customer_support_image_imageSetting)
                            <img src="{{$customer_support_image_imageSetting->meta_value}}" alt="Banner Image" style="height: 100px; width:100px;">
                            @endif
                        </div>
                         
                        <div class="col-md-6 form-group">
                            <label>Customer Support Title </label>
                            <input type="text" name="customer_support_title" class="form-control jqv-input" value="{{ $customer_support_titleSetting ? $customer_support_titleSetting->meta_value : '' }}" placeholder="Customer Support Title">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Stop There Description </label>
                            <textarea class="form-control description" name="customer_support_description" placeholder="Customer Support Description">{{ $customer_support_descriptionSetting ? $customer_support_descriptionSetting->meta_value : '' }}</textarea>
                        </div>
                    </div>


                      <div class="row">
                    
                        <div class="col-md-6 form-group">
                            <label>Features  Image</label>
                          
                            <input type="file" name="features_image" class="form-control" accept="image/*">
                              @if ($features_image_imageSetting)
                            <img src="{{$features_image_imageSetting->meta_value}}" alt="Banner Image" style="height: 100px; width:100px;">
                            @endif
                        </div>
                         
                        <div class="col-md-6 form-group">
                            <label>Features  Title </label>
                            <input type="text" name="features_title" class="form-control jqv-input" value="{{ $features_titleSetting ? $features_titleSetting->meta_value : '' }}" placeholder="Features Title">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Features  Description </label>
                            <textarea class="form-control description" name="features_description" placeholder="Features Description">{{ $features_descriptionSetting ? $features_descriptionSetting->meta_value : '' }}</textarea>
                        </div>
                    </div>


                       <div class="row">
                    
                        <div class="col-md-6 form-group">
                            <label>How it works Step 1  Image</label>
                           
                            <input type="file" name="how_it_work_1_image" class="form-control" accept="image/*">
                             @if ($how_it_work_1_imageSetting)
                            <img src="{{$how_it_work_1_imageSetting->meta_value}}" alt="Banner Image" style="height: 100px; width:100px;">
                            @endif
                        </div>
                         
                        <div class="col-md-6 form-group">
                            <label>How it works Step 1  Title </label>
                            <input type="text" name="how_it_work_1_title" class="form-control jqv-input" value="{{ $how_it_work_1_titleSetting ? $how_it_work_1_titleSetting->meta_value : '' }}" placeholder="How it works Step 1 Title">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>How it works Step 1  Description </label>
                            <textarea class="form-control description" name="how_it_work_1_description" placeholder="How it works Step 1 Description">{{ $how_it_work_1_descriptionSetting ? $how_it_work_1_descriptionSetting->meta_value : '' }}</textarea>
                        </div>
                    </div>

                     <div class="row">
                    
                        <div class="col-md-6 form-group">
                            <label>How it works Step 2  Image</label>
                          
                            <input type="file" name="how_it_work_2_image" class="form-control" accept="image/*">
                              @if ($how_it_work_2_imageSetting)
                            <img src="{{$how_it_work_2_imageSetting->meta_value}}" alt="Banner Image" style="height: 100px; width:100px;">
                            @endif
                        </div>
                         
                        <div class="col-md-6 form-group">
                            <label>How it works Step 2  Title </label>
                            <input type="text" name="how_it_work_2_title" class="form-control jqv-input" value="{{ $how_it_work_2_titleSetting ? $how_it_work_2_titleSetting->meta_value : '' }}" placeholder="How it works Step 1 Title">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>How it works Step 1  Description </label>
                            <textarea class="form-control description" name="how_it_work_2_description" placeholder="How it works Step 2 Description">{{ $how_it_work_2_descriptionSetting ? $how_it_work_2_descriptionSetting->meta_value : '' }}</textarea>
                        </div>
                    </div>
                     <div class="row">
                    
                        <div class="col-md-6 form-group">
                            <label>How it works Step 3  Image</label>
                           
                            <input type="file" name="how_it_work_3_image" class="form-control" accept="image/*">
                             @if ($how_it_work_3_imageSetting)
                            <img src="{{$how_it_work_3_imageSetting->meta_value}}" alt="Banner Image" style="height: 100px; width:100px;">
                            @endif
                        </div>
                         
                        <div class="col-md-6 form-group">
                            <label>How it works Step 3  Title </label>
                            <input type="text" name="how_it_work_3_title" class="form-control jqv-input" value="{{ $how_it_work_3_titleSetting ? $how_it_work_3_titleSetting->meta_value : '' }}" placeholder="How it works Step 3 Title">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>How it works Step 3  Description </label>
                            <textarea class="form-control description" name="how_it_work_3_description" placeholder="How it works Step 3 Description">{{ $how_it_work_3_descriptionSetting ? $how_it_work_3_descriptionSetting->meta_value : '' }}</textarea>
                        </div>
                    </div>

                     <div class="row">
                    
                        <div class="col-md-6 form-group">
                            <label>Lets Download App  Image</label>
                         
                            <input type="file" name="lets_download_app_image" class="form-control" accept="image/*">
                               @if ($lets_download_app_imageSetting)
                            <img src="{{$lets_download_app_imageSetting->meta_value}}" alt="Banner Image" style="height: 100px; width:100px;">
                            @endif
                        </div>
                         <div class="col-md-6 form-group">
                            <label>Lets Download App  Image 2</label>
                          
                            <input type="file" name="lets_download_app_image1" class="form-control" accept="image/*">
                              @if ($lets_download_app_imageSetting1)
                            <img src="{{$lets_download_app_imageSetting1->meta_value}}" alt="Banner Image" style="height: 100px; width:100px;">
                            @endif
                        </div>
                         
                        <div class="col-md-6 form-group">
                            <label>Lets Download App  Title </label>
                            <input type="text" name="lets_download_app_title" class="form-control jqv-input" value="{{ $lets_download_app_titleSetting ? $lets_download_app_titleSetting->meta_value : '' }}" placeholder="Lets Download App Title">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Lets Download App  Description </label>
                            <textarea class="form-control description" name="lets_download_app_description" placeholder="Lets Download App Description">{{ $lets_download_app_descriptionSetting ? $lets_download_app_descriptionSetting->meta_value : '' }}</textarea>
                        </div>
                    </div>


                    <div class="form-group">
                        <button type="submit" class="mt-4 btn btn-primary">Save</button>
                    </div>
                </form>



                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')

<script>
    $(document).ready(function() {
     tinymce.init({
        selector: 'textarea.description',
        plugins: 'code',
        toolbar: 'undo redo | formatselect | bold italic | code',
        height: 300,
        setup: function (editor) {
            editor.ui.registry.addButton('code', {
                icon: 'sourcecode',
                tooltip: 'Insert Code',
                onAction: function (_) {
                    editor.windowManager.open({
                        title: 'Insert Code',
                        body: {
                            type: 'panel',
                            items: [
                            {
                                type: 'textarea',
                                name: 'code',
                                label: 'Code',
                                multiline: true,
                                minHeight: 200
                            }
                            ]
                        },
                        buttons: [
                        {
                            type: 'cancel',
                            text: 'Cancel'
                        },
                        {
                            type: 'submit',
                            text: 'Insert',
                            primary: true
                        }
                        ],
                        onSubmit: function (api) {
                            var code = api.getData().code;
                            editor.insertContent('<pre><code>' + code + '</code></pre>');
                            api.close();
                        }
                    });
                }
            });
        }
    });

 });
</script>

@endsection