<?php

namespace App\Livewire\Edition;

use App\Models\Edition;
use App\Models\Signup as SignupModel;
use App\Models\Beverage;
use App\Services\SignupService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Signup extends Component
{
    public Edition $edition;

    public int $currentStep = 1;
    public int $totalSteps = 3;

    #[Rule('required|array|min:1')]
    public array $selectedSchedules = [];

    #[Rule('array|max:5')]
    public array $selectedBeverages = [];

    #[Rule('boolean')]
    public bool $staysOnCampsite = false;

    #[Rule('boolean')]
    public bool $joinsBarbecue = false;

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

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
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
                joinsBarbecue: $this->joinsBarbecue
            );

            session()->flash('success', 'Successfully signed up for ' . $this->edition->name . '!');
            $this->redirect('/dashboard');
            $this->reset(['currentStep', 'selectedSchedules', 'selectedBeverages', 'staysOnCampsite', 'joinsBarbecue']);
        } catch (\Exception $e) {
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
            default => 'Step ' . $step
        };
    }
}
