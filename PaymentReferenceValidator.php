<?php
/**
 * PaymentReferenceValidator class file.
 *
 * PaymentReferenceValidator can be used to validate a payment reference in Dutch or Belgium format. In the Netherlands,
 * this is called a 'betalingskenmerk', in Belgium a 'gestructureerde mededeling'. This validator is auto-detecting the
 * type of payment reference; in Belgium this is prefixed (and postfixed) with '+++' or '***', if that string can be
 * found, the payment reference is considered to be Belgium, Dutch otherwise.
 *
 * @author Emile Bons <emile@emilebons.nl>
 * @date 10-03-15
 */

namespace app\components;

use Exception;
use yii\base\Model;
use yii\validators\Validator;

class PaymentReferenceValidator extends Validator
{

	/**
	 * Validates the payment reference using either the Dutch or Belgium system
	 * @param Model $model the model being validated
	 * @param string $attribute the attribute being validated
	 * @return void
	 */
	public function validateAttribute($model, $attribute)
	{
		try {
			if(strpos($model->$attribute, '+++') === 0 || strpos($model->$attribute, '***') === 0) {
				$this->validatePaymentReferenceBelgium($model, $attribute);
			} else {
				$this->validatePaymentReferenceNetherlands($model, $attribute);
			}
		} catch(Exception $e) {
			$this->addError($model, $attribute,
				'An unknown error occurred when validating the payment reference: '.$e->getMessage());
		}
	}

	/**
	 * Validates the payment reference using the Belgium system of checking a payment reference
	 * @see http://nl.wikipedia.org/wiki/Gestructureerde_mededeling
	 * @param Model $model the model being validated
	 * @param string $attribute the attribute being validated
	 */
	private function validatePaymentReferenceBelgium($model, $attribute)
	{
		$value = str_replace(['+++', '***', '/'], ['', '', ''], trim($model->$attribute));
		$reference = substr($value, 0, 10);
		$givenRest = substr($value, 10, 2);
		$rest = $reference%97;
		$rest = $rest === 0 ? 97 : ($rest < 10 ? '0'.$rest : $rest);
		if($rest != $givenRest) {
			$this->addError($model, $attribute, 'The payment reference is not a valid reference in Belgium');
		}
	}

	/**
	 * Validates the payment reference using the Dutch system of checking a payment reference
	 * @see http://nl.wikipedia.org/wiki/Elfproef#Betalingskenmerk
	 * @param Model $model the model being validated
	 * @param string $attribute the attribute being validated
	 */
	private function validatePaymentReferenceNetherlands($model, $attribute)
	{
		$value = trim(str_replace(' ', '', $model->$attribute));
		$number = substr($value, (strlen($value) === 16 ? 1 : 2));
		if (strlen($number) < 15) {
			$number = str_pad($number, 15, '0', STR_PAD_LEFT);
		}
		$sum = (int) 0;
		$sum += (int)(substr($number,  -1, 1) *  2);
		$sum += (int)(substr($number,  -2, 1) *  4);
		$sum += (int)(substr($number,  -3, 1) *  8);
		$sum += (int)(substr($number,  -4, 1) *  5);
		$sum += (int)(substr($number,  -5, 1) * 10);
		$sum += (int)(substr($number,  -6, 1) *  9);
		$sum += (int)(substr($number,  -7, 1) *  7);
		$sum += (int)(substr($number,  -8, 1) *  3);
		$sum += (int)(substr($number,  -9, 1) *  6);
		$sum += (int)(substr($number, -10, 1) *  1);
		$sum += (int)(substr($number, -11, 1) *  2);
		$sum += (int)(substr($number, -12, 1) *  4);
		$sum += (int)(substr($number, -13, 1) *  8);
		$sum += (int)(substr($number, -14, 1) *  5);
		$sum += (int)(substr($number, -15, 1) * 10);
		$check = 11 - ($sum % 11);
		$check = $check == 10 ? 1 : ($check == 11 ? 0 : $check);
		$checkGiven = substr($value, 0, (strlen($value) === 16 ? 1 : 2));
		if($check != $checkGiven) {
			$this->addError($model, $attribute, 'The payment reference is not a valid reference in the Netherlands');
		}
	}

}
