<?php declare(strict_types=1);

namespace App\Http\Controllers\Settings\Payment;

use App\Exceptions\UserNotConnectedToMollie;
use App\PaymentProfile;
use App\Services\AuthenticatedUserLoader;
use App\Services\Mollie\PaymentMethodService;
use App\Services\Mollie\StatusService;
use App\Services\Mollie\UserPaymentProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class StatusController
{
    /** @var StatusService */
    private StatusService $onboardingService;

    /** @var AuthenticatedUserLoader */
    private AuthenticatedUserLoader $userLoader;

    /** @var UserPaymentProfileService */
    private UserPaymentProfileService $profileService;

    /** @var PaymentMethodService */
    private PaymentMethodService $paymentMethodsService;

    public function __construct(
        StatusService $onboardingService,
        AuthenticatedUserLoader $userLoader,
        UserPaymentProfileService $profileService,
        PaymentMethodService $paymentMethodsService
    ) {
        $this->onboardingService = $onboardingService;
        $this->userLoader = $userLoader;
        $this->profileService = $profileService;
        $this->paymentMethodsService = $paymentMethodsService;
    }

    /**
     * @return RedirectResponse|View
     * @throws IdentityProviderException
     */
    public function __invoke(Request $request)
    {
        $user = $this->userLoader->load();

        try {
            $status = $this->onboardingService->getOnboardingStatus($user);
        } catch (UserNotConnectedToMollie $e) {
            return redirect(route('connect_to_mollie'));
        }

        $profiles = $this->profileService->loadUserProfile($user);
        $selected = $this->getSelectedProfile($request->get('profile'), $profiles);

        $methods = $this->paymentMethodsService->loadFromProfile($user, $selected);

        return view('settings.payment.status', [
            'status' => $status,
            'profiles' => $profiles,
            'methods' => $methods
        ]);
    }

    /**
     * @param string|null $profileId
     * @param PaymentProfile[] $profiles
     * @return PaymentProfile
     */
    private function getSelectedProfile(?string $profileId, array $profiles): PaymentProfile
    {
        $selected = current(
            array_filter($profiles, function (PaymentProfile $profile) use ($profileId): bool {
                return $profile->getId() === $profileId;
            })
        );

        if (!$selected) {
            return current($profiles);
        }

        return $selected;
    }
}
