<ul class="space-y-3">
    <li class="flex items-center space-x-2">
        <span class="text-gray-700 font-medium">{{ $data['name'] }}</span>
    </li>
    <li class="flex items-center space-x-2">
        <span class="text-blue-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </span>
        <span class="text-gray-700 font-medium">{{ $data['value'] }}{{ $data['type'] === 'amount' ? ' USD' : '%' }} discount</span>
    </li>
    <li class="flex items-center space-x-2">
        <span class="text-green-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </span>
        <span class="text-gray-700">{{ $data['usage_limit'] == 0 ? 'Unlimited usage' : $data['usage_limit'] . ' times usage' }}</span>
    </li>
    <li class="flex items-center space-x-2">
        <span class="text-purple-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </span>
        <span class="text-gray-700">{{ $data['trial_days'] ? $data['trial_days'] : 0 }} days trial</span>
    </li>
    <li class="flex items-center space-x-2">
        <span class="text-yellow-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </span>
        <span class="text-gray-700">Start: {{ $data['started_at'] ? formatDate($data['started_at']) : 'N/A' }}</span>
    </li>
    <li class="flex items-center space-x-2">
        <span class="text-red-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </span>
        <span class="text-gray-700">End: {{ $data['expired_at'] ? formatDate($data['expired_at']) : 'N/A' }}</span>
    </li>
</ul>
