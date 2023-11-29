<div>
    @foreach ($getState() as $relation)
        <div>
            {{ $relation->name }}
        </div>
    @endforeach
</div>
