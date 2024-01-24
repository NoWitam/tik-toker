<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <style>
        .json-string {
            white-space: pre-line;
        }
    </style>

    @php 
        $json = ( (is_string($getState()) AND !json_validate($getState())) OR !is_string($getState())) ? json_encode($getState()) : $getState();
    @endphp

    <div style="width: 850px" x-data='{
        get prettyJson() {
            console.log({{$json}})
            return window.prettyPrint({{$json}})
        }
    }'>
        <pre style="max-height: 600px;" class="prettyjson" x-html="prettyJson"></span>
    </div>
</x-dynamic-component>
