<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Events\EventNovoRegistro;
use App\Events\EventResetarSenha;

class AutenticadorControlador extends Controller
{
    public function registro(Request $request) {
        
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);
        
        $user = new User([
            'name'=> $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'token' => str_random(60)
        ]);
        
        $user->save();
        
        event(new EventNovoRegistro($user));

        return response()->json([
            'res'=>'Usuario criado com sucesso'
        ], 201);
    }

    public function login(Request $request) {
        
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);
        $credenciais = [
            'email' => $request->email,
            'password' => $request->password,
            'active' => 1
        ];

        if (!Auth::attempt($credenciais))
            return response()->json([
                'res' => 'Acesso negado'
            ], 401);
        
        $user = $request->user();
        $token = $user->createToken('Token de acesso')->accessToken;

        return response()->json([
            'token' => $token
        ], 200);
    }

    public function logout(Request $request) {
        $request->user()->token()->revoke();
        return response()->json([
            'res' => 'Deslogado com sucesso'
        ]);
    }

    public function ativarregistro($id, $token) {
        $user = User::find($id);

        if ($user) {
            if ($user->token == $token) {
                $user->active = true;
                $user->token = '';
                $user->save();
                return view('registroativo');
            }
        }
        return view('registroerro');
    }

    public function redefinirsenha($id, Request $request) {
        $user = User::find($id);

        if ($user) {
                $user->token = str_random(60);
                $user->save();
                
                event(new EventResetarSenha($user));

                return view('redefinicaosenhaemailenviado');

                return response()->json([
                    'res'=>'Email de redefinição enviado com sucesso '
                ], 201);
        }
        return view('registroerro');

    } 

    public function resetarsenha($id, $token, Request $request) {
        if (!$request->password == $request->password_confirmation) {
            return response()->json([
                'res'=>'Erro na confirmação da senha !'
            ]);
        }

        $user = User::find($id);

        if ($user) {
            if ($user->token == $token) {
                $user->token = '';
                $user->password = bcrypt($request->password);
                $user->save();

                return view('confirmacaoresetsenha');

                return response()->json([
                    'res'=>'Senha redefinida com sucesso '
                ], 201);
            }

        }
        
    }



}
