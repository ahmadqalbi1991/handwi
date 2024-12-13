@if ($paginator->hasPages())
<ul class="pagination">
       
        @if ($paginator->onFirstPage())
            <li class="paginate_button page-item previous disabled" id="example2_previous">
                <a href="#" aria-controls="example2" data-dt-idx="0" tabindex="0" class="page-link">Previous</a>
            </li>
        @else
            <li class="paginate_button page-item previous" id="example2_previous">
                <a href="{{ $paginator->previousPageUrl() }}" aria-controls="example2" data-dt-idx="0" tabindex="0" class="page-link">Previous</a>
            </li>
        @endif

        <?php 
        $apString = "";
        if(!empty($_GET)) { 
            
            foreach ($_GET as $key => $value) {
                $apString .= "&".$key."=".$value; 
            }
        }
        
        ?>
      
        @foreach ($elements as $element)
           
            @if (is_string($element))
                <li class="disabled"><span>{{ $element }}</span></li>
            @endif


           
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="paginate_button page-item active">
                            <a href="#" aria-controls="example2" data-dt-idx="{{ $page }}" tabindex="0" class="page-link">{{ $page }}</a>
                        </li>
                    @else
                        <li class="paginate_button page-item">
                            <a href="{{ $url.$apString }}" aria-controls="example2" data-dt-idx="{{ $page }}" tabindex="0" class="page-link">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach


        
        @if ($paginator->hasMorePages())
            <li class="paginate_button page-item next" id="example2_next">
                <a href="{{ $paginator->nextPageUrl().$apString }}" aria-controls="example2" data-dt-idx="2" tabindex="0" class="page-link">Next</a>
            </li>
        @else
            <li class="paginate_button page-item next disabled" id="example2_next">
                <a href="#" aria-controls="example2" data-dt-idx="2" tabindex="0" class="page-link">Next</a>
            </li>
        @endif
    </ul>
@endif 