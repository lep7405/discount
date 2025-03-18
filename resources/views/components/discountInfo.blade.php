<ul class="space-y-3">
    <li class="flex items-center space-x-2">
        <span class="text-gray-700 font-medium">{{ $discount->name }}</span>
    </li>
    <li class="flex items-center space-x-2">
        <span class="text-blue-500">
            <i class="fas fa-money-bill-wave w-5 h-5"></i>
        </span>
        <span class="text-gray-700 font-medium">{{ $discount->value }}{{ $discount->type === 'amount' ? ' USD' : '%' }} discount</span>
    </li>
    <li class="flex items-center space-x-2">
        <span class="text-green-500">
            <i class="fas fa-check-circle w-5 h-5"></i>
        </span>
        <span class="text-gray-700">{{ $discount->usage_limit == 0 ? 'Unlimited usage' : $discount->usage_limit . ' times usage' }}</span>
    </li>
    <li class="flex items-center space-x-2">
        <span class="text-purple-500">
            <i class="fas fa-clock w-5 h-5"></i>
        </span>
        <span class="text-gray-700">{{ $discount->trial_days ? $discount->trial_days : 0 }} days trial</span>
    </li>
    <li class="flex items-center space-x-2">
        <span class="text-yellow-500">
            <i class="fas fa-calendar-plus w-5 h-5"></i>
        </span>
        <span class="text-gray-700">Start: {{ $discount->started_at ? formatDate($discount->started_at) : 'N/A' }}</span>
    </li>
    <li class="flex items-center space-x-2">
        <span class="text-red-500">
            <i class="fas fa-calendar-times w-5 h-5"></i>
        </span>
        <span class="text-gray-700">End: {{ $discount->expired_at ? formatDate($discount->expired_at) : 'N/A' }}</span>
    </li>
</ul>
