<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div x-data='{
        get prettyJson() {
            return window.prettyPrint({!! $getState() !!})
        }
    }'>
        <pre class="prettyjson" x-html="prettyJson"></span>
    </div>
</x-dynamic-component>
