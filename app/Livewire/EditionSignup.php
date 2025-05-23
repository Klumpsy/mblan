<?php

namespace App\Livewire;

use App\Models\Edition;
use App\Models\Signup;
use App\Models\Beverage;
use App\Services\SignupService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EditionSignup extends Component
{
    public Edition $edition;

    public int $currentStep = 1;
    public int $totalSteps = 3;

    public array $selectedSchedules = [];
    public array $selectedBeverages = [];
    public bool $staysOnCampsite = false;
    public bool $joinsBarbecue = false;

    public function mount(Edition $edition): void
    {
        $this->edition = $edition;
    }

    public function render()
    {
        $schedules = $this->edition->schedules;
        $beverages = Beverage::all();

        return view('livewire.edition-signup', [
            'user' => Auth::user(),
            'schedules' => $schedules,
            'beverages' => $beverages,
        ]);
    }

    protected function rules()
    {
        return [
            'selectedSchedules' => [
                'required',
                'array',
                'min:1',
                Rule::exists('schedules', 'id')->where('edition_id', $this->edition->id)
            ],
            'selectedBeverages' => [
                'array',
                'max:5'
            ],
            'selectedBeverages.*' => [
                Rule::exists('beverages', 'id')
            ],
            'staysOnCampsite' => 'boolean',
            'joinsBarbecue' => 'boolean',
        ];
    }

    protected function messages()
    {
        return [
            'selectedSchedules.required' => 'Please select at least one day to participate.',
            'selectedSchedules.min' => 'Please select at least one day to participate.',
            'selectedSchedules.*.exists' => 'One or more selected days are invalid.',
            'selectedBeverages.max' => 'You can select a maximum of 5 beverages.',
            'selectedBeverages.*.exists' => 'One or more selected beverages are invalid.',
        ];
    }

    protected function validationAttributes()
    {
        return [
            'selectedSchedules' => 'participation days',
            'selectedBeverages' => 'beverage preferences',
            'staysOnCampsite' => 'campsite accommodation',
            'joinsBarbecue' => 'barbecue participation',
        ];
    }

    protected function getStepRules(int $step): array
    {
        $allRules = $this->rules();

        return match ($step) {
            1 => [
                'selectedSchedules' => $allRules['selectedSchedules'],
            ],
            2 => [
                'staysOnCampsite' => $allRules['staysOnCampsite'],
                'joinsBarbecue' => $allRules['joinsBarbecue'],
            ],
            3 => [
                'selectedBeverages' => $allRules['selectedBeverages'],
                'selectedBeverages.*' => $allRules['selectedBeverages.*'],
            ],
            default => []
        };
    }

    private function validateCurrentStep()
    {
        $this->validate(
            $this->getStepRules($this->currentStep),
            $this->messages(),
            $this->validationAttributes()
        );
    }

    public function nextStep()
    {
        $this->validateCurrentStep();

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
        $existingSignup = Signup::where('user_id', Auth::id())
            ->where('edition_id', $this->edition->id)
            ->first();

        if ($existingSignup) {
            session()->flash('error', 'You are already signed up for this edition!');
            return;
        }

        $this->validate($this->rules(), $this->messages(), $this->validationAttributes());

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
            $this->dispatch('signup-completed');
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
