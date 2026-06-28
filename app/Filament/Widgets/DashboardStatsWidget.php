<?php

namespace App\Filament\Widgets;

use App\Models\Edition;
use App\Models\Signup;
use App\Models\Tournament;
use App\Models\User;
use App\Models\Blog;
use App\Models\Achievement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $activeEdition = Edition::where('is_active', true)->first();
        $activeTournaments = Tournament::where('is_active', true)->count();
        $totalUsers = User::count();
        $pendingSignups = Signup::where('confirmed', false)->count();
        $totalSignups = Signup::where('confirmed', true)->count();
        $unpublishedBlogs = Blog::where('published', false)->count();
        $totalAchievements = Achievement::count();

        $stats = [
            Stat::make('Active Edition', $activeEdition ? $activeEdition->name : 'None')
                ->description($activeEdition ? "Year: {$activeEdition->year}" : 'No active edition')
                ->descriptionIcon($activeEdition ? 'heroicon-m-calendar-days' : 'heroicon-m-x-circle')
                ->color($activeEdition ? 'success' : 'danger'),

            Stat::make('Active Tournaments', $activeTournaments)
                ->description('Currently running')
                ->descriptionIcon('heroicon-m-bolt')
                ->color($activeTournaments > 0 ? 'info' : 'gray'),

            Stat::make('Total Users', $totalUsers)
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
        ];

        if ($activeEdition) {
            $editionSignups = $activeEdition->signups()->where('confirmed', true)->count();
            $editionPending = $activeEdition->signups()->where('confirmed', false)->count();
            $editionRevenue = $activeEdition->signups()
                ->where('confirmed', true)
                ->get()
                ->sum(function ($signup) {
                    return $signup->calculateCost();
                });

            $stats = array_merge($stats, [
                Stat::make('Edition Signups', $editionSignups)
                    ->description("{$editionPending} pending confirmation")
                    ->descriptionIcon('heroicon-m-user-plus')
                    ->color('success'),

                Stat::make('Edition Revenue', '€' . number_format($editionRevenue, 2))
                    ->description('From confirmed signups')
                    ->descriptionIcon('heroicon-m-currency-euro')
                    ->color('success'),
            ]);
        } else {
            $stats = array_merge($stats, [
                Stat::make('Pending Signups', $pendingSignups)
                    ->description('Awaiting confirmation')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color($pendingSignups > 0 ? 'warning' : 'success'),

                Stat::make('Total Signups', $totalSignups)
                    ->description('All-time confirmed')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('info'),
            ]);
        }

        $stats = array_merge($stats, [
            Stat::make('Draft Blogs', $unpublishedBlogs)
                ->description('Unpublished posts')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($unpublishedBlogs > 0 ? 'warning' : 'success'),

            Stat::make('Achievements', $totalAchievements)
                ->description('Available to earn')
                ->descriptionIcon('heroicon-m-star')
                ->color('primary'),
        ]);

        return $stats;
    }
}
