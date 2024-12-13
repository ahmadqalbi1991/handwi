@extends("admin.template.layout")

@section("header")
    <style>
        tr{
            cursor: move;
        }
    </style>
@stop


@section("content")
<div class="card mb-5">
    <div class="card-header"></div>
    <div class="card-body">
    <div class="dataTables_wrapper container-fluid dt-bootstrap4">
   

    <div class="row">
        
    </div>
    @if($list->count() > 0)
        <table class="table table-condensed table-striped" id="banners">
            <thead>
                <tr>
                    <th>#</th>
                    
                    <th >Title</th>
                    <th>Status</th>
                    <th>Type</th>
                    <!--<th></th>-->
                </tr>
            </thead>
            <tbody id="sortable">
            <?php $i = 0; ?>
            @foreach($list as $item)
            <?php $i++; ?>
               <tr data-id="{{$item->id}}">
                   <td>{{$i}}</td>
                   
                   
                   <td>{{$item->title}}</td>
                   <td>
                        <label class="switch s-icons s-outline  s-outline-warning mt-2 mb-2 mr-2">
                            <input type="checkbox" class="change_status" data-id="{{ $item->id }}"
                                data-url="{{ url('admin/home_section/change_status') }}"
                                @if ($item->status) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </td>
                    <td>{{$item->type}}</td>
                    <!--<td>-->
                    <!--    <a class="dropdown-item" data-role="unlink"-->
                    <!--                            data-message="Do you want to remove this item?"-->
                    <!--                            href="{{ url('admin/home_section/delete/' . $item->id) }}"><i-->
                    <!--                                class="flaticon-delete-1"></i> Delete</a>-->
                    <!--</td>-->
               </tr>
            @endforeach
            </tbody>
        </table>
       
            
            
        
        @else
        <br>
        <div class="alert alert-warning">
            <p>No banner found</p>
        </div>
        @endif
    </div>
    </div>
</div>
@stop

@section("script")
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
$(document).ready(function () {
    $("#sortable").sortable({
        update: function (event, ui) {
            var sortedIDs = $("#sortable tr").map(function() {
                    return $(this).data("id");
                }).get();

            $.ajax({
                url: "{{route('admin.home_section_update')}}",
                type: "POST",
                dataType: "json",
                data: {
                    sortedIDs: sortedIDs,
                    '_token':"{{csrf_token()}}"
                },
                success: function (response) {
                    console.log(response);
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }
    });
    $("#sortable").disableSelection();
});
    
</script>
@stop