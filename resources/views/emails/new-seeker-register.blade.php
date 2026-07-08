@component('mail::message')
    <h2>Assalamualaikum Warahmatullahi Wabarakatuh</h2>
    
    We have a new seeker who has registered on the {{ config('app.name', 'Job-sphere-rwanda') }} system.

@component('mail::panel')
    <p>The details are as follows:</p>

    Firstname: {{ $firstname }}
    Lastname : {{ $lastname }}
    Gender : {{ $gender }}
    Email : {{ $email }}
    Birthdate : {{ $birthdate }}

@endcomponent

<p>Now you have {{ $count_users }} users in {{ config('app.name', 'Job-sphere-rwanda') }}</p>

<p>Thank you, and have a great day!</p>
@endcomponent

