<?php

namespace App\Support;

use App\Models\Photo;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Builds the "User stats" leaderboards for the Leaderboard page: several ranked
 * brackets computed from data we already track (beer, timeline photos, emoji
 * reactions, achievements) plus a composite engagement score. Each bracket is
 * a title + unit + a ranked list of {user, value}.
 */
class UserLeaderboards
{
    private const TOP = 10;

    /**
     * @return array<int, array{key: string, title: string, unit: string, icon: string, caption: ?string, rows: Collection}>
     */
    public function all(): array
    {
        $photos = $this->photosPerUser();
        $likes = $this->likesReceivedPerUser();
        $given = $this->reactionsGivenPerUser();
        $achievements = $this->achievementsPerUser();
        $beer = $this->beerPerUser();

        // Composite engagement: rewards posting, being liked, reacting, and
        // unlocking achievements. Formula is shown to players as a caption.
        $engagement = [];
        foreach ([$photos, $likes, $given, $achievements] as $weightIndex => $map) {
            $weight = [3, 2, 1, 5][$weightIndex];
            foreach ($map as $id => $value) {
                $engagement[$id] = ($engagement[$id] ?? 0) + $value * $weight;
            }
        }

        $brackets = [
            $this->bracket('beer', 'Meeste bier gedronken', 'biertjes', 'tile_0072', null, $beer),
            $this->bracket('engagement', 'Betrokkenheid', 'punten', 'tile_0083',
                'Punten: foto x3, ontvangen reactie x2, gegeven reactie x1, achievement x5.', $engagement),
            $this->bracket('photos', "Meeste foto's geplaatst", "foto's", 'tile_0088', null, $photos),
            $this->bracket('likes', 'Meeste likes op posts', 'reacties', 'tile_0044', 'Reacties die anderen op jouw foto\'s achterlieten.', $likes),
            $this->bracket('given', 'Gulste reageerder', 'reacties', 'tile_0122', 'Reacties die jij op andermans posts gaf.', $given),
            $this->bracket('achievements', 'Meeste achievements', 'behaald', 'tile_0096', null, $achievements),
        ];

        // The icons follow the edition theme: the curated farm tiles for the
        // farm set, otherwise a stable pick from the edition's sprite pool.
        $edition = \App\Models\Edition::current();
        $themed = $edition && ($edition->scenery_set !== 'farm' || ! empty($edition->scenery_sprites));
        $pool = $themed ? $edition->scenerySprites() : [];

        foreach ($brackets as $i => $bracket) {
            $brackets[$i]['icon_url'] = $pool
                ? $pool[$i % count($pool)]
                : asset('images/farm/'.$bracket['icon'].'.png');
        }

        return $brackets;
    }

    /**
     * @param  array<int, int>  $map  user id => value
     * @return array{key: string, title: string, unit: string, icon: string, caption: ?string, rows: Collection}
     */
    private function bracket(string $key, string $title, string $unit, string $icon, ?string $caption, array $map): array
    {
        arsort($map);
        $top = array_slice($map, 0, self::TOP, true);

        $users = User::whereIn('id', array_keys($top))->get()->keyBy('id');

        $rows = collect($top)
            ->map(fn (int $value, int $id) => $users->has($id) ? ['user' => $users[$id], 'value' => $value] : null)
            ->filter()
            ->values();

        return compact('key', 'title', 'unit', 'icon', 'caption', 'rows');
    }

    /** @return array<int, int> */
    private function beerPerUser(): array
    {
        return User::where('beer_count', '>', 0)->pluck('beer_count', 'id')->all();
    }

    /** @return array<int, int> */
    private function photosPerUser(): array
    {
        return Photo::query()
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as c')
            ->pluck('c', 'user_id')->all();
    }

    /** @return array<int, int> Reactions others left on a user's photos. */
    private function likesReceivedPerUser(): array
    {
        return Reaction::query()
            ->where('reactions.reactable_type', Photo::class)
            ->join('photos', 'photos.id', '=', 'reactions.reactable_id')
            ->groupBy('photos.user_id')
            ->selectRaw('photos.user_id as uid, COUNT(*) as c')
            ->pluck('c', 'uid')->all();
    }

    /** @return array<int, int> */
    private function reactionsGivenPerUser(): array
    {
        return Reaction::query()
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as c')
            ->pluck('c', 'user_id')->all();
    }

    /** @return array<int, int> Achievements actually unlocked (achieved_at set). */
    private function achievementsPerUser(): array
    {
        return DB::table('achievement_user')
            ->whereNotNull('achieved_at')
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as c')
            ->pluck('c', 'user_id')->all();
    }
}
