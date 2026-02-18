PayPap API Documentation
Complete reference for integrating bank deposits, withdrawals, and card payments.
PRODUCTION https://api.paypap.org/v1
TEST https://test-api.paypap.org/v1
Introduction
Overview of the PayPap Payment API
The PayPap API enables you to integrate multiple payment methods into your application, including bank transfers
(deposits & withdrawals) and credit card payments. All endpoints use JSON for request and response bodies.
Important
All monetary amounts are in major currency units (e.g., 5000 = 5000 TRY). Bank deposits must be positive
integers, while card deposits accept decimals.
Authentication
How to authenticate your API requests
Bearer Token Authentication
All API requests require a Bearer token in the Authorization header.HEADER
Authorization: Bearer <your_api_token>
Error Responses
Common error responses across all endpoints
400 Bad Request
Validation Error
{ "code": 400, "message": "\"amount\" must be an integer" }
Amount Out of Range (1002)
{ "code": 400, "message": { "code": 1002, "desc": "Allowed amount range is 500 to 100000" } }
401 Unauthorized
{ "code": 401, "message": "Bearer token is invalid or missing" }
404 Not Found
{ "code": 404, "message": "Not found" }
409 Conflict
Transaction ID Conflict (1003)
{ "code": 409, "message": { "code": 1003, "desc": "Already exists with same transaction ID" } }
User Has Pending Transaction (1004){ "code": 409, "message": { "code": 1004, "desc": "User already has an uncompleted transaction" } }
503 Service Unavailable
{ "code": 503, "message": "Service is unavailable (maintenance or disabled temporarily)" }
Status Codes
Payment status codes returned in responses and callbacks
Bank Deposit Status Codes
Code Description
4000 Pending
4001 Completed
4002 Payments cannot be accepted
4003 Name does not match
4004 User cancelled
4005 Not paid
4006 Timeout
4007 Unexpected error
Bank Withdrawal Status Codes
Code Description
5000 PendingCode Description
5001 Completed
5002 Invalid IBAN
5003 Name does not match
5004 Supplier cancelled
5005 Timeout
5006 Unexpected error
Card Deposit Status Codes
Code Description
6000 Pending
6001 Completed
6002 Name does not match
6003 User cancelled
6004 Timeout
6005 Internal error
6006 Payment declined
6007 Payment error
6008 Payment failed
6009 Payment cancelled
6010 Not paidCode Description
6011 3DS failed
6012 Insufficient funds
Bank Deposits
Create and retrieve bank transfer depositsPOST /bankDeposits Create a bank deposit
Creates a new bank deposit request. Choose between direct (returns IBAN details) or redirect (returns
hosted payment page URL).
Request Body
PARAMETER TYPE DESCRIPTION
type required string direct or redirect
transactionId required string Unique transaction ID (UUID recommended)
fullName required string Sender's full name
currency required string TRY
amount required integer Amount (positive integer)
user required object Customer info: userId, username, fullName
redirectUrls redirect only object success and fail URLs
Request Example (Direct)
REQUEST BODY
{
"type": "direct",
"transactionId": "7a0bd718-111c-4a02-af36-0be7b8fd387b",
"fullName": "John Doe",
"currency": "TRY",
"amount": 5000,
"user": { "userId": "1234567890", "username": "johndoe", "fullName": "John Doe" }
}Request Example (Redirect)
REQUEST BODY
{
"type": "redirect",
"transactionId": "7a0bd718-111c-4a02-af36-0be7b8fd387b",
"fullName": "John Doe",
"currency": "TRY",
"amount": 5000,
"user": { "userId": "1234567890", "username": "johndoe", "fullName": "John Doe" },
"redirectUrls": { "success": "https://example.com/success", "fail": "https://example.com/
fail" }
}
Response (Direct) - 201 Created
{
"depositId": "bbeebd49-bd79-41a5-8f6b-0ad92c88d673",
"recipient": { "bankName": "Example Bank", "fullName": "John Smith", "iban":
"TR000000000000000000000000" },
"expiresAt": "2020-01-01T00:00:10.000Z"
}
Response (Redirect) - 201 Created
{
"depositId": "bbeebd49-bd79-41a5-8f6b-0ad92c88d673",
"url": "https://pay.paypap.org/bankDeposit/bbeebd49-bd79-41a5-8f6b-0ad92c88d673"
}
Error Responses: 400 Bad Request, 401 Unauthorized, 409 Conflict, 502 Bad Gateway, 503 Service
UnavailableGET /bankDeposits/{depositId} Get a bank deposit
Retrieves details of a specific bank deposit by its ID.
Response - 200 OK
{
"transactionId": "7a0bd718-111c-4a02-af36-0be7b8fd387b",
"fullName": "John Doe",
"currency": "TRY",
"requestedAmount": 5000,
"amount": 5000,
"user": { "userId": "1234567890", "username": "johndoe", "fullName": "John Doe" },
"status": { "code": 4001, "desc": "Completed" },
"createdAt": "2020-01-01T00:00:00.000Z",
"updatedAt": "2020-01-01T00:00:10.000Z"
}
Other Responses: 202 Accepted (pending), 400 Bad Request, 401 Unauthorized, 404 Not Found
Bank Withdrawals
Create and retrieve bank transfer withdrawalsPOST /bankWithdrawals Create a bank withdrawal
Creates a new bank withdrawal request to send funds to a customer's IBAN.
Request Example
REQUEST BODY
{
"transactionId": "5d94f369-34eb-4f8c-882c-3a951e0f075d",
"fullName": "John Doe",
"iban": "TR000000000000000000000000",
"currency": "TRY",
"amount": 5000,
"user": { "userId": "1234567890", "username": "johndoe", "fullName": "John Doe" }
}
Response - 201 Created
{ "withdrawalId": "1b66d504-6486-4131-9b77-11a92f3559bf" }GET /bankWithdrawals/{withdrawalId} Get a bank withdrawal
Retrieves details of a specific bank withdrawal by its ID.
Response - 200 OK
{
"transactionId": "5d94f369-34eb-4f8c-882c-3a951e0f075d",
"fullName": "John Doe",
"iban": "TR000000000000000000000000",
"currency": "TRY",
"requestedAmount": 5000,
"amount": 5000,
"status": { "code": 5001, "desc": "Completed" },
"createdAt": "2020-01-01T00:00:00.000Z",
"updatedAt": "2020-01-01T00:00:10.000Z"
}
Card Deposits
Create and retrieve credit/debit card depositsPOST /cardDeposits Create a card deposit
Creates a new card deposit. Use direct to send card data via API, or redirect for a hosted payment page.
Request Example (Direct)
REQUEST BODY
{
"type": "direct",
"transactionId": "7a0bd718-111c-4a02-af36-0be7b8fd387b",
"currency": "TRY",
"amount": 5000,
"card": { "holderName": "John Doe", "cardNumber": "4111111111111111", "expMonth": "01",
"expYear": "2030", "cvv2": "123" },
"billing": { "email": "user@example.com", "city": "Istanbul", "country": "TR" },
"user": { "userId": "1234567890", "username": "johndoe", "fullName": "John Doe" },
"redirectUrls": { "success": "https://example.com/success", "fail": "https://example.com/
fail" }
}
Request Example (Redirect)
REQUEST BODY
{
"type": "redirect",
"transactionId": "7a0bd718-111c-4a02-af36-0be7b8fd387b",
"currency": "TRY",
"amount": 5000,
"billing": { "email": "user@example.com" },
"user": { "userId": "1234567890", "username": "johndoe", "fullName": "John Doe" },
"redirectUrls": { "success": "https://example.com/success", "fail": "https://example.com/
fail" }
}
Response - 201 Created
{
"depositId": "bbeebd49-bd79-41a5-8f6b-0ad92c88d673",
"url": "https://pay.paypap.org/deposit/card/bbeebd49-bd79-41a5-8f6b-0ad92c88d673"
}GET /cardDeposits/{depositId} Get a card deposit
Retrieves details of a specific card deposit by its ID.
Response - 200 OK
{
"transactionId": "7a0bd718-111c-4a02-af36-0be7b8fd387b",
"currency": "TRY",
"requestedAmount": 5000,
"amount": 5000,
"status": { "code": 6001, "desc": "Completed" },
"createdAt": "2020-01-01T00:00:00.000Z",
"updatedAt": "2020-01-01T00:00:10.000Z"
}
Webhook
Receive payment status notificationsPOST /callback Client Callback Endpoint
Important
This endpoint is implemented by the client. PayPap will send POST requests to your configured
callback URL when payment status changes.
Callback Payload Example
{
"method": "bank",
"type": "deposit",
"clientId": 100123456789,
"depositId": "bbeebd49-bd79-41a5-8f6b-0ad92c88d673",
"transactionId": "7a0bd718-111c-4a02-af36-0be7b8fd387b",
"fullName": "Foo Bar",
"currency": "TRY",
"requestedAmount": 5000,
"amount": 5000,
"user": { "userId": "1234567890", "username": "foobar", "fullName": "Foo Bar" },
"status": { "code": 4001, "desc": "Completed" },
"timestamp": 1672531200,
"checksum": "0c1c7dc308dfc8f586484a6eaebe3be07b099462"
}
Checksum Verification (JavaScript)
const crypto = require('crypto');
function verifyChecksum(payload, apiSecret) {
const expected = crypto.createHash('sha1')
.update(apiSecret + payload.timestamp + payload.depositId)
.digest('hex');
return expected === payload.checksum;
}PayPap API Documentation v1.2.3

