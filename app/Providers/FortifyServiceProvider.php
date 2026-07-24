<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        $this->configureDutchMails();
    }

    /**
     * The verification ("welcome") and password reset mails, in Dutch and in the
     * MBLAN26 tone: plain, no icons, no decorative dashes.
     */
    protected function configureDutchMails(): void
    {
        VerifyEmail::toMailUsing(function ($notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject('Bevestig je e-mailadres voor MBLAN26')
                ->greeting('Welkom bij MBLAN26')
                ->line('Leuk dat je erbij bent. Bevestig je e-mailadres met de knop hieronder, dan kun je het speelschema en de toernooien in.')
                ->action('E-mailadres bevestigen', $url)
                ->line('Heb je geen account aangemaakt? Dan hoef je niets te doen.')
                ->salutation("Tot bij de schuur,\nTeam MBLAN26");
        });

        ResetPassword::toMailUsing(function ($notifiable, string $token): MailMessage {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('Wachtwoord opnieuw instellen voor MBLAN26')
                ->greeting('Wachtwoord opnieuw instellen')
                ->line('Je ontvangt deze mail omdat er een verzoek is gedaan om je wachtwoord opnieuw in te stellen.')
                ->action('Wachtwoord opnieuw instellen', $url)
                ->line("Deze link verloopt over {$minutes} minuten.")
                ->line('Heb je hier niet om gevraagd? Dan hoef je niets te doen.')
                ->salutation("Tot bij de schuur,\nTeam MBLAN26");
        });
    }
}
