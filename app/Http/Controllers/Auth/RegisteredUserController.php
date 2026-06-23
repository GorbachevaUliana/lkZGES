<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    // app/Http/Controllers/Auth/RegisteredUserController.php

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 1. Создаем пользователя с ролью 'guest' (новый пользователь)
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);
        // role убрана из $fillable — выставляем явно через forceFill.
        $user->forceFill(['role' => 'guest'])->save();

        // Намеренно НЕ делаем автопривязку клиента по email.
        // Раньше здесь был блок: Client::where('email', $email)->first()
        // с автоматическим $client->update(['user_id' => $user->id]).
        // Это позволяло любому, знающему email клиента, зарегистрироваться
        // и сразу получить доступ к чужим данным, минуя верификацию.
        //
        // Теперь все пользователи — включая тех, кого администратор завёл
        // заранее — проходят через WelcomePage: вводят ЛС + ФИО + код на email.
        // Если у клиента нет email или утерян доступ — сотрудник привязывает
        // аккаунт вручную через админку (AdminClientController::update).

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('welcome.step'));
    }
}