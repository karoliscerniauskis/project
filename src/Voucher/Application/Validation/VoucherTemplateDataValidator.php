<?php

declare(strict_types=1);

namespace App\Voucher\Application\Validation;

use App\Voucher\Application\Exception\VoucherTemplateValidationFailed;
use App\Voucher\Domain\Enum\VoucherType;

final readonly class VoucherTemplateDataValidator
{
    public function validate(
        string $name,
        VoucherType $type,
        string $title,
        string $description,
        string $htmlTemplate,
    ): void {
        $errors = [];

        if (trim($name) === '') {
            $errors[] = [
                'field' => 'name',
                'message' => 'Voucher template name cannot be blank.',
            ];
        }

        if (trim($title) === '') {
            $errors[] = [
                'field' => 'title',
                'message' => 'Voucher template title cannot be blank.',
            ];
        }

        if (trim($description) === '') {
            $errors[] = [
                'field' => 'description',
                'message' => 'Voucher template description cannot be blank.',
            ];
        }

        if (trim($htmlTemplate) === '') {
            $errors[] = [
                'field' => 'htmlTemplate',
                'message' => 'Voucher template HTML cannot be blank.',
            ];
        }

        foreach ($this->getRequiredPlaceholders($type) as $placeholder) {
            if (!preg_match('/{{\s*'.preg_quote($placeholder, '/').'\s*}}/', $htmlTemplate)) {
                $errors[] = [
                    'field' => 'htmlTemplate',
                    'message' => sprintf('Voucher template is missing required placeholder "{{ %s }}".', $placeholder),
                ];
            }
        }

        if ($errors !== []) {
            throw VoucherTemplateValidationFailed::withErrors($errors);
        }
    }

    /**
     * @return list<string>
     */
    private function getRequiredPlaceholders(VoucherType $type): array
    {
        return [
            'title',
            'description',
            'code',
            'providerName',
            $type === VoucherType::Amount ? 'amount' : 'usage',
        ];
    }
}
