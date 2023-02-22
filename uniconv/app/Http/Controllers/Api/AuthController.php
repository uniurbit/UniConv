<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;
use App\User;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','refresh']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            auth()->logout();
            return response()->json([
              'status' => 'success',
              'msg' => 'You have successfully logged out.'
            ]);
          } catch (JWTException $e) {
              JWTAuth::unsetToken();
              // something went wrong tries to validate a invalid token
              return response()->json([
                'status' => 'error',
                'msg' => 'Failed to logout, please try again.'
            ]);
          }
       
        //redirect('saml2/logout'); 
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function cambiautente(Request $request)
    {   
        if (!Auth::user()->hasRole('super-admin')){
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $user = User::find($request->id);
        if ($user) {
            Auth::login($user);
            $token = JWTAuth::fromUser($user);
            return response()->json(compact('token'));
        }
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {  
        //$new_token  = JWTAuth::refresh($request->token);
        //$token = $this->jwtAuth->getToken();
        //$token = auth()->refresh();

        $token = JWTAuth::getToken();

        if (! $token) {
            throw new BadRequestHttpException('Token not provided');
        }

        //try {
        $token = auth()->refresh();
        //$token = JWTAuth::refresh($request->refreshToken);
        //} catch (TokenInvalidException $e) {
        //    throw new AccessDeniedHttpException('The token is invalid');
        //}

        return response()->json(compact('token'));
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}