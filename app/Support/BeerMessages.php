<?php

namespace App\Support;

/**
 * Funny escalating one-liners for the Discord /beer command. The higher a
 * drinker's personal beer count climbs, the more absurd the reply gets. Dutch,
 * no emoji, to match the rest of the bot's voice.
 */
class BeerMessages
{
    /**
     * A random line for the given running total, with {name} and {count}
     * placeholders already filled in.
     */
    public static function line(string $name, int $count): string
    {
        $pool = self::poolFor($count);
        $template = $pool[array_rand($pool)];

        return strtr($template, ['{name}' => $name, '{count}' => (string) $count]);
    }

    /**
     * @return array<int, string>
     */
    private static function poolFor(int $count): array
    {
        return match (true) {
            $count <= 2 => [
                'Proost {name}. Dat is biertje nummer {count}. Rustig opbouwen.',
                '{name} zet de eerste stappen: {count} biertjes. De avond is nog jong.',
                'Genoteerd, {name}. {count} op de teller. Nog helder genoeg om te typen.',
            ],
            $count <= 5 => [
                '{name} zit op {count} biertjes. De schuur wordt langzaam gezellig.',
                'Biertje {count} voor {name}. De reactietijd in de game gaat er niet op vooruit.',
                '{name} pakt door: {count} stuks. Nog steeds in staat een muis te bedienen.',
            ],
            $count <= 9 => [
                '{name} staat op {count}. Vanaf nu tellen we ook de gemiste kills.',
                'Biertje {count}, {name}. Je begint jezelf verrassend grappig te vinden.',
                '{count} biertjes voor {name}. De WASD-toetsen worden een suggestie.',
            ],
            $count <= 15 => [
                '{name} beukt door naar {count}. De schuur draait, of ben jij dat?',
                '{count} biertjes. {name}, je aim is nu officieel een kunstvorm van toeval.',
                'Biertje {count} voor {name}. Iemand houdt de bank en de bar in de gaten.',
            ],
            $count <= 24 => [
                '{name} noteert {count}. Legendarisch, en morgen levensgevaarlijk.',
                '{count} biertjes. {name} speelt nu vooral tegen de zwaartekracht.',
                'Biertje {count} voor {name}. We hebben je stoel alvast vastgeschroefd.',
            ],
            default => [
                '{count} BIERTJES. {name} is geen deelnemer meer, {name} is folklore.',
                '{name} staat op {count}. De brouwerij heeft gebeld, ze willen je sponsoren.',
                'Biertje {count}. {name}, standbeeld verdiend, lever verloren. Respect.',
            ],
        };
    }
}
