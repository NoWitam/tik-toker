<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div x-data="{
        get prettyJson() {
            json = JSON.parse('{{$getState()}}')
            return window.prettyPrint(json)
        }
    }">
        <pre class="prettyjson" x-html="prettyJson"></span>
    </div>
</x-dynamic-component>
