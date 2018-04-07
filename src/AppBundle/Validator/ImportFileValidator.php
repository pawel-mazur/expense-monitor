<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ImportFile.
 */
class ImportFileValidator extends ConstraintValidator
{
    /**
     * @{@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ('text/csv' !== $value->getClientMimeType()) {
            $this->context->buildViolation($constraint->mimeTypesMessage)
                ->setParameter('{{ file }}', $this->formatValue($value->getPathname()))
                ->setParameter('{{ type }}', $this->formatValue($value->getMimeType()))
                ->setParameter('{{ types }}', $this->formatValues(['text/csv']))
                ->setCode(File::INVALID_MIME_TYPE_ERROR)
                ->addViolation();
        }
    }
}
