<?php

namespace App\Livewire\Edition;

use App\Enums\TshirtSizeType;
use App\Mail\Welcome;
use App\Models\Edition;
use App\Models\Signup as SignupModel;
use App\Models\Beverage;
use App\Services\SignupService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Enum;

class Signup extends Component
{
    public Edition $edition;

    public int $currentStep = 1;
    public int $totalSteps = 4;
    public bool $joinsOnFriday = false;
    public bool $joinsOnSaturday = false;

    #[Rule('required|array|min:1')]
    public array $selectedSchedules = [];

    #[Rule('array')]
    public array $selectedBeverages = [];

    #[Rule('boolean')]
    public bool $staysOnCampsite = false;

    #[Rule('boolean')]
    public bool $joinsBarbecue = false;

    #[Rule('boolean')]
    public bool $joinsPizza = false;

    #[Rule('boolean')]
    public bool $isVegan = false;

    #[Rule('boolean')]
    public bool $wantsTshirt = false;

    #[Rule('max:20')]
    public string $tshirtText = '';

    #[Rule(new Enum(TshirtSizeType::class))]
    public TshirtSizeType $tshirtSize = TshirtSizeType::SIZE_M;

    public function mount(Edition $edition): void
    {
        $this->edition = $edition;
    }

    public function render()
    {
        $schedules = $this->edition->schedules;
        $beverages = Beverage::all();

        return view('livewire.edition.signup', [
            'user' => Auth::user(),
            'schedules' => $schedules,
            'beverages' => $beverages,
        ]);
    }

    public function nextStep()
    {
        $this->validate();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function updatedSelectedSchedules(): void
    {
        $this->joinsOnFriday = $this->computeJoinsOnFriday();
        $this->joinsOnSaturday = $this->computeJoinsOnSaturday();

        if (!$this->joinsOnSaturday) {
            $this->joinsBarbecue = false;
            $this->isVegan = false;
        }

        if (!$this->joinsOnFriday) {
            $this->joinsPizza = false;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function getSelectedScheduleNamesProperty(): array
    {
        return $this->edition->schedules
            ->whereIn('id', $this->selectedSchedules)
            ->pluck('name')
            ->toArray();
    }

    public function getSelectedBeverageNamesProperty(): array
    {
        return Beverage::whereIn('id', $this->selectedBeverages)
            ->pluck('name')
            ->toArray();
    }

    private function computeJoinsOnFriday(): bool
    {
        foreach ($this->selectedSchedules as $scheduleId) {
            $schedule = $this->edition->schedules->find($scheduleId);
            if ($schedule && $schedule->date && Carbon::parse($schedule->date)->isFriday()) {
                return true;
            }
        }
        return false;
    }

    private function computeJoinsOnSaturday(): bool
    {
        foreach ($this->selectedSchedules as $scheduleId) {
            $schedule = $this->edition->schedules->find($scheduleId);
            if ($schedule && $schedule->date && Carbon::parse($schedule->date)->isSaturday()) {
                return true;
            }
        }
        return false;
    }

    public function signup()
    {
        $existingSignup = SignupModel::where('user_id', Auth::id())
            ->where('edition_id', $this->edition->id)
            ->first();

        if ($existingSignup) {
            session()->flash('error', 'You are already signed up for this edition!');
            return;
        }

        $this->validate();

        try {
            app(SignupService::class)->createSignup(
                user: Auth::user(),
                edition: $this->edition,
                schedules: $this->selectedSchedules,
                beverages: $this->selectedBeverages,
                staysOnCampsite: $this->staysOnCampsite,
                joinsBarbecue: $this->joinsBarbecue,
                joinsPizza: $this->joinsPizza,
                isVegan: $this->isVegan,
                wantsTshirt: $this->wantsTshirt,
                tshirtSize: $this->tshirtSize,
                tshirtText: $this->tshirtText
            );

            session()->flash('success', 'Successfully signed up for ' . $this->edition->name . '!');

            $signup = Auth::user()->signups()->where('edition_id', $this->edition->id)->first();
            Mail::to(Auth::user()->email)->queue(new Welcome($signup));

            $this->redirect('/dashboard');
            $this->reset(['currentStep', 'selectedSchedules', 'selectedBeverages', 'staysOnCampsite', 'joinsBarbecue']);
        } catch (\Exception $e) {
            Log::error('Signup error: ' . $e->getMessage(), ['exception' => $e]);
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }

    public function isStepCompleted($step)
    {
        switch ($step) {
            case 1:
                return !empty($this->selectedSchedules);
            case 2:
                return true;
            case 3:
                return true;
            case 4:
                return true;
            default:
                return false;
        }
    }

    public function getStepTitle($step)
    {
        return match ($step) {
            1 => 'Select Days',
            2 => 'Preferences',
            3 => 'Beverages',
            4 => $this->edition->name . ' T-Shirt',
            default => 'Step ' . $step
        };
    }
}
