<?php

namespace App\Support;

use App\Models\Edition;

/**
 * Resolves the edition the visitor is currently "viewing".
 *
 * Priority: the edition chosen via the navbar switcher (stored in the session),
 * then the edition flagged active, then the most recent edition by year. This is
 * what drives both the site's accent colour and the content shown per edition,
 * and it scales to any number of editions.
 */
class CurrentEdition
{
    private ?Edition $resolved = null;
    private bool $resolvedSet = false;

    public const SESSION_KEY = 'viewing_edition_id';

    public function get(): ?Edition
    {
        if ($this->resolvedSet) {
            return $this->resolved;
        }
        $this->resolvedSet = true;

        $id = session(self::SESSION_KEY);
        if ($id && ($edition = Edition::find($id))) {
            return $this->resolved = $edition;
        }

        return $this->resolved = Edition::where('is_active', true)->first()
            ?? Edition::orderByDesc('year')->first();
    }

    public function id(): ?int
    {
        return $this->get()?->id;
    }

    public function color(): string
    {
        return ThemeService::normalizeHex($this->get()?->color) ?? ThemeService::DEFAULT_COLOR;
    }

    public function set(Edition $edition): void
    {
        session([self::SESSION_KEY => $edition->id]);
        $this->resolved = $edition;
        $this->resolvedSet = true;
    }
}
