<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            [
                'name' => 'Warcraft III',
                'year_of_release' => '2002',
                'text_block_one' => 'Warcraft III: Reign of Chaos is a high fantasy real-time strategy game developed and published by Blizzard Entertainment. It was released in July 2002 and has since become one of the most influential RTS games of all time.',
                'text_block_two' => 'The game features four playable races: Humans, Orcs, Night Elves, and Undead, each with unique units, buildings, and abilities. Warcraft III introduced hero units, powerful characters that gain experience, level up, and learn new abilities throughout a match.',
                'text_block_three' => 'The game\'s expansion, The Frozen Throne, added new campaign missions, heroes, units, and multiplayer maps. Warcraft III\'s World Editor also led to the creation of Defense of the Ancients (DotA), which pioneered the MOBA genre.',
                'short_description' => 'A legendary real-time strategy game featuring four unique races and hero units in the Warcraft universe.',
                'image' => 'games/warcraft3.jpg',
                'link_to_website' => 'https://playwarcraft3.com/',
                'link_to_youtube' => 'https://www.youtube.com/watch?v=72UbFQO5-m0',
                'likes' => 0
            ],
            [
                'name' => 'Team Fortress 2',
                'year_of_release' => '2007',
                'text_block_one' => 'Team Fortress 2 is a team-based first-person shooter multiplayer game developed and published by Valve. Released in 2007 as part of The Orange Box, it became free-to-play in 2011 and remains one of the most popular multiplayer games on Steam.',
                'text_block_two' => 'The game features nine distinct character classes divided into three categories: Offense (Scout, Soldier, Pyro), Defense (Demoman, Heavy, Engineer), and Support (Medic, Sniper, Spy). Each class has unique weapons and abilities that contribute to team-based objectives.',
                'text_block_three' => 'TF2 is known for its cartoon-like art style, humor, and distinctive characters. The game has received numerous updates over the years, including new game modes, maps, weapons, and cosmetic items. It has also developed a robust economy around its in-game items and hats.',
                'short_description' => 'A timeless team-based FPS with distinctive character classes and a unique visual style.',
                'image' => 'games/teamfortress2.jpg',
                'link_to_website' => 'https://www.teamfortress.com/',
                'link_to_youtube' => 'https://www.youtube.com/watch?v=pP84b1NqpS0',
                'likes' => 0
            ],
            [
                'name' => 'Unreal Tournament 2004',
                'year_of_release' => '2004',
                'text_block_one' => 'Unreal Tournament 2004 is a first-person arena shooter developed by Epic Games and Digital Extremes. Released in March 2004, it expanded upon its predecessors with new game modes, weapons, and vehicles.',
                'text_block_two' => 'The game features a variety of fast-paced game modes, including Deathmatch, Team Deathmatch, Capture the Flag, Bombing Run, and the innovative Onslaught mode which introduced vehicular combat. UT2004 is known for its diverse weapon arsenal, from the basic Assault Rifle to the devastating Redeemer.',
                'text_block_three' => 'UT2004 was praised for its AI bots, which provided challenging opponents even in offline play. The game also shipped with a robust map editor and modding tools, leading to a thriving community of custom content creators. Even years after release, it remains a benchmark for arena shooters.',
                'short_description' => 'A fast-paced arena shooter with diverse game modes, weapons, and vehicles.',
                'image' => 'games/unrealtournament2004.jpg',
                'link_to_website' => 'https://www.epicgames.com/unrealtournament/',
                'link_to_youtube' => 'https://www.youtube.com/watch?v=abmiv22Q7xA',
                'likes' => 0
            ],
            [
                'name' => 'Hearthstone - Battlegrounds',
                'year_of_release' => '2014',
                'text_block_one' => 'Hearthstone is a free-to-play digital collectible card game developed and published by Blizzard Entertainment. Launched in 2014, the game is set in the Warcraft universe and has become one of the most popular digital card games worldwide.',
                'text_block_two' => 'Players build decks of cards representing spells, weapons, and minions, and take turns playing cards to defeat their opponent. The game features various modes including Casual, Ranked, Arena, Tavern Brawl, Battlegrounds, and single-player Adventures. New card expansions are released regularly, introducing new mechanics and strategies.',
                'text_block_three' => 'Hearthstone is known for its accessibility, colorful art style, and dynamic gameplay that balances simplicity with deep strategic options. The game has a thriving competitive scene with tournaments offering substantial prize pools. It\'s available on PC, mobile devices, and tablets.',
                'short_description' => 'A digital card game set in the Warcraft universe with accessible yet strategic gameplay.',
                'image' => 'games/hearthstone.jpg',
                'link_to_website' => 'https://playhearthstone.com/',
                'link_to_youtube' => 'https://www.youtube.com/watch?v=NuW_wDMXl64',
                'likes' => 0
            ],
            [
                'name' => 'Diablo III',
                'year_of_release' => '2012',
                'text_block_one' => 'Diablo III is an action role-playing game developed and published by Blizzard Entertainment. Released in 2012, it is the third installment in the Diablo series and has sold over 30 million copies across all platforms.',
                'text_block_two' => 'The game takes place in the dark fantasy world of Sanctuary, where players choose one of seven character classes (Barbarian, Demon Hunter, Monk, Witch Doctor, Wizard, Crusader, and Necromancer) to battle the forces of Hell. Diablo III features a dynamic loot system, randomized dungeons, and multiple difficulty levels.',
                'text_block_three' => 'The Reaper of Souls expansion added the Adventure Mode, which introduced Bounties, Nephalem Rifts, and Greater Rifts, greatly expanding the endgame content. The game\'s Seasons feature provides regular opportunities for players to start fresh with new characters and compete for unique rewards.',
                'short_description' => 'A dark fantasy action RPG featuring seven classes and endless demon-slaying combat.',
                'image' => 'games/diablo3.jpg',
                'link_to_website' => 'https://us.diablo3.com/',
                'link_to_youtube' => 'https://www.youtube.com/watch?v=f-lEvrz2D8w',
                'likes' => 0
            ],
            [
                'name' => 'Trackmania',
                'year_of_release' => '2020',
                'text_block_one' => 'Trackmania (2020) is a racing game developed by Ubisoft Nadeo. It\'s a reboot of the Trackmania Nations series, focusing on arcade-style time trial racing with an emphasis on skill, precision, and creativity.',
                'text_block_two' => 'The game features a robust track editor that allows players to create and share their own tracks, leading to an ever-expanding library of community-created content. Races take place on highly stylized tracks with loops, jumps, and wall rides that test players\' reflexes and vehicle control.',
                'text_block_three' => 'Trackmania has a unique subscription model with different tiers of access, but the base game offers free access to selected tracks. The game has a strong competitive scene with regular tournaments and a global ranking system. Its minimalist visual style and precise physics make it accessible yet difficult to master.',
                'short_description' => 'A fast-paced arcade racing game with precision driving and extensive track creation tools.',
                'image' => 'games/trackmania.jpg',
                'link_to_website' => 'https://www.trackmania.com/',
                'link_to_youtube' => 'https://www.youtube.com/watch?v=VEe4e0OA67I',
                'likes' => 0
            ],
            [
                'name' => 'Mario Party',
                'year_of_release' => '2018',
                'text_block_one' => 'Mario Party is a party game series featuring Mario franchise characters, with the latest mainline entry being Super Mario Party (2018) for the Nintendo Switch, followed by Mario Party Superstars (2021). The series combines board game elements with mini-games.',
                'text_block_two' => 'Players move around digital board games, collecting coins and stars while competing in over 80 mini-games. The games support up to four players and blend luck with skill-based challenges. Super Mario Party introduced character-specific dice blocks and a new team-based mode called River Survival.',
                'text_block_three' => 'Mario Party Superstars returned to the classic gameplay style with boards from the Nintendo 64 era and a collection of 100 mini-games from throughout the series\' history. The games are designed to be accessible to players of all ages, making them popular choices for family gatherings and social events.',
                'short_description' => 'A digital board game featuring Mario characters and competitive mini-games for up to four players.',
                'image' => 'games/marioparty.jpg',
                'link_to_website' => 'https://www.nintendo.com/games/detail/super-mario-party-switch/',
                'link_to_youtube' => 'https://www.youtube.com/watch?v=y9BKCVx_mNk',
                'likes' => 0
            ],
            [
                'name' => 'Battlefield Vietnam',
                'year_of_release' => '2004',
                'text_block_one' => 'Battlefield Vietnam is a first-person shooter developed by DICE and published by Electronic Arts in 2004. It was the second installment in the Battlefield series, set during the Vietnam War of the 1960s and 1970s.',
                'text_block_two' => 'The game features asymmetric warfare between American and North Vietnamese forces, with historically authentic weapons, vehicles, and aircraft. Players could ride in helicopters while listening to era-appropriate music like "Ride of the Valkyries" and "Fortunate Son." The game emphasized teamwork across land, air, and sea combat.',
                'text_block_three' => 'Battlefield Vietnam introduced new mechanics like booby traps, punji sticks, and the ability to airlift vehicles with helicopters. The dense jungle environments provided a stark contrast to the desert settings of the original Battlefield 1942. While it has been overshadowed by later entries in the series, it remains notable for its atmospheric recreation of the Vietnam War era.',
                'short_description' => 'A team-based FPS set during the Vietnam War with authentic weapons, vehicles, and music of the era.',
                'image' => 'games/battlefieldvietnam.jpg',
                'link_to_website' => 'https://www.ea.com/games/battlefield',
                'link_to_youtube' => 'https://www.youtube.com/watch?v=DM-GZGtqmEw',
                'likes' => 0
            ],
            [
                'name' => 'Age of Empires III',
                'year_of_release' => '2005',
                'text_block_one' => 'Age of Empires III is a real-time strategy game developed by Ensemble Studios and published by Microsoft Game Studios in 2005. It focuses on the European colonization of the Americas from 1492 to 1876.',
                'text_block_two' => 'The game introduced several new mechanics to the series, including Home Cities that provide support and upgrades over time, and a focus on gunpowder units and naval warfare. Players can choose from eight European civilizations in the base game, with expansions adding Native American, Asian, and African civilizations.',
                'text_block_three' => 'Age of Empires III: Definitive Edition was released in 2020, featuring remastered graphics, new content, and gameplay improvements. The game combines resource gathering, base building, and military strategy across different Ages as players advance their civilization from Discovery Age to Imperial Age.',
                'short_description' => 'A historical RTS game focusing on European colonization of the Americas with innovative Home City mechanics.',
                'image' => 'games/ageofempires3.jpg',
                'link_to_website' => 'https://www.ageofempires.com/games/aoeiii/',
                'link_to_youtube' => 'https://www.youtube.com/watch?v=wI_b-xETQu4',
                'likes' => 0
            ],
            [
                'name' => 'Among Us',
                'year_of_release' => '2018',
                'text_block_one' => 'Among Us is a social deduction game developed by InnerSloth. Released in 2018, it gained massive popularity in 2020 during the COVID-19 pandemic, becoming a cultural phenomenon with millions of players worldwide.',
                'text_block_two' => 'The game takes place on a spaceship where players are assigned roles as either Crewmates or Impostors. Crewmates must complete tasks and identify the Impostors among them, while Impostors attempt to sabotage the ship and eliminate Crewmates without being detected. Emergency meetings can be called to discuss suspicious behavior and vote out suspected Impostors.',
                'text_block_three' => 'Among Us features simple 2D graphics and accessible gameplay that works well across PC, mobile devices, and consoles. The game emphasizes communication, deception, and observation skills. Regular updates have added new maps, roles, and cosmetic items to the game.',
                'short_description' => 'A social deduction game where crewmates must identify impostors aboard their spaceship.',
                'image' => 'games/amongus.jpg',
                'link_to_website' => 'https://www.innersloth.com/games/among-us/',
                'link_to_youtube' => 'https://www.youtube.com/watch?v=36ua-xxGEYQ',
                'likes' => 0
            ]
        ];

        foreach ($games as $game) {
            Game::create($game);
        }
    }
}
