<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <style>
        .json-string {
            white-space: pre-line;
        }
        </style>
    <div style="width: 850px" x-data='{
        get prettyJson() {
            return window.prettyPrint({!! $getState() !!})
        }
    }'>
        <pre class="prettyjson" x-html="prettyJson"></span>
    </div>
</x-dynamic-component>
