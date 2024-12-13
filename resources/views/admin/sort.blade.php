@extends("admin.template.layout")

@section("header")
<link href="{{asset('')}}admin-assets/plugins/drag-and-drop/jquery-ui/scrollable/style.css" rel="stylesheet" type="text/css" />
@stop

@section("content")
<div class="card mb-5">

<style>
    .widget-content-area{
        box-shadow: none
        height:10000px;
    }
</style>
    <!-- <div class="card-header"><a href="{{url('admin/category/create')}}" class="btn btn-warning mb-4 mr-2 btn-rounded">Create Category</a></div> -->
    <div class="card-body">
    <div class="widget-content widget-content-area">
            <div class="row">
                <div class="col-lg-6">
                    <div id="sortable" >
                        <ul>
                            @php $c=0; @endphp
                            @foreach($list as $key)
                            @php $c++; @endphp
                            <li data-id="{{$key->id}}"> {{$c}}, {{$key->name}} @if(isset($key->parent) && !empty($key->parent)) / {{$key->parent->name}}  @endif  {{ $key->activity ? ('/ '.$key->activity->name) : ''}} @if(request()->test) {{$key->activity_id ?? ''}} @endif</li>
                            @endforeach
                        </ul>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section("script")
<script src="{{asset('')}}admin-assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<script>
        /* 
            call mCustomScrollbar function before jquery ui sortable()
        */
        var $sortableList = $("#sortable ul");

        $sortableList.sortable({
            cursor:"move",
            stop: function(ev, ui) {
                var formData = new FormData();
                var items = [];
                var children = $('#sortable ul').sortable().children();
                $.each(children, function() {
                    if(jQuery.inArray($(this).data('id'), items) !== -1){}else{items.push($(this).data('id'));}
                        
                });
                
                formData.append('items',items);
                formData.append('_token',"{{ csrf_token() }}");
                App.loading(true);
                $.ajax({
                    type: "POST",
                    enctype: 'multipart/form-data',
                    url: '{{url()->current()}}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    cache: false,
                    dataType:'json',
                    timeout: 600000,
                    success: function (res) {
                        App.loading(false);
                        if ( res['status'] == 0 ) {
                            var m = res['message']||'Unable to sort menu. Please try again later.';
                            App.alert(m, 'Oops!');
                        }
                    	
                        
                        
                   	},
                    error: function (e) {
                        App.loading(false);                       
                        App.alert( e.responseText, 'Oops!');
                    }
                });
            }
        });
        $sortableList.disableSelection();
        $("#sortable, #sortable2").mCustomScrollbar({
            scrollbarPosition:"outside",
            scrollInertia:450,
        });

        // $("#sortable ul, #sortable2 ul").sortable({
        //     axis:"y",
        //     cursor:"move",
        //     tolerance:"intersect",
        //     refreshPositions:true,
        //     change:function(e,ui){
        //         var h=ui.helper.outerHeight(true),
        //             elem=$("#sortable .mCustomScrollBox"),
        //             elemHeight=elem.height(),
        //             moveBy=$("#sortable li").outerHeight(true)*3,
        //             mouseCoordsY=e.pageY-elem.offset().top;
        //         if(mouseCoordsY<h){
        //             $("#sortable").mCustomScrollbar("scrollTo","+="+moveBy);
        //         }else if(mouseCoordsY>elemHeight-h){
        //             $("#sortable").mCustomScrollbar("scrollTo","-="+moveBy);
        //         }
        //     },
        //     stop: function(ev, ui) {
        //         var formData = new FormData();
		// 		var items = [];
		// 	    var children = $('#sortable').sortable('refreshPositions').children();
		// 	    $.each(children, function() {
		// 	        items.push($(this).data('id'));
		// 	    });
        //         formData.append('items',items);
        //         formData.append('_token',"{{ csrf_token() }}");
        //         App.loading(true);
        //         $.ajax({
        //             type: "POST",
        //             enctype: 'multipart/form-data',
        //             url: '{{url()->current()}}',
        //             data: formData,
        //             processData: false,
        //             contentType: false,
        //             cache: false,
        //             dataType:'json',
        //             timeout: 600000,
        //             success: function (res) {
        //                 App.loading(false);
        //                 if ( res['status'] == 0 ) {
        //                     var m = res['message']||'Unable to sort menu. Please try again later.';
        //                     App.alert(m, 'Oops!');
        //                 }
                    	
                        
                        
        //            	},
        //             error: function (e) {
        //                 App.loading(false);                       
        //                 App.alert( e.responseText, 'Oops!');
        //             }
        //         });
			    
		// 	    // $.post('{{url()->current()}}', {
		// 	    // 	'items': items,
        //         //     '_token' : "{{ csrf_token() }}"
		// 	    // }, function(res) {
		// 	    // 	if ( res['status'] == 0 ) {
		// 	    // 		var m = res['message']||'Unable to sort menu. Please try again later.';
        //         //         App.alert(m, 'Oops!');
		// 	    // 	}
		// 	    // });
		// 	}
        // });
    </script>
@stop