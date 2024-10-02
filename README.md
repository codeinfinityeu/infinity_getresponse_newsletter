# GetResponse newsletter module

Module allow to subscribe for GetResponse mailing list. Provide block with subscription form.

GetResponse docs: 
[API docs](https://apireference.getresponse.com/#operation/getContactList)

Installation
------------
* Download and install module - no extra dependencies

Configuration
-------------

* Provide GetResponse `Subscriber ID list` and `API key` at config page: `/admin/config/getresponse/newsletter`
* Provide messages for terms of service and responses 
* Add the "Newsletter - GetResponse subscriber block" block in the block configuration at Admin > Structure > Blocks `/admin/structure/block`.


### Example consents:

Terms or service:
_I order the newsletter and declare that I have read the newsletter terms of service and accept its content._

Rodo:
_I declare that I have read the newsletter information clause and accept its content._

Validation messages:

- (success) _Thank you for joining our newsletter._
- (error) _An error occurred while subscribing to the newsletter._
- (already subscribed) _You are already subscribed to our newsletter. Thank you._
