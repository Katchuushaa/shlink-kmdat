<?php
declare(strict_types=1);

namespace Shlinkio\Shlink\Core\Model;

use Shlinkio\Shlink\Core\Exception\ValidationException;
use Shlinkio\Shlink\Core\Validation\ShortUrlMetaInputFilter;

final class ShortCodeMeta
{
    /**
     * @var \DateTime|null
     */
    private $validSince;
    /**
     * @var \DateTime|null
     */
    private $validUntil;
    /**
     * @var string|null
     */
    private $customSlug;
    /**
     * @var int|null
     */
    private $maxVisits;

    // Force named constructors
    private function __construct()
    {
    }

    /**
     * @param array $data
     * @return ShortCodeMeta
     * @throws ValidationException
     */
    public static function createFromRawData(array $data): self
    {
        $instance = new self();
        $instance->validate($data);
        return $instance;
    }

    /**
     * @param string|\DateTimeInterface|null $validSince
     * @param string|\DateTimeInterface|null $validUntil
     * @param string|null $customSlug
     * @param int|null $maxVisits
     * @return ShortCodeMeta
     * @throws ValidationException
     */
    public static function createFromParams(
        $validSince = null,
        $validUntil = null,
        $customSlug = null,
        $maxVisits = null
    ): self {
        // We do not type hint the arguments because that will be done by the validation process
        $instance = new self();
        $instance->validate([
            ShortUrlMetaInputFilter::VALID_SINCE => $validSince,
            ShortUrlMetaInputFilter::VALID_UNTIL => $validUntil,
            ShortUrlMetaInputFilter::CUSTOM_SLUG => $customSlug,
            ShortUrlMetaInputFilter::MAX_VISITS => $maxVisits,
        ]);
        return $instance;
    }

    /**
     * @param array $data
     * @throws ValidationException
     */
    private function validate(array $data)
    {
        $inputFilter = new ShortUrlMetaInputFilter($data);
        if (! $inputFilter->isValid()) {
            throw ValidationException::fromInputFilter($inputFilter);
        }

        $this->validSince = $inputFilter->getValue(ShortUrlMetaInputFilter::VALID_SINCE);
        $this->validUntil = $inputFilter->getValue(ShortUrlMetaInputFilter::VALID_UNTIL);
        $this->customSlug = $inputFilter->getValue(ShortUrlMetaInputFilter::CUSTOM_SLUG);
        $this->maxVisits = $inputFilter->getValue(ShortUrlMetaInputFilter::MAX_VISITS);
        $this->maxVisits = $this->maxVisits !== null ? (int) $this->maxVisits : null;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidSince()
    {
        return $this->validSince;
    }

    public function hasValidSince(): bool
    {
        return $this->validSince !== null;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    public function hasValidUntil(): bool
    {
        return $this->validUntil !== null;
    }

    /**
     * @return null|string
     */
    public function getCustomSlug()
    {
        return $this->customSlug;
    }

    public function hasCustomSlug(): bool
    {
        return $this->customSlug !== null;
    }

    /**
     * @return int|null
     */
    public function getMaxVisits()
    {
        return $this->maxVisits;
    }

    public function hasMaxVisits(): bool
    {
        return $this->maxVisits !== null;
    }
}
