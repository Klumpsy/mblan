<div wire:key="tournament-section-{{ $tournament->id }}">
    @if ($inactive)
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-l my-4 shadow-shite dark:shadow-gray-700">
            <div class="p-4 mb-4 bg-gray-100 dark:bg-gray-700 rounded-lg min-h-40 flex flex-col justify-between">
                <div class="md:flex md:items-center md:justify-between">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ $tournament->name }}</h3>
                    @if ($this->isTournamentJoinable())
                        <div class="my-2 md:my-0">
                            @if ($userJoined)
                                <x-button wire:click="leave">Leave</x-button>
                            @else
                                <x-button wire:click="signup">Join</x-button>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex justify-start">
                    <span
                        class="mr-2 flex justify-center items-center p-3 text-sm text-gray-500 dark:text-gray-400 bg-gray-200 dark:bg-gray-800 rounded">
                        {{ $tournament->time_start }}
                    </span>
                    <span
                        class="flex justify-center items-center p-3 text-sm text-gray-500 dark:text-gray-400 bg-gray-200 dark:bg-gray-800 rounded">
                        {{ $tournament->time_end }}
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto overflow-y-auto max-h-75">
                @if ($tournament->is_team_based)
                    {{-- TEAM-BASED TABLE --}}
                    <table class="min-w-full divide-y table-auto divide-gray-200 dark:divide-gray-700 rounded-md">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Team</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Player</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Team Score</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach (collect($leaderboard)->groupBy('team_name') as $teamName => $members)
                                {{-- Team row --}}
                                <tr class="bg-gray-200 dark:bg-gray-900">
                                    <td class="px-6 py-3 font-bold text-gray-900 dark:text-gray-200" colspan="2">
                                        {{ $teamName ?? 'No Team' }}
                                    </td>
                                    <td class="px-6 py-3 font-bold text-gray-900 dark:text-gray-200">
                                        {{ $members->first()['team_score'] ?? 0 }}
                                    </td>
                                </tr>
                                {{-- Players in team --}}
                                @foreach ($members as $member)
                                    <tr @class([
                                        'bg-gray-100 dark:bg-gray-800' => $member['name'] === auth()->user()->name,
                                    ])>
                                        <td class="px-6 py-4"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                            {{ $member['name'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                            {{ $member['score'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                @else
                    {{-- NORMAL PLAYER TABLE --}}
                    <table class="min-w-full divide-y table-auto divide-gray-200 dark:divide-gray-700 rounded-md">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Rank</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Player</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Score</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($tournament->getLeaderboard() as $row)
                                <tr @class([
                                    'bg-gray-300 dark:bg-gray-900' => $row['name'] === auth()->user()->name,
                                ])>
                                    <td class="px-6 py-4">{{ $row['ranking'] }}</td>
                                    <td class="px-6 py-4">{{ $row['name'] }}</td>
                                    <td class="px-6 py-4">{{ $row['score'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-l my-4 shadow-shite dark:shadow-gray-700">
            <div class="p-4 mb-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                <div class="md:flex md:items-center md:justify-between mb-2">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ $tournament->name }}</h3>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">
                            {{ $tournament->time_start }} - {{ $tournament->time_end }}
                        </span>
                        @if ($this->isTournamentJoinable())
                            @if ($userJoined)
                                <x-button wire:click="leave" class="text-red-700">Leave</x-button>
                            @else
                                <x-button wire:click="signup" class="text-green-300">Join</x-button>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="w-full aspect-video my-4 rounded-lg overflow-hidden">
                    <img src="{{ asset('storage/' . $tournament->game->image) }}" alt="{{ $tournament->game->name }}"
                        class="w-full h-full object-cover">
                </div>

                <p class="text-gray-600 dark:text-gray-400">{{ $tournament->description }}</p>
            </div>

            <div class="overflow-x-auto">
                @if ($tournament->is_team_based)
                    {{-- TEAM-BASED TABLE --}}
                    <table class="min-w-full divide-y table-auto divide-gray-200 dark:divide-gray-700 rounded-md">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Team</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Player</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Team Score</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach (collect($tournament->getLeaderboard())->groupBy('team_name') as $teamName => $members)
                                {{-- Team row --}}
                                <tr class="bg-gray-200 dark:bg-gray-900">
                                    <td class="px-6 py-3 font-bold text-gray-900 dark:text-gray-200" colspan="2">
                                        {{ $teamName ?? 'No Team' }}
                                    </td>
                                    <td class="px-6 py-3 font-bold text-gray-900 dark:text-gray-200">
                                        {{ $members->first()['team_score'] ?? 0 }}
                                    </td>
                                </tr>
                                @foreach ($members as $member)
                                    <tr @class([
                                        'bg-gray-100 dark:bg-gray-800' => $member['name'] === auth()->user()->name,
                                    ])>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                            <img src="{{ $member['profile_photo_path'] ?? asset('images/default-avatar.png') }}"
                                                alt="{{ $member['name'] }}'s Avatar"
                                                class="inline-block w-10 h-10 aspect-square rounded-full mr-2">
                                            {{ $member['name'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                @else
                    {{-- NORMAL PLAYER TABLE --}}
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 rounded-md">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Rank</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Player</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Score</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($tournament->getLeaderboard() as $leaderboardRow)
                                <tr class="@if ($leaderboardRow['name'] === auth()->user()->name) bg-gray-300 dark:bg-gray-900 @endif">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200 flex items-center">
                                        <img src="{{ $leaderboardRow['profile_photo_path'] ?? asset('images/default-avatar.png') }}"
                                            alt="{{ $leaderboardRow['name'] }}'s Avatar"
                                            class="inline-block w-10 h-10 aspect-square rounded-full mr-2">

                                        @switch($leaderboardRow['ranking'])
                                            @case(1)
                                                <span class="text-yellow-500 font-bold">1st</span>
                                            @break

                                            @case(2)
                                                <span class="text-gray-500 font-bold">2nd</span>
                                            @break

                                            @case(3)
                                                <span class="text-yellow-700 font-bold">3rd</span>
                                            @break

                                            @default
                                                {{ $leaderboardRow['ranking'] . 'th' }}
                                        @endswitch
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                        {{ $leaderboardRow['name'] }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                        {{ $leaderboardRow['score'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif
</div>
