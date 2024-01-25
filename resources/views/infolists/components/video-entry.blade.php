<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div>
        <video width="360" height="640" controls>
            <source type="video/mp4" src="{{ route('content', ['content' => $getRecord()]) }}">
        </video>
    </div>
</x-dynamic-component>
