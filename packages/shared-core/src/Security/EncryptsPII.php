<?php

namespace Shared\Core\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

trait EncryptsPII
{
    /**
     * Define which attributes are sensitive and should be encrypted at rest.
     */
    protected function getSensitiveAttributes(): array
    {
        return property_exists($this, 'sensitive') ? $this->sensitive : [];
    }

    /**
     * Automatically encrypt on set and decrypt on get.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->getSensitiveAttributes()) && !is_null($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->getSensitiveAttributes()) && !is_null($value)) {
            $value = Crypt::encryptString($value);
        }

        return parent::setAttribute($key, $value);
    }
}
