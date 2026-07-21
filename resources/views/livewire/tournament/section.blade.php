<div wire:key="tournament-section-{{ $tournament->id }}">
    @if ($inactive)
        <div class="metal-edge clip-corner my-4">
            <div class="p-4 mb-4 bg-forge-graphite min-h-40 flex flex-col justify-between">
                <div class="md:flex md:items-center md:justify-between">
                    <h3 class="font-display text-xl font-semibold uppercase tracking-wide text-white">{{ $tournament->name }}</h3>
                    @if ($this->isTournamentJoinable())
                        <div class="my-2 md:my-0">
                            @if ($userJoined)
                                <x-forge.btn variant="ghost" wire:click="leave">Leave</x-forge.btn>
                            @else
                                <x-forge.btn wire:click="signup">Join</x-forge.btn>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex justify-start space-x-2">
                    <span
                        class="flex justify-center items-center p-3 font-display text-xs uppercase tracking-widest text-forge-steel/70 bg-forge-panel/40">
                        {{ $tournament->time_start }}
                    </span>
                    <span
                        class="flex justify-center items-center p-3 font-display text-xs uppercase tracking-widest text-forge-steel/70 bg-forge-panel/40">
                        {{ $tournament->time_end }}
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto overflow-y-auto max-h-75">
                @if ($tournament->is_team_based)
                    {{-- TEAM-BASED TABLE --}}
                    <table class="min-w-full table-auto divide-y divide-primary-500/10">
                        <thead class="bg-forge-graphite">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left font-display text-xs uppercase tracking-wide text-forge-steel/70">
                                    Team</th>
                                <th
                                    class="px-6 py-3 text-left font-display text-xs uppercase tracking-wide text-forge-steel/70">
                                    Player</th>
                                <th
                                    class="px-6 py-3 text-left font-display text-xs uppercase tracking-wide text-forge-steel/70">
                                    Team Score</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary-500/10">
                            @php $teamRank = 1; @endphp
                            @foreach (collect($tournament->getLeaderboard())->groupBy('team_name') as $teamName => $members)
                                {{-- Team row with rank-based colors --}}
                                @switch($teamRank)
                                    @case(1)
                                        @php $teamColor = 'text-amber-300'; @endphp
                                    @break

                                    @case(2)
                                        @php $teamColor = 'text-forge-steel'; @endphp
                                    @break

                                    @case(3)
                                        @php $teamColor = 'text-amber-600'; @endphp
                                    @break

                                    @default
                                        @php $teamColor = 'text-primary-300'; @endphp
                                @endswitch

                                <tr class="bg-forge-graphite">
                                    <td class="px-6 py-3 font-display font-bold {{ $teamColor }}" colspan="2">
                                        {{ $teamName ?? 'No Team' }}
                                    </td>
                                    <td class="px-6 py-3 font-display font-bold {{ $teamColor }}">
                                        {{ $members->first()['team_score'] ?? 0 }}
                                    </td>
                                </tr>
                                {{-- Players in team --}}
                                @foreach ($members as $member)
                                    <tr @class([
                                        'bg-primary-500/10' => $member['name'] === auth()->user()->name,
                                        'bg-forge-panel/40' => $member['name'] !== auth()->user()->name,
                                    ])>
                                        <td class="px-6 py-4"></td>
                                        <td class="px-6 py-4 text-sm text-forge-steel">
                                            {{ $member['name'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-forge-steel">
                                            {{ $member['score'] }}
                                        </td>
                                    </tr>
                                @endforeach
                                @php $teamRank++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                @else
                    {{-- NORMAL PLAYER TABLE --}}
                    <table class="min-w-full table-auto divide-y divide-primary-500/10">
                        <thead class="bg-forge-graphite">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left font-display text-xs uppercase tracking-wide text-forge-steel/70">
                                    Rank</th>
                                <th
                                    class="px-6 py-3 text-left font-display text-xs uppercase tracking-wide text-forge-steel/70">
                                    Player</th>
                                <th
                                    class="px-6 py-3 text-left font-display text-xs uppercase tracking-wide text-forge-steel/70">
                                    Score</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary-500/10">
                            @foreach ($tournament->getLeaderboard() as $row)
                                <tr
                                    class="@if ($row['name'] === auth()->user()->name) bg-primary-500/10 @else bg-forge-panel/40 @endif">
                                    <td class="px-6 py-4 text-forge-steel">
                                        @switch($row['ranking'])
                                            @case(1)
                                                <span class="text-amber-300 font-bold">1st</span>
                                            @break

                                            @case(2)
                                                <span class="text-forge-steel font-bold">2nd</span>
                                            @break

                                            @case(3)
                                                <span class="text-amber-600 font-bold">3rd</span>
                                            @break

                                            @default
                                                {{ $row['ranking'] . 'th' }}
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 text-forge-steel">{{ $row['name'] }}</td>
                                    <td class="px-6 py-4 text-forge-steel">{{ $row['score'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @else
        <div class="metal-edge clip-corner my-4">
            <div class="p-4 mb-4 bg-forge-graphite">
                <div class="md:flex md:items-center md:justify-between mb-2">
                    <h3 class="font-display text-xl font-semibold uppercase tracking-wide text-white">{{ $tournament->name }}</h3>
                    <div class="flex items-center">
                        <span class="font-display text-xs uppercase tracking-widest text-forge-steel/70 mr-2">
                            {{ $tournament->time_start }} - {{ $tournament->time_end }}
                        </span>
                        @if ($this->isTournamentJoinable())
                            @if ($userJoined)
                                <x-forge.btn variant="ghost" wire:click="leave">Leave</x-forge.btn>
                            @else
                                <x-forge.btn wire:click="signup">Join</x-forge.btn>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="w-full aspect-video my-4 clip-corner overflow-hidden bg-forge-graphite">
                    <img src="{{ asset('storage/' . $tournament->game->image) }}" alt="{{ $tournament->game->name }}"
                        class="w-full h-full object-cover">
                </div>

                <p class="text-forge-steel">{{ $tournament->description }}</p>
            </div>

            <div class="overflow-x-auto">
                @if ($tournament->is_team_based)
                    {{-- TEAM-BASED TABLE --}}
                    <table class="min-w-full table-auto divide-y divide-primary-500/10">
                        <thead class="bg-forge-graphite">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left font-display text-xs uppercase tracking-wide text-forge-steel/70">
                                    Team</th>
                                <th
                                    class="px-6 py-3 text-left font-display text-xs uppercase tracking-wide text-forge-steel/70">
                                    Player</th>
                                <th
                                    class="px-6 py-3 text-left font-display text-xs uppercase tracking-wide text-forge-steel/70">
                                    Team Score</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary-500/10">
                            @php $teamRank = 1; @endphp
                            @foreach (collect($tournament->getLeaderboard())->groupBy('team_name') as $teamName => $members)
                                @switch($teamRank)
                                    @case(1)
                                        @php $teamColor = 'text-amber-300'; @endphp
                                    @break

                                    @case(2)
                                        @php $teamColor = 'text-forge-steel'; @endphp
                                    @break

                                    @case(3)
                                        @php $teamColor = 'text-amber-600'; @endphp
                                    @break

                                    @default
                                        @php $teamColor = 'text-primary-300'; @endphp
                                @endswitch

                                <tr class="bg-forge-graphite">
                                    <td class="px-6 py-3 font-display font-bold {{ $teamColor }}" colspan="2">
                                        {{ $teamName ?? 'No Team' }}
                                    </td>
                                    <td class="px-6 py-3 font-display font-bold {{ $teamColor }}">
                                        {{ $members->first()['team_score'] ?? 0 }}
                                    </td>
                                </tr>
                                @foreach ($members as $member)
                                    <tr @class([
                                        'bg-primary-500/10' => $member['name'] === auth()->user()->name,
                                        'bg-forge-panel/40' => $member['name'] !== auth()->user()->name,
                                    ])>
                                        <td class="px-6 py-4 text-sm text-forge-steel">
                                            <img src="{{ $member['profile_photo_path'] ?? asset('images/default-avatar.png') }}"
                                                alt="{{ $member['name'] }}'s Avatar"
                                                class="inline-block w-10 h-10 aspect-square rounded-full mr-2">
                                            {{ $member['name'] }}
                                        </td>
                                        <td></td>
                                        <td class="px-6 py-4 text-sm text-forge-steel">
                                            {{ $member['score'] }}</td>
                                    </tr>
                                @endforeach
                                @php $teamRank++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                @else
                    {{-- NORMAL PLAYER TABLE --}}
                    <table class="min-w-full table-auto divide-y divide-primary-500/10">
                        <thead class="bg-forge-graphite">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left font-display text-xs uppercase tracking-wide text-forge-steel/70">
                                    Rank</th>
                                <th
                                    class="px-6 py-3 text-left font-display text-xs uppercase tracking-wide text-forge-steel/70">
                                    Player</th>
                                <th
                                    class="px-6 py-3 text-left font-display text-xs uppercase tracking-wide text-forge-steel/70">
                                    Score</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary-500/10">
                            @foreach ($tournament->getLeaderboard() as $leaderboardRow)
                                <tr
                                    class="@if ($leaderboardRow['name'] === auth()->user()->name) bg-primary-500/10 @else bg-forge-panel/40 @endif">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-forge-steel flex items-center">
                                        <img src="{{ $leaderboardRow['profile_photo_path'] ?? asset('images/default-avatar.png') }}"
                                            alt="{{ $leaderboardRow['name'] }}'s Avatar"
                                            class="inline-block w-10 h-10 aspect-square rounded-full mr-2">

                                        @switch($leaderboardRow['ranking'])
                                            @case(1)
                                                <span class="text-amber-300 font-bold">1st</span>
                                            @break

                                            @case(2)
                                                <span class="text-forge-steel font-bold">2nd</span>
                                            @break

                                            @case(3)
                                                <span class="text-amber-600 font-bold">3rd</span>
                                            @break

                                            @default
                                                {{ $leaderboardRow['ranking'] . 'th' }}
                                        @endswitch
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-forge-steel">
                                        {{ $leaderboardRow['name'] }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-forge-steel">
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
