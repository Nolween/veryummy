<div {{ $attributes }} class="bg-gray-100 drop-shadow-md rounded-sm mb-5 w-full md:w-3/4 lg:w-2/3 mx-3">
    <div class="flex justify-between">
        <div class="pl-3 text-veryummy-ternary text-4xl">{{ $attributes->get('author')}}</div>
        <div class="pr-3 text-veryummy-secondary text-4xl">{{ \Carbon\Carbon::createFromTimestamp($attributes->get('date'))->format('d/m/Y h:H') }}</div>
    </div>
    <div class="flex justify-between mb-3 pr-3">
        <div class="pl-3 text-veryummy-secondary text-4xl">{{ $attributes->get('name') }}</div>
        <div class="flex justify-end space-x-2">
            <x-fas-check-square class="text-veryummy-primary h-10 w-10 cursor-pointer" />
            <x-fas-window-close class="text-veryummy-ternary h-10 w-10 cursor-pointer" />
        </div>
    </div>

</div>