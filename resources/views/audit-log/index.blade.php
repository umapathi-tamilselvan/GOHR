<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit Log') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('User') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Action') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Model') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Changes') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Timestamp') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($logs as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $log->user?->name ?? 'System' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $log->action }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $log->auditable_type }} (ID: {{ $log->auditable_id }})
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($log->action === 'updated')
                                                @foreach ($log->new_values as $key => $value)
                                                    <div>
                                                        <strong>{{ $key }}:</strong>
                                                        <span class="text-red-500">{{ $log->old_values[$key] ?? 'null' }}</span> ->
                                                        <span class="text-green-500">{{ $value }}</span>
                                                    </div>
                                                @endforeach
                                            @elseif($log->action === 'created' && $log->new_values)
                                                @foreach ($log->new_values as $key => $value)
                                                    <div>
                                                        <strong>{{ $key }}:</strong>
                                                        <span class="text-green-500">{{ $value }}</span>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 