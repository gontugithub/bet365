<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\TraitApiResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use TraitApiResponse;


    public function register(Request $request){

        $request->validate([
            'name'=> ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'alpha_num', 'min:8']

        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => "usuario"
        ]);

        $token = $user->createToken('api-token', ['read_only', 'full_access'], now()->addWeek())->plainTextToken;

        return $this->successResponse(['user' => $user, 'token' => $token], 'usario creado correctamente', 201);

    }

    public function login(Request $request){

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'alpha_num', 'min:8']

        ]);

        $user = User::where('email', $request->email)->first();

        // si no hay usuario (no se ha encontrado) o la contaseña no es la misma respuesta error

        if (! $user || ! Hash::check($request->password, $user->password)){ 

            return $this->errorResponse('login invalido, no existe usario o contraseña', 401 );

        } else {

            $user->tokens()->delete();

            $token = $user->createToken('api-token', ['read_only', 'full_access'], now()->addWeek())->plainTextToken;

            return $this->successResponse(['user' => $user, 'token' => $token], 'usario logeado y token creado', 200);
        }

    }

    public function logout(Request $request){

        $user = $request->user();

        $user->tokens()->delete();

        return $this->successResponse($user, 'usuario sin sesion', 200);

    }

}
