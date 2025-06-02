 <div class="p-4 mb-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
     <div class="md:flex md:items-center md:justify-between mb-2">
         <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ $tournament->name }}</h3>
         <span class="text-sm text-gray-500 dark:text-gray-400">
             {{ $tournament->time_start }} - {{ $tournament->time_end }}
         </span>
     </div>

     <p class="text-gray-600 dark:text-gray-400">{{ $tournament->description }}</p>
 </div>
 <div class="overflow-x-auto">
     <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 rounded-md">
         <thead class="bg-gray-50 dark:bg-gray-700">
             <tr>
                 <th scope="col"
                     class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                     Rank</th>
                 <th scope="col"
                     class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                     Player</th>
                 <th scope="col"
                     class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                     Score</th>
             </tr>
         </thead>
         <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
             @foreach ($tournament->getLeaderboard() as $index => $leaderboardRow)
                 <tr>
                     <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
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
 </div>
 </div
