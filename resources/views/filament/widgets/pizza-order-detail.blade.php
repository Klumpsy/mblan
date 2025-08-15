{{-- resources/views/filament/widgets/pizza-order-detail.blade.php --}}
<div class="space-y-4">
    <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 border border-orange-200 dark:border-orange-800">
        <div class="flex items-center gap-2 mb-3">
            <span class="text-2xl">🍕</span>
            <h3 class="text-lg font-semibold text-orange-800 dark:text-orange-200">Pizza Order Details</h3>
        </div>

        <div class="space-y-3">
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Customer:</label>
                <p class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Order:</label>
                <div class="mt-1 p-3 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700">
                    <p class="text-base text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $order }}</p>
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Last Updated:</label>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    {{ $updated_at->format('F j, Y \a\t g:i A') }}
                    <span class="text-gray-500 dark:text-gray-400">({{ $updated_at->diffForHumans() }})</span>
                </p>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Order Length:</label>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ strlen($order) }} characters</p>
            </div>
        </div>
    </div>

    <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
        💡 Orders are submitted via Discord using the <code
            class="bg-gray-100 dark:bg-gray-800 px-1 rounded">/pizza</code> command
    </div>
</div>
