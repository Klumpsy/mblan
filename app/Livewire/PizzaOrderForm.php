<?php

namespace App\Livewire;

use App\Models\PizzaOrder;
use App\Models\PizzaRound;
use App\Services\AchievementEvaluator;
use App\Support\PizzaMenu;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Lets a logged-in user place (or update) their food order for the currently
 * open round. One order per user per round; the admin sees the combined list in
 * the backend.
 */
class PizzaOrderForm extends Component
{
    public ?int $roundId = null;

    public string $pizza = '';

    public string $notes = '';

    public bool $saved = false;

    public function mount(): void
    {
        $round = PizzaRound::current();
        $this->roundId = $round?->id;

        if ($round) {
            $existing = $round->orders()->where('user_id', auth()->id())->first();
            if ($existing) {
                $this->pizza = $existing->pizza;
                $this->notes = (string) $existing->notes;
            }
        }
    }

    public function save(): void
    {
        $round = PizzaRound::current();

        if (! $round || $round->id !== $this->roundId) {
            $this->addError('pizza', 'De bestelronde is gesloten of gewijzigd. Ververs de pagina.');

            return;
        }

        $this->validate([
            'pizza' => ['required', Rule::in(PizzaMenu::values())],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'pizza.required' => 'Kies iets van het menu.',
            'pizza.in' => 'Kies een geldig item van het menu.',
        ]);

        PizzaOrder::updateOrCreate(
            ['pizza_round_id' => $round->id, 'user_id' => auth()->id()],
            ['pizza' => $this->pizza, 'notes' => $this->notes ?: null],
        );

        // Ordering food can unlock an achievement.
        app(AchievementEvaluator::class)->sync(auth()->user());

        $this->saved = true;
        $this->dispatch('toast', message: 'Je bestelling is opgeslagen. Smakelijk!');
    }

    public function render()
    {
        $round = $this->roundId ? PizzaRound::find($this->roundId) : null;
        $myOrder = $round
            ? $round->orders()->where('user_id', auth()->id())->first()
            : null;

        // Everyone may see the round's combined list, so you know what the
        // group ordered without asking the organisation.
        $orders = $round
            ? $round->orders()->with('user')->oldest()->get()
            : collect();

        return view('livewire.pizza-order-form', [
            'round' => $round,
            'menu' => PizzaMenu::grouped(),
            'myOrder' => $myOrder,
            'orders' => $orders,
        ]);
    }
}
