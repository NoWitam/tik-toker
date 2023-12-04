<div>
    @foreach ($getState() as $relation)
        <span>
            {{ $relation->name }}
        </span>
    @endforeach
</div>
