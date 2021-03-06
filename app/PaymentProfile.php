<?php declare(strict_types=1);

namespace App;

class PaymentProfile
{
    /** @var string */
    private string $id;

    /** @var string */
    private string $name;

    /** @var string */
    private string $website;

    public function __construct(string $id, string $name, string $website)
    {
        $this->id = $id;
        $this->name = $name;
        $this->website = $website;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWebsite(): string
    {
        return $this->website;
    }
}
