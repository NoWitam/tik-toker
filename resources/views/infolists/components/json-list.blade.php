<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div x-data="{
        get prettyJson() {
            json = '{{$getState()}}'
            json = json.replace(/&quot;/g, '\\"')
            json = JSON.parse(json)
            return window.prettyPrint(json)
        }
    }">
        <pre class="prettyjson" x-html="prettyJson"></span>
    </div>
</x-dynamic-component>
