<?php

namespace App\Support;

/**
 * Funny escalating one-liners for the Discord /wine command, the classier
 * sibling of BeerMessages. The higher a drinker's personal glass count climbs,
 * the more the sommelier act crumbles. Dutch, no emoji, to match the bot.
 */
class WineMessages
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
                'Santé {name}. Glas nummer {count}. Beschaafd begin.',
                '{name} walst het glas rond: {count} op de teller. Mooie neus, zegt men.',
                'Genoteerd, {name}. {count} glazen wijn. De pink gaat nog keurig omhoog.',
            ],
            $count <= 5 => [
                '{name} zit op {count} glazen. Het bouquet wordt met de minuut interessanter.',
                'Glas {count} voor {name}. De omschrijvingen worden al iets minder Frans.',
                '{name} proeft door: {count} glazen. "Aards, met een hint van LAN-party."',
            ],
            $count <= 9 => [
                '{name} staat op {count} glazen. De sommelier-act begint scheurtjes te vertonen.',
                'Glas {count}, {name}. Je noemt het nog steeds "degusteren". Wij noemen het drinken.',
                '{count} glazen voor {name}. De kurk is inmiddels zoek, net als je aim.',
            ],
            $count <= 15 => [
                '{name} schenkt door naar {count}. De fles heeft geen etiket meer nodig.',
                '{count} glazen. {name} beschrijft de wijn nu als "nat" en "lekker".',
                'Glas {count} voor {name}. Het is officieel: dit is geen proeverij meer.',
            ],
            $count <= 24 => [
                '{name} noteert glas {count}. De wijngaard overweegt een gedenksteen.',
                '{count} glazen. {name} drinkt nu rechtstreeks uit de karaf.',
                'Glas {count} voor {name}. Morgen alleen nog fluisteren, afgesproken.',
            ],
            default => [
                '{count} GLAZEN. {name} is geen wijnkenner meer, {name} is een wijnlegende.',
                '{name} staat op {count}. Het chateau heeft gebeld, je krijgt een eigen vleugel.',
                'Glas {count}. {name}, de druiven zijn bang voor je. Chapeau.',
            ],
        };
    }
}
