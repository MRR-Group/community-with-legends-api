<?php

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Enums\Role;
use CommunityWithLegends\Helpers\IdenticonHelper;
use CommunityWithLegends\Models\User;
use Illuminate\Support\Env;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response as Status;

class TwitchController extends Controller
{
    public function loginByAuthCode(Request $request, string $platform)
    {
        $authenticationCode = $request->get("code");

        $error = $request->get("error");

        if ($error) {
            $error_description = $request->get("error_description");

            return response()->json([
                'error' => $error,
                'error_description' => $error_description,
            ]);
        }

        $accessTokenData = $this->getAccessToken($authenticationCode);
        $errorMessage = $accessTokenData->get('message');

        if ($errorMessage) {
            return response()->json($accessTokenData);
        }

        $userDetailsResponse = $this->getUserDetails($accessTokenData->get('access_token'));

        $userDetails = collect($userDetailsResponse[0][0]);
        $email = $userDetails->get('email');

        if ($email) {
            $user = User::query()->where('email', $email)->first();
            if ($user) {
                $token = $user->createToken("api-token")->plainTextToken;

                Auth::login($user);

                return match ($platform) {
                    'mobile' => redirect()->to(Env::get('TWITCH_LOGIN_REDIRECT_URL_MOBILE') . '?token=' . $token),
                    'web' => redirect()->to(Env::get('TWITCH_LOGIN_REDIRECT_URL_WEB')),
                    default => response()->json([
                        'message' => 'Invalid platform',
                    ], Status::HTTP_BAD_REQUEST),
                };
            }else{
                return response()->json([
                    'message' => "No account was found linked to this email address from Twitch. Please make sure you're using the correct account or sign up to continue.",
                    'email' => $email,
                    'user' => $user,
                ], Status::HTTP_NOT_FOUND);
            }
        }

        return response()->json([
            'message' => 'Failed to log in with Twitch. Please try again.',
        ], Status::HTTP_UNAUTHORIZED);
    }

    public function registerByAuthCode(Request $request, IdenticonHelper $identiconHelper, string $platform)
    {
        $authenticationCode = $request->get("code");

        $error = $request->get("error");

        if ($error) {
            $error_description = $request->get("error_description");

            return response()->json([
                'error' => $error,
                'error_description' => $error_description,
            ]);
        }

        $accessTokenData = $this->getAccessToken($authenticationCode);
        $errorMessage = $accessTokenData->get('message');

        if ($errorMessage) {
            return response()->json($accessTokenData);
        }

        $userDetailsResponse = $this->getUserDetails($accessTokenData->get('access_token'));

        $userDetails = collect($userDetailsResponse[0][0]);
        $email = $userDetails->get('email');
        $username = $userDetails->get('display_name');

        if ($email == null) {
            return response()->json([
                'message' => 'Failed to log in with Twitch. Please try again.',
                'email' => $email,
            ], Status::HTTP_BAD_REQUEST);
        }

        $userExist = User::query()->where('email', $email)->first();

        if ($userExist) {
            return response()->json([
                'message' => 'An account with this email address already exists. Assign a twitch account in settings',
                'email' => $email,
            ], Status::HTTP_CONFLICT);
        }

        $user = new User(
            [
                'email' => $email,
                'name' => $username
            ]
        );
        $user->markEmailAsVerified();
        $user->save();

        $identiconHelper->create($user->id, $user->email);

        $user->assignRole(Role::User);
        $user->syncPermissions(Role::User->permissions());

        $token = $user->createToken("api-token")->plainTextToken;

        Auth::login($user);

        return match ($platform) {
            'mobile' => redirect()->to(Env::get('TWITCH_LOGIN_REDIRECT_URL_MOBILE') . '?token=' . $token),
            'web' => redirect()->to(Env::get('TWITCH_LOGIN_REDIRECT_URL_WEB')),
            default => response()->json([
                'message' => 'Invalid platform',
            ], Status::HTTP_BAD_REQUEST),
        };

    }

    public function receiveAccessToken(Request $request): Collection
    {
        $access_token = $request->get("access_token");
        $expires_in = $request->get("expires_in");

        $refresh_token = $request->get("refresh_token");
        $scope = $request->get("scope");
        $token_type = $request->get("token_type");

        $errorMessage = $request->get("message");

        if ($errorMessage) {
            return collect([
                'error_message' => $errorMessage,
            ]);
        }

        return collect([
            'access_token' => $access_token,
            'expires_in' => $expires_in,
            'refresh_token' => $refresh_token,
            'scope' => $scope,
            'token_type' => $token_type,
        ]);
    }

    private function getAccessToken(string $authenticationCode): Collection
    {
        $response = Http::post('https://id.twitch.tv/oauth2/token', [
            'client_id' => config("twitch.client_id"),
            'client_secret' => config("twitch.client_secret"),
            'code' => $authenticationCode,
            'grant_type' => 'authorization_code',
            'redirect_uri' => config("twitch.token_redirect_uri"),
        ]);

        return collect($response->json());
    }

    private function getUserDetails(string $accessToken): Collection
    {
        $response = Http::withHeaders([
            'Client-Id' => config("twitch.client_id"),
            'Authorization' => "Bearer $accessToken"
        ])->get('https://api.twitch.tv/helix/users');

        $data = collect($response->json());

        $errorMessage = $data->get('message');

        if ($errorMessage) {
            return collect($data);
        }

        $userData = collect($data->get('data'));

        return collect([$userData]);
    }
}
