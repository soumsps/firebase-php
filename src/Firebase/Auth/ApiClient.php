<?php

namespace Kreait\Firebase\Auth;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\Auth\CredentialsMismatch;
use Kreait\Firebase\Exception\Auth\EmailNotFound;
use Kreait\Firebase\Exception\Auth\InvalidCustomToken;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Request;
use Lcobucci\JWT\Token;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Takes a custom token and exchanges it with an ID token.
     *
     * @param Token $token
     *
     * @see https://firebase.google.com/docs/reference/rest/auth/#section-verify-custom-token
     *
     * @throws InvalidCustomToken
     * @throws CredentialsMismatch
     *
     * @return ResponseInterface
     */
    public function exchangeCustomTokenForIdAndRefreshToken(Token $token): ResponseInterface
    {
        return $this->request('verifyCustomToken', [
            'token' => (string) $token,
            'returnSecureToken' => true,
        ]);
    }

    public function createUser(Request\CreateUser $request): ResponseInterface
    {
        return $this->request('signupNewUser', $request);
    }

    public function updateUser(Request\UpdateUser $request): ResponseInterface
    {
        return $this->request('setAccountInfo', $request);
    }

    /**
     * Returns a user for the given email address.
     *
     * @param string $email
     *
     * @throws EmailNotFound
     *
     * @return ResponseInterface
     */
    public function getUserByEmail(string $email): ResponseInterface
    {
        return $this->request('getAccountInfo', [
            'email' => [$email],
        ]);
    }

    /**
     * Returns a user for the given phone number.
     *
     * @param string $phoneNumber
     *
     * @return ResponseInterface
     */
    public function getUserByPhoneNumber(string $phoneNumber): ResponseInterface
    {
        return $this->request('getAccountInfo', [
            'phoneNumber' => [$phoneNumber],
        ]);
    }

    public function downloadAccount(int $batchSize = null, string $nextPageToken = null): ResponseInterface
    {
        $batchSize = $batchSize ?? 1000;

        return $this->request('downloadAccount', array_filter([
            'maxResults' => $batchSize,
            'nextPageToken' => $nextPageToken,
        ]));
    }

    public function deleteUser(string $uid): ResponseInterface
    {
        return $this->request('deleteAccount', [
            'localId' => $uid,
        ]);
    }

    public function getAccountInfo(string $uid): ResponseInterface
    {
        return $this->request('getAccountInfo', [
            'localId' => [$uid],
        ]);
    }

    public function verifyPassword(string $email, string $password): ResponseInterface
    {
        return $this->request('verifyPassword', [
            'email' => $email,
            'password' => $password,
        ]);
    }

    public function sendEmailVerification(string $idToken, string $continueUrl = null, string $locale = null): ResponseInterface
    {
        $headers = $locale ? ['X-Firebase-Locale' => $locale] : null;

        $data = array_filter([
            'requestType' => 'VERIFY_EMAIL',
            'idToken' => $idToken,
            'continueUrl' => $continueUrl,
        ]);

        return $this->request('getOobConfirmationCode', $data, $headers);
    }

    public function sendPasswordResetEmail(string $email, string $continueUrl = null, string $locale = null): ResponseInterface
    {
        $headers = $locale ? ['X-Firebase-Locale' => $locale] : null;

        $data = array_filter([
            'email' => $email,
            'requestType' => 'PASSWORD_RESET',
            'continueUrl' => $continueUrl,
        ]);

        return $this->request('getOobConfirmationCode', $data, $headers);
    }

    public function revokeRefreshTokens(string $uid): ResponseInterface
    {
        return $this->request('setAccountInfo', [
            'localId' => $uid,
            'validSince' => time(),
        ]);
    }

    public function unlinkProvider(string $uid, array $providers): ResponseInterface
    {
        return $this->request('setAccountInfo', [
            'localId' => $uid,
            'deleteProvider' => $providers,
        ]);
    }

    private function request(string $uri, $data, array $headers = null): ResponseInterface
    {
        if ($data instanceof \JsonSerializable && empty($data->jsonSerialize())) {
            $data = (object) []; // Will be '{}' instead of '[]' when JSON encoded
        }

        $options = array_filter([
            'json' => $data,
            'headers' => $headers,
        ]);

        try {
            return $this->client->request('POST', $uri, $options);
        } catch (RequestException $e) {
            throw AuthException::fromRequestException($e);
        } catch (\Throwable $e) {
            throw new AuthException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
