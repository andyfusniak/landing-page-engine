<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Form\Validator
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace Gm\LandingPageEngine\Form\Validator;

class ValidatorChain implements ValidatorInterface
{
    const CHAIN_STRING_DELIMITER = ' -> ';

    /**
     * @var array of Validator objects
     */
    protected $validators = [];

    /**
     * @var array|null
     */
    protected $messages;

    /**
     * @param ValidatorInterface validator object
     * @return ValidatorChain
     */
    public function attach($validator)
    {
        if ($validator instanceof ValidatorInterface) {
            $this->validators[] = $validator;
        } else {
            throw new Exception\InvalidArgumentException(sprintf(
               '%s expects an object of type ValidatorInterface',
                __METHOD__
            ));
        }
        return $this;
    }

    /**
     * @param string $value form value to check
     * @context array|null an optional array holding the form context
     */
    public function isValid($value, $context = null)
    {
        $this->messages = [];
        $result = true;

        foreach ($this->validators as $validator) {
            if (!$validator->isValid($value, $context)) {
                $result = false;
                $this->messages = array_replace_recursive(
                    $this->messages,
                    $validator->getMessages()
                );
            }
        }
        return $result;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function __toString()
    {
        $validatorChainString = '{ ';
        foreach ($this->validators as $validator) {
            $validatorChainString .= $validator;
            $validatorChainString .= self::CHAIN_STRING_DELIMITER;
        }

        // cut off the trailing comma ', ' (last 2 characters)
        return substr(
            $validatorChainString,
            0,
            strlen($validatorChainString) - strlen(self::CHAIN_STRING_DELIMITER)
        ) . ' }';
    }
}
