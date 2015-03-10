# PaymentReferenceValidator
A yii2 validator for validating a Dutch 'betalingskenmerk' and Belgium 'gestructureerde mededeling'.

Installation
------------
Put the PaymentReferenceValidator class in your app\compontens folder and the following validation rule to your model:
```php
['payment_reference', 'app\components\PaymentReferenceValidator']
```
