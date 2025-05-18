<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Enums\Role;
use CommunityWithLegends\Helpers\IdenticonHelper;
use CommunityWithLegends\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response as Status;

class TwitchController extends Controller
{
    public function loginByAuthCode(Request $request, string $platform)
    {
        $userDetails = $this->getUserDetails($request, $platform);

        if ($userDetails instanceof RedirectResponse) {
            return $userDetails;
        }

        $email = $userDetails->get("email");

        if ($email) {
            $user = User::query()->where("email", $email)->first();

            if ($user) {
                $token = $user->createToken("api-token")->plainTextToken;

                Auth::login($user);

                return $this->redirectByPlatform($platform, $token);
            }

            return $this->redirectErrorByPlatform($platform, "No account was found linked to this email address from Twitch. Please make sure you're using the correct account or sign up to continue.");
        }

        return $this->redirectErrorByPlatform($platform, "Failed to log in with Twitch. Please try again.");
    }

    public function registerByAuthCode(Request $request, IdenticonHelper $identiconHelper, string $platform)
    {
        $userDetails = $this->getUserDetails($request, $platform);

        if ($userDetails instanceof RedirectResponse) {
            return $userDetails;
        }

        $email = $userDetails->get("email");
        $username = $userDetails->get("display_name");

        if ($email === null) {
            return $this->redirectErrorByPlatform($platform, "Failed to register via Twitch. Please try again.");
        }

        $userExist = User::query()->where("email", $email)->first();

        if ($userExist) {
            return $this->redirectErrorByPlatform($platform, "An account with this email address already exists. Assign a twitch account in settings");
        }

        $user = new User(
            [
                "email" => $email,
                "name" => $username,
            ],
        );
        $user->markEmailAsVerified();
        $user->save();

        $identiconHelper->create($user->id, $user->email);

        $user->assignRole(Role::User);
        $user->syncPermissions(Role::User->permissions());

        $token = $user->createToken("api-token")->plainTextToken;

        Auth::login($user);

        return $this->redirectByPlatform($platform, $token);
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
                "error_message" => $errorMessage,
            ]);
        }

        return collect([
            "access_token" => $access_token,
            "expires_in" => $expires_in,
            "refresh_token" => $refresh_token,
            "scope" => $scope,
            "token_type" => $token_type,
        ]);
    }

    private function getUserDetails(Request $request, string $platform): Collection|RedirectResponse
    {
        $authenticationCode = $request->get("code");
        $error_description = $request->get("error_description");

        if ($error_description) {
            return $this->redirectErrorByPlatform($platform, $error_description);
        }

        $accessTokenData = $this->getAccessToken($authenticationCode);
        $errorMessage = $accessTokenData->get("message");

        if ($errorMessage) {
            return $this->redirectErrorByPlatform($platform, $errorMessage);
        }

        $userDetailsResponse = $this->getTwitchUser($accessTokenData->get("access_token"));

        return collect($userDetailsResponse[0][0]);
    }

    /**
     * @return Collection<string, mixed>
     */
    private function getAccessToken(string $authenticationCode): Collection
    {
        $response = Http::post("https://id.twitch.tv/oauth2/token", [
            "client_id" => config("twitch.client_id"),
            "client_secret" => config("twitch.client_secret"),
            "code" => $authenticationCode,
            "grant_type" => "authorization_code",
            "redirect_uri" => config("twitch.token_redirect_uri"),
        ]);

        return collect($response->json());
    }

    private function getTwitchUser(string $accessToken): Collection
    {
        $response = Http::withHeaders([
            "Client-Id" => config("twitch.client_id"),
            "Authorization" => "Bearer $accessToken",
        ])->get("https://api.twitch.tv/helix/users");

        $data = collect($response->json());

        $errorMessage = $data->get("message");

        if ($errorMessage) {
            return collect($data);
        }

        $userData = collect($data->get("data"));

        return collect([$userData]);
    }

    private function redirectByPlatform(string $platform, string $token): JsonResponse|RedirectResponse
    {
        return match ($platform) {
            "mobile" => redirect()->away(config("twitch.login_redirect_url_mobile") . "?token=" . $token),
            "web" => redirect()->away(config("twitch.login_redirect_url_web")),
            default => response()->json([
                "message" => "Invalid platform",
            ], Status::HTTP_BAD_REQUEST),
        };
    }

    private function redirectErrorByPlatform(string $platform, string $message): JsonResponse|RedirectResponse
    {
        return match ($platform) {
            "mobile" => redirect()->away(config("twitch.login_error_redirect_url_mobile") . "?message=" . $message),
            "web" => redirect()->away(config("twitch.login_error_redirect_url_web") . "?message=" . $message),
            default => response()->json([
                "message" => "Invalid platform",
            ], Status::HTTP_BAD_REQUEST),
        };
    }
}
