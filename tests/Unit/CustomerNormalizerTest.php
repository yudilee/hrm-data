<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CustomerNormalizer;
use Tests\TestCase;

class CustomerNormalizerTest extends TestCase
{
    protected CustomerNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new CustomerNormalizer;
    }

    public function test_normalize_name_removes_titles(): void
    {
        $this->assertSame('JOHNDOE', $this->normalizer->normalizeName('Mr. John Doe'));
        $this->assertSame('JANEDOE', $this->normalizer->normalizeName('MRS Jane Doe'));
        $this->assertSame('BUDI', $this->normalizer->normalizeName('H. Budi'));
    }

    public function test_normalize_name_removes_entity_prefixes(): void
    {
        $this->assertSame('MAJUMUNDUR', $this->normalizer->normalizeName('PT Maju Mundur'));
        $this->assertSame('BERKAHJAYA', $this->normalizer->normalizeName('CV. Berkah Jaya'));
    }

    public function test_normalize_name_handles_empty(): void
    {
        $this->assertSame('', $this->normalizer->normalizeName(null));
        $this->assertSame('', $this->normalizer->normalizeName(''));
    }

    public function test_canonical_phone_normalizes_indonesian_numbers(): void
    {
        $this->assertSame('08123456789', $this->normalizer->canonicalPhone('+628123456789'));
        $this->assertSame('08123456789', $this->normalizer->canonicalPhone('628123456789'));
        $this->assertSame('08123456789', $this->normalizer->canonicalPhone('08123456789'));
    }

    public function test_canonical_phone_strips_non_digits(): void
    {
        $this->assertSame('08123456789', $this->normalizer->canonicalPhone('0812-3456-789'));
        $this->assertSame('0211234567', $this->normalizer->canonicalPhone('(021) 123-4567'));
    }

    public function test_canonical_phone_handles_empty(): void
    {
        $this->assertNull($this->normalizer->canonicalPhone(null));
        $this->assertNull($this->normalizer->canonicalPhone(''));
    }

    public function test_detect_phone_type_mobile(): void
    {
        $this->assertSame('mobile', $this->normalizer->detectPhoneType('08123456789'));
    }

    public function test_detect_phone_type_landline(): void
    {
        $this->assertSame('landline', $this->normalizer->detectPhoneType('0211234567'));
    }

    public function test_detect_phone_type_unknown(): void
    {
        $this->assertSame('unknown', $this->normalizer->detectPhoneType(null));
        $this->assertSame('unknown', $this->normalizer->detectPhoneType(''));
    }
}
