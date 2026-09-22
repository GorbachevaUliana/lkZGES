<?php

namespace Tests\Unit;

use App\Enums\SignatureMethod;
use App\Enums\SigningReason;
use App\Models\Application;
use App\Services\ContractSigningModeService;
use Tests\TestCase;

class ContractSigningModeServiceTest extends TestCase
{
    private ContractSigningModeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ContractSigningModeService();

        // Не зависим от конфига проекта: порог задаём явно
        config(['contracts.signing_power_threshold_kw' => 670]);
    }

    private function makeApplication(
        ?float $power,
        ?bool $requested = null,
        string $clientType = 'individual'
    ): Application {
        $application = new Application();
        $application->client_type       = $clientType;
        $application->max_power_kw      = $power;
        $application->signing_requested = $requested;

        return $application;
    }

    public function test_signing_is_required_above_threshold(): void
    {
        $result = $this->service->resolve($this->makeApplication(1500.0));

        $this->assertTrue($result['signing_required']);
        $this->assertSame(SigningReason::PowerThreshold->value, $result['signing_reason']);
    }

    public function test_signing_is_required_exactly_at_threshold(): void
    {
        $result = $this->service->resolve($this->makeApplication(670.0));

        $this->assertTrue($result['signing_required']);
        $this->assertSame(SigningReason::PowerThreshold->value, $result['signing_reason']);
    }

    public function test_signing_is_not_required_just_below_threshold(): void
    {
        $result = $this->service->resolve($this->makeApplication(669.99));

        $this->assertFalse($result['signing_required']);
        $this->assertSame(SigningReason::NotRequired->value, $result['signing_reason']);
    }

    public function test_client_cannot_opt_out_above_threshold(): void
    {
        $result = $this->service->resolve($this->makeApplication(700.0, false));

        $this->assertTrue($result['signing_required']);
        $this->assertSame(SigningReason::PowerThreshold->value, $result['signing_reason']);
    }

    public function test_signing_is_required_when_client_asked(): void
    {
        $result = $this->service->resolve($this->makeApplication(15.0, true));

        $this->assertTrue($result['signing_required']);
        $this->assertSame(SigningReason::ClientRequest->value, $result['signing_reason']);
    }

    public function test_signing_is_not_required_when_client_declined(): void
    {
        $result = $this->service->resolve($this->makeApplication(15.0, false));

        $this->assertFalse($result['signing_required']);
    }

    public function test_signing_is_not_required_when_question_was_not_asked(): void
    {
        $result = $this->service->resolve($this->makeApplication(15.0, null));

        $this->assertFalse($result['signing_required']);
    }

    public function test_missing_power_does_not_require_signing(): void
    {
        $result = $this->service->resolve($this->makeApplication(null));

        $this->assertFalse($result['signing_required']);
        $this->assertNull($result['max_power_kw']);
    }

    public function test_individual_signs_with_pep(): void
    {
        $result = $this->service->resolve($this->makeApplication(15.0, true, 'individual'));

        $this->assertSame(SignatureMethod::Pep->value, $result['signature_method']);
    }

    public function test_legal_entity_signs_with_ukep(): void
    {
        $result = $this->service->resolve($this->makeApplication(15.0, true, 'legal'));

        $this->assertSame(SignatureMethod::Ukep->value, $result['signature_method']);
    }

    public function test_power_rule_is_false_without_power(): void
    {
        $this->assertFalse($this->service->isSigningMandatoryByPower(null));
    }

    public function test_power_rule_boundary(): void
    {
        $this->assertFalse($this->service->isSigningMandatoryByPower(669.99));
        $this->assertTrue($this->service->isSigningMandatoryByPower(670.0));
    }
}