<?php


namespace App\Http\Controllers\Auth;

use App\Services\AuthenticatedUserLoader;
use App\Services\Mollie\GetOnboardingStatusService;
use App\Services\Mollie\SubmitOnboardingDataService;

class RedirectToMollieOnboardingAfterAuthorization
{
    /**
     * @var GetOnboardingStatusService
     */
    private GetOnboardingStatusService $onboardingStatusService;

    /**
     * @var AuthenticatedUserLoader
     */
    private AuthenticatedUserLoader $userLoader;
    /**
     * @var SubmitOnboardingDataService
     */
    private SubmitOnboardingDataService $submitOnboardingDataService;

    public function __construct(
        GetOnboardingStatusService $onboardingStatusService,
        AuthenticatedUserLoader $userLoader,
        SubmitOnboardingDataService $submitOnboardingDataService
    )
    {
        $this->onboardingStatusService = $onboardingStatusService;
        $this->userLoader = $userLoader;
        $this->submitOnboardingDataService = $submitOnboardingDataService;
    }

    public function __invoke()
    {
        $user = $this->userLoader->load();

        $mollieStatus = $this->onboardingStatusService->getOnboardingStatus($user);

        if (!$mollieStatus->needsData()) {
            return redirect(route("payment_status"));
        }

        $this->submitOnboardingDataService->submitOnboardingData($user);

        return redirect($mollieStatus->getDashboardLink());
    }
}
