<?php

namespace App\Support;

/**
 * The De Pyramiden menu used for LAN food orders: the pizzas plus non-pizza
 * options (broodjes, schotels, grill, pasta, salades) for those who don't want
 * pizza. Each item is [name, description, price]. The chosen item's name is
 * stored on the order.
 */
class PizzaMenu
{
    /**
     * @return array<string, array<int, array{0: string, 1: string, 2: float}>>
     */
    public static function categories(): array
    {
        return [
            "Pizza's" => [
                ['53. Margherita', 'Tomatensaus, kaas', 9.00],
                ['54. Napoletana', 'Ansjovis', 10.50],
                ['55. Borromea', 'Ham', 10.50],
                ['56. Salami', 'Salami', 10.50],
                ['57. Ananas', 'Ananas', 10.50],
                ['58. Funghi', 'Champignons', 10.50],
                ['59. Bote', 'Ham, salami', 11.00],
                ['60. Hawaï', 'Ham, ananas', 12.00],
                ['61. Peperoni', 'Paprika, uien, peperoni', 11.00],
                ['62. Cappricciosa', 'Champignons, ham', 11.50],
                ['63. Sole Mio', 'Ei, uien', 11.00],
                ['64. La Baba', 'Ham, uien', 11.50],
                ['65. Gorgonzola', 'Pittige Italiaanse kaas', 11.50],
                ['66. Campagnola', 'Paprika, uien, ham, champignons', 12.50],
                ['67. Vegetaria', 'Verschillende groenten', 12.50],
                ['68. Fantasia', 'Fantasie van de kok', 16.00],
                ['69. Quattro Stagione', 'Ham, salami, paprika, champignons', 13.00],
                ['70. Calzone', 'Ham, salami, champignons', 13.00],
                ['71. Paesana', 'Ham, salami, spek, uien, champignons', 13.00],
                ['72. Funghi Marinara', 'Zalm, uien, champignons', 13.50],
                ['73. Dello Chef', 'Gehakt, uien', 13.00],
                ['74. Tonno', 'Tonijn, uien', 13.00],
                ['75. Sphinx', 'Shoarmavlees', 14.00],
                ['76. Mathieu', 'Gehakt, kip, shoarma, extra kaas', 16.50],
                ['77. Iyor', 'Gehakt, kip, champignons', 14.00],
                ['78. Sardegnola', 'Paprika, champignons, salami, olijven, uien', 13.50],
                ['79. Frutti Di Mare', 'Verschillende soorten vis', 14.00],
                ['80. Döner', 'Döner kebab', 14.00],
                ['81. Pyramide', 'Champignons, shoarma, paprika, uien', 15.50],
                ['82. Carbonara Speciale', 'Ham, salami, spek, uien, ei', 14.00],
                ['83. Slomo', 'Ham, salami, ananas', 13.00],
                ['84. Lamama', 'Shoarma, kip, uien, paprika', 15.50],
                ['85. Athro', 'Champignons, kip, uien', 15.00],
                ['86. Quatro formaggio', '4 soorten kaas', 13.50],
                ['87. Urhoy', 'Ham, spek, salami, champignons, paprika', 14.00],
                ['88. Lobon', 'Ham, spek, champignons, kip', 15.50],
                ['89. Malyo', 'Gehakt, ham, champignons, paprika, shoarma, uien', 16.00],
                ['90. Al Forno', 'Kip', 14.50],
                ['91. Edessa', 'Champignons, gehakt, shoarma, kip, uien', 16.50],
            ],
            'Broodjes' => [
                ['Broodje shoarma', 'Warm vlees van de grill', 8.50],
                ['Broodje shoarma kaas', 'Shoarma met gesmolten kaas', 9.00],
                ['Broodje shaslick', 'Varkensvlees, paprika, uien', 9.00],
                ['Broodje kip shaslick', 'Kipfilet, paprika, uien', 9.00],
                ['Broodje kebab', 'Gegrild gehakt', 9.00],
                ['Broodje döner kebab', 'Kalfsvlees, Turks brood', 8.50],
                ['Hamburger', 'Huisgemaakt van de grill', 8.00],
                ['Broodje gezond', 'Tomaat, komkommer, sla, ham, kaas', 6.00],
            ],
            'Superbroodjes' => [
                ['Super shoarma', '', 11.00],
                ['Super shaslick', '', 12.00],
                ['Kapsalon', 'Shoarma, friet, salade, kaas', 12.00],
                ['Portie spareribs', '', 13.00],
            ],
            'Schotels & grill' => [
                ['Shoarma schotel', 'Met friet, brood en saus', 16.50],
                ['Shaslick schotel', '', 17.50],
                ['Spareribs schotel', '', 18.00],
                ['Mixed grill shaslick', 'Shoarma & shaslick', 18.00],
                ['Mixed grill Pyramiden', 'Diverse soorten vlees', 20.50],
            ],
            'Pasta & salades' => [
                ['Pasta bolognese', 'Tomatensaus, gehakt en kaas', 11.00],
                ['Herdersalade', 'Tomaat, komkommer, dressing (vega)', 6.00],
                ['Boerensalade', 'Tomaat, komkommer, ui, olijf, feta (vega)', 6.50],
            ],
        ];
    }

    /**
     * Grouped for a <select> with optgroups / Filament: category => [value => label].
     *
     * @return array<string, array<string, string>>
     */
    public static function grouped(): array
    {
        $out = [];
        foreach (self::categories() as $category => $items) {
            foreach ($items as [$name, $desc, $price]) {
                $label = $name;
                if ($desc !== '') {
                    $label .= ' — '.$desc;
                }
                $label .= ' · € '.number_format($price, 2, ',', '.');
                $out[$category][$name] = $label;
            }
        }

        return $out;
    }

    /** All valid item names, for validation. */
    public static function values(): array
    {
        $values = [];
        foreach (self::categories() as $items) {
            foreach ($items as [$name]) {
                $values[] = $name;
            }
        }

        return $values;
    }
}
