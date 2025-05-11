<?php

namespace CommunityWithLegends\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class TwitchController extends Controller
{
    public function loginByAuthCode(Request $request): JsonResponse
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

        $userDetails = $this->getUserDetails($accessTokenData->get('access_token'));

        return response()->json($userDetails);

        // TODO: add login functionality
    }

    // TODO: add registerByAuthCode

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
