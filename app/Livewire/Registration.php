<?php

namespace App\Http\Livewire;

use App\Models\Edition;
use App\Models\Registration;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class EditionRegistration extends Component
{
    public $editionId;
    public $edition;
    public $attendanceDays = [];
    public $stayingForCamping = false;
    public $dietaryRequirements = '';
    public $equipment = '';
    public $additionalNotes = '';
    public $registrationSubmitted = false;
    public $registrationStatus = null;
    public $showPaymentButton = false;

    public $availableDays = [];

    public function mount($editionId)
    {
        $this->editionId = $editionId;
        $this->edition = Edition::findOrFail($editionId);

        $scheduleDays = $this->edition->schedules()
            ->selectRaw('DATE(start_time) as day')
            ->distinct()
            ->orderBy('day')
            ->pluck('day')
            ->toArray();

        foreach ($scheduleDays as $day) {
            $date = new \DateTime($day);
            $dayName = strtolower($date->format('l'));
            $formattedDate = $date->format('F j');
            $this->availableDays[$dayName] = "{$date->format('l')} ({$formattedDate})";
        }

        if (empty($this->availableDays)) {
            $this->availableDays = [
                'friday' => 'Friday (Day 1)',
                'saturday' => 'Saturday (Day 2)',
                'sunday' => 'Sunday (Day 3)',
            ];
        }

        if (Auth::check()) {
            $registration = Registration::where('user_id', Auth::id())
                ->where('edition_id', $this->editionId)
                ->first();

            if ($registration) {
                $this->registrationSubmitted = true;
                $this->registrationStatus = $registration->status;
                $this->showPaymentButton = $registration->status === 'approved' && !$registration->is_paid;

                if ($registration->status === 'pending') {
                    $this->attendanceDays = $registration->attendance_days;
                    $this->stayingForCamping = $registration->staying_for_camping;
                    $this->dietaryRequirements = $registration->dietary_requirements;
                    $this->equipment = $registration->equipment;
                    $this->additionalNotes = $registration->additional_notes;
                }
            }
        }
    }

    public function submitRegistration()
    {
        $this->validate([
            'attendanceDays' => 'required|array|min:1',
            'dietaryRequirements' => 'nullable|string|max:500',
            'equipment' => 'nullable|string|max:500',
            'additionalNotes' => 'nullable|string|max:1000',
        ]);

        $registration = Registration::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'edition_id' => $this->editionId
            ],
            [
                'attendance_days' => $this->attendanceDays,
                'staying_for_camping' => $this->stayingForCamping,
                'dietary_requirements' => $this->dietaryRequirements,
                'equipment' => $this->equipment,
                'additional_notes' => $this->additionalNotes,
                'status' => 'pending',
                'is_paid' => false,
            ]
        );

        $this->registrationSubmitted = true;
        $this->registrationStatus = 'pending';

        session()->flash('message', 'Registration submitted successfully! We will review your application and notify you when it\'s approved.');
    }

    public function initiatePayment()
    {
        return redirect()->route('edition.payment.initiate', ['editionId' => $this->editionId]);
    }

    public function render()
    {
        return view('livewire.edition-registration');
    }
}
